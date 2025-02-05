<?php

namespace App\Http\Controllers\Api\V2;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SupportTicketReply;
use App\Http\Requests\Api\SupportTicketStore;
use App\Http\Resources\NewsCollection;
use App\Http\Resources\NewsDetailsCollection;
use App\Http\Resources\PolicyCollection;
use App\Http\Resources\RefundPolicyCollection;
use App\Http\Resources\SupportTicketCollection;
use App\Http\Resources\TermsOfServiceCollection;
use App\Http\Resources\TicketBetsCollection;
use App\Models\AdminNotification;
use App\Models\Bet;
use App\Models\Deposit;
use App\Models\Form;
use App\Models\Frontend;
use App\Models\SupportAttachment;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    protected $files;
    protected $allowedExtension = ['jpg', 'png', 'jpeg', 'pdf', 'doc', 'docx'];

    public function supportTicket($pageNo = null,$perPage = null){
        $perPage = $perPage ?? 10;
        $paginationData = [];
        $supports  = SupportTicket::where('user_id', Auth::id())->orderBy('id', 'desc');
        $totalItems = $supports->count();
        if($pageNo){
            $skip = $pageNo == 1 ? 0 : $perPage * ($pageNo - 1);
            $supports = $supports->skip($skip)->take($perPage)->get();

            $paginationData = [
                'currentPage'         => $pageNo,
                'nextPage'          => $pageNo+1,
                'totalPages'         => ceil($totalItems / $perPage),
                'totalItems'         => $totalItems,
                'itemsPerPage'         => $perPage,
            ];
        } else{
            $supports = $supports->get();
        }

        $payload = [
            'status'         => true,
            'data' => SupportTicketCollection::collection($supports),
            'paginationData' =>  $paginationData,
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function getBetsData(){
        $allBets = Bet::latest()->select('id as bet_num', 'bet_number', 'type', 'stake_amount as amount')->where('user_id', Auth::id())->limit(15)->get();
        $payload = [
            'status'         => true,
            'data' => TicketBetsCollection::collection($allBets),
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function storeSupportTicket(SupportTicketStore $request){

        if ($request->subject === 'Withdraw problem') {
            $checkTrxId = Withdrawal::where('trx', $request->transaction_id)->where('user_id', Auth::id())->first();
            if (!$checkTrxId) {
                $payload = [
                    'status'         => false,
                    'notify_status'  => 'error',
                    'notify'         => 'Withdraw not found',
                    'app_message'    => 'Withdraw not found',
                    'user_message'   => 'Withdraw not found'
                ];
                return response()->json($payload, 200);
            }
        }
        if ($request->subject === 'Deposit problem') {
            $checkTrxId = Deposit::where('trx', $request->transaction_id)->where('user_id', Auth::id())->first();
            if (!$checkTrxId) {
                $payload = [
                    'status'         => false,
                    'notify_status'  => 'error',
                    'notify'         => 'Deposit not found',
                    'app_message'    => 'Deposit not found',
                    'user_message'   => 'Deposit not found'
                ];
                return response()->json($payload, 200);
            }
        }
        $ticketData = [
            'user_id'           => Auth::id(),
            'ticket'            => rand(100000, 999999),
            'name'              => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            'email'             => Auth::user()->email,
            'subject'             => $request->subject,
            'trx_no'             => $request->transaction_id,
            'trx_date'             => $request->transaction_date,
            'bet_id'             => $request->bet_no,
            'priority'             => $request->priority,
            'status'             => Status::TICKET_OPEN,
            'last_reply'             => Carbon::now(),
        ];
        $insertSupportTicket = SupportTicket::create($ticketData);
        if($insertSupportTicket){
            try {
                $message = new SupportMessage();
                $message->support_ticket_id = $insertSupportTicket->id;
                $message->message           = $request->message;
                $message->save();

                if ($request->hasFile('attachments')) {
                    try {
                        $path = getFilePath('ticket');

                        foreach ($request->attachments  as $file) {
                            try {
                                $attachment                     = new SupportAttachment();
                                $attachment->support_message_id = $message->id;
                                $attachment->attachment         = fileUploader($file, $path);
                                $attachment->save();
                            } catch (\Exception $exp) {
                                $payload = [
                                    'status'         => false,
                                    'notify_status'  => 'error',
                                    'notify'         => 'File could not upload',
                                    'app_message'    => 'New support ticket has opened',
                                    'user_message'   => 'New support ticket has opened'
                                ];
                                return response()->json($payload, 200);
                            }
                        }
                    }catch (\Exception $exception){

                    }
                }
            }catch (\Exception $e){}

            try {
                $adminNotification            = new AdminNotification();
                $adminNotification->user_id   = Auth::id();
                $adminNotification->title     = 'New support ticket has opened';
                $adminNotification->click_url = urlPath('admin.ticket.view', $insertSupportTicket->id);
                $adminNotification->save();
            }catch (\Exception $e){}

            $payload = [
                'status'         => true,
                'notify_status'  => 'success',
                'message_id'     => $insertSupportTicket->ticket,
                'notify'         => 'Ticket opened Successful',
                'app_message'    => 'Store Process Successful',
                'user_message'   => 'Store Process Successful'
            ];
            return response()->json($payload, 200);
        }else{
            $payload = [
                'status'         => false,
                'notify_status'  => 'error',
                'notify'         => 'Ticket opened unsuccessful',
                'app_message'    => 'Store Process Unsuccessful',
                'user_message'   => 'Store Process Unsuccessful'
            ];
            return response()->json($payload, 200);
        }

    }
    public function viewTicket($ticketID){
        $ticket = SupportTicket::with('deposits', 'withdraws', 'bets')
            ->where('ticket', $ticketID)->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->first();
        if($ticket){
            $messages = SupportMessage::where('support_ticket_id', $ticket->id)
                ->with('ticket', 'admin', 'attachments')
                ->orderBy('id', 'desc')->get();
        }else{
            $messages = [];
        }


        $payload = [
            'status'         => true,
            'data' => [
                'ticket' => $ticket,
                'messages' => $messages,
            ],
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function replyTicket(SupportTicketReply $request,$ticketID){
        $ticket = SupportTicket::where('id', $ticketID)->first();
        if($ticket){
            $message = new SupportMessage();
            $request->merge(['ticket_reply' => 1]);

            $ticket->status     = $request->user_type != 'admin' ? Status::TICKET_REPLY : Status::TICKET_ANSWER;
            $ticket->last_reply = Carbon::now();
            $ticket->save();

            $message->support_ticket_id = $ticket->id;

            if ($request->user_type == 'admin') {
                $message->admin_id = Auth::id();
            }

            $message->message = $request->message;
            $message->save();

            if ($request->hasFile('attachments')) {
                try {
                    $path = getFilePath('ticket');

                    foreach ($request->attachments as $file) {
                        try {
                            $attachment                     = new SupportAttachment();
                            $attachment->support_message_id = $message->id;
                            $attachment->attachment         = fileUploader($file, $path);
                            $attachment->save();
                        } catch (\Exception $exp) {
                            $payload = [
                                'status'         => false,
                                'notify_status'  => 'error',
                                'notify'         => 'File could not upload',
                                'app_message'    => 'Support ticket replied successfully!',
                                'user_message'   => 'Support ticket replied successfully!'
                            ];
                            return response()->json($payload, 200);
                        }
                    }
                }catch (\Exception $exception){

                }
            }

            if ($request->user_type == 'admin') {
                $createLog = false;
                $user      = $ticket;
                if ($ticket->user_id != 0) {
                    $createLog = true;
                    $user      = $ticket->user;
                }

                notify($user, 'ADMIN_SUPPORT_REPLY', [
                    'ticket_id'      => $ticket->ticket,
                    'ticket_subject' => $ticket->subject,
                    'reply'          => $request->message,
                    'link'           => route('ticket.view', $ticket->ticket),
                ], null, $createLog);
            }
            $payload = [
                'status'         => true,
                'notify_status'  => 'success',
                'notify'         => 'Support ticket replied successfully!',
                'app_message'    => 'Support ticket replied successfully!',
                'user_message'   => 'Support ticket replied successfully!'
            ];
            return response()->json($payload, 200);
        }else{
            $payload = [
                'status'         => false,
                'notify_status'  => 'error',
                'notify'         => 'Ticket Not Found',
                'app_message'    => 'Ticket Not Found',
                'user_message'   => 'Ticket Not Found'
            ];
            return response()->json($payload, 200);
        }
    }
    public function closeTicket(Request $request,$id)
    {
        $ticket = SupportTicket::where('id', $id)->first();
        if($ticket){
            if ($request->user_type != 'admin') {
                if (Auth::id() != $ticket->user_id) {
                    $payload = [
                        'status'         => false,
                        'notify_status'  => 'error',
                        'notify'         => 'Ticket Not Found',
                        'app_message'    => 'Ticket Not Found',
                        'user_message'   => 'Ticket Not Found'
                    ];
                    return response()->json($payload, 200);
                }
            }

            $ticket->status = Status::TICKET_CLOSE;
            $ticket->save();
            $payload = [
                'status'         => true,
                'notify_status'  => 'success',
                'notify'         => 'Support ticket closed successfully!',
                'app_message'    => 'Support ticket closed successfully!',
                'user_message'   => 'Support ticket closed successfully!'
            ];
            return response()->json($payload, 200);
        }else{
            $payload = [
                'status'         => false,
                'notify_status'  => 'error',
                'notify'         => 'Ticket Not Found',
                'app_message'    => 'Ticket Not Found',
                'user_message'   => 'Ticket Not Found'
            ];
            return response()->json($payload, 200);
        }
    }

    public function ticketDownload($ticket_id)
    {
        $attachment = SupportAttachment::findOrFail(decrypt($ticket_id));
        $file       = $attachment->attachment;
        $path       = getFilePath('ticket');
        $full_path  = $path . '/' . $file;
        $title      = slug($attachment->supportMessage->ticket->subject);
        $ext        = pathinfo($file, PATHINFO_EXTENSION);
        $mimetype   = mime_content_type($full_path);
        header('Content-Disposition: attachment; filename="' . $title . '.' . $ext . '";');
        header("Content-Type: " . $mimetype);
        return readfile($full_path);
    }
}
