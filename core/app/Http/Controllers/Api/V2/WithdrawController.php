<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\WithdrawStore;
use App\Http\Resources\WithdrawHistoryCollection;
use App\Models\AdminNotification;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Withdrawal;
use App\Models\WithdrawMethod;
use Illuminate\Http\Request;
use App\Models\TransectionProviders;
use App\Models\GatewayCurrency;
use App\Constants\Status;
use App\Http\Resources\PaymentMethodCollection;
use App\Models\Deposit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class WithdrawController extends Controller
{
    public function withdrawStore(WithdrawStore $request){
//        return $request;
        $user = auth()->user();
        if ($request->payment_gateway) {
            if ($request->payment_gateway == 'local') {
                $agentCheck = DB::table('admin_transection_providers')
                    ->where('transection_provider_id', $request->provider)
                    ->where('admin_id', $request->agent)->first();
                if (!$agentCheck) {
                    $payload = [
                        'status'         => false,
                        'app_message'  => 'Please try again',
                        'user_message' => 'Please try again'
                    ];
                    return response()->json($payload, 200);
                }

                $insertData = [
                    'user_id' => $user->id,
                    'agent_id' => 1,
                    'phone' => $request->payment_number,
                    'gateway' => $request->payment_gateway,
                    'provider_id' => $request->provider,
                    'amount' => $request->amount,
                    'currency' => Auth::user()->currency,
                    'final_amount' => $request->amount,
                    'status' => 2,
                    'trx' => getNumberTrx(),
                ];

            } else {
                $insertData = [
                    'user_id' => $user->id,
                    'agent_id' => $request->agent,
                    'phone' => $request->payment_number,
                    'gateway' => $request->payment_gateway,
                    'amount' => $request->amount,
                    'currency' => Auth::user()->currency,
                    'final_amount' => $request->amount,
                    'available_amount' => $request->amount,
                    'trx' => getNumberTrx(),
                    'status' => 2,
                ];
            }
        } else {

            $method = WithdrawMethod::where('id', $request->method_code)->where('status', Status::ENABLE)->firstOrFail();
            if ($request->amount < $method->min_limit) {
                $payload = [
                    'status'         => false,
                    'app_message'  => 'Your requested amount is smaller than minimum amount.',
                    'user_message' => 'Your requested amount is smaller than minimum amount.'
                ];
                return response()->json($payload, 200);
            }
            if ($request->amount > $method->max_limit) {
                $payload = [
                    'status'         => false,
                    'app_message'  => 'Your requested amount is larger than maximum amount.',
                    'user_message' => 'Your requested amount is larger than maximum amount.'
                ];
                return response()->json($payload, 200);
            }

            if ($request->amount > $user->withdrawal) {
                $payload = [
                    'status'         => false,
                    'app_message'  => 'You do not have sufficient withdrawal balance for withdraw.',
                    'user_message' => 'You do not have sufficient withdrawal balance for withdraw.'
                ];
                return response()->json($payload, 200);
            }


            $charge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
            $afterCharge = $request->amount - $charge;
            $finalAmount = $afterCharge * $method->rate;
            $insertData = [
                'method_id' => $method->id,
                'user_id' => $user->id,
                'amount' => $request->amount,
                'currency' => Auth::user()->currency,
                'rate' => $method->rate,
                'charge' => $charge,
                'final_amount' => $finalAmount,
                'after_charge' => $afterCharge,
                'available_amount' => Auth::user()->withdrawal,
                'trx' => getNumberTrx(),
                'status' => 2,
            ];
        }
        $withdrawCreate = Withdrawal::create($insertData);
        if ($withdrawCreate) {
            $user = auth()->user();
            $user->decrement('withdrawal', $request->amount);
        }
        $payload = [
            'status'         => true,
            'data'            => [
                'trx' => $withdrawCreate->trx,
                'amount' => showAmount($withdrawCreate->amount),
                'charge' => showAmount($withdrawCreate->charge),
                'final_amount' => showAmount($withdrawCreate->final_amount),
                'currency' => $withdrawCreate->currency,
                'phone' => $withdrawCreate->phone,
                'method' => $withdrawCreate->method_id ? $withdrawCreate->method->name : $withdrawCreate->transectionProviders->name ?? 'Cash agent',
            ],
            'app_message'  => 'Successfully Stored Data',
            'user_message' => 'Successfully Stored Data'
        ];
        return response()->json($payload, 200);
    }
    public function getWithdrawPaymentData(){
        $providers = TransectionProviders::where('status', 1)->where('country_code', auth()->user()->country_code)->get();
        $payload = [
            'status'         => true,
            'data' => [
                'payment_method' => PaymentMethodCollection::collection($providers),
                'mob_cash_agent' => '',
            ],
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function withdrawSubmit(Request $request){
        $this->validate($request, [
            'trx' => 'required',
        ]);
        $user = auth()->user();

        $withdraw = Withdrawal::with('method', 'user')->where('trx', $request->trx)
            ->where('status', Status::PAYMENT_INITIATE)
            ->orderBy('id', 'desc')
            ->firstOrFail();
        if ($user->ts) {
            $response = verifyG2fa($user, $request->authenticator_code);
            if (!$response) {
                $payload = [
                    'status'         => false,
                    'app_message'  => 'Wrong verification code',
                    'user_message' => 'Wrong verification code'
                ];
                return response()->json($payload, 200);
            }
        }
        if ($withdraw->amount > $user->withdrawal) {
            $payload = [
                'status'         => false,
                'app_message'  => 'Your request amount is larger then your current withdrawal balance.',
                'user_message' => 'Your request amount is larger then your current withdrawal balance.'
            ];
            return response()->json($payload, 200);
        }
        try {
            $withdraw->status = Status::PAYMENT_PENDING;
            $withdraw->withdraw_information = [];
            $withdraw->available_amount = Auth::user()->withdrawal;
            $withdraw->save();

            $user->withdrawal  -=  $withdraw->amount;
            $user->save();

            $transaction = new Transaction();
            $transaction->user_id = $withdraw->user_id;
            $transaction->amount = $withdraw->amount;
            $transaction->post_balance = $user->withdrawal;
            $transaction->charge = $withdraw->charge;
            $transaction->trx_type = '-';
            $transaction->details = showAmount(@$withdraw->final_amount) . ' ' . $withdraw->currency . ' Withdraw Via ' . @$withdraw->method->name ? @$withdraw->method->name : @$withdraw->transectionProviders->name ?? 'Cash agent';
            $transaction->trx = $withdraw->trx;
            $transaction->remark = 'withdraw';
            $transaction->save();

            $adminNotification = new AdminNotification();
            $adminNotification->user_id = $user->id;
            $adminNotification->title = 'New withdraw request from ' . $user->username;
            $adminNotification->click_url = urlPath('admin.withdraw.details', $withdraw->id);
            $adminNotification->save();

            notify($user, 'WITHDRAW_REQUEST', [
                'method_name' => @$withdraw->method->name ? @$withdraw->method->name : @$withdraw->transectionProviders->name ?? 'Cash agent',
                'method_currency' => $withdraw->currency,
                'method_amount' => showAmount($withdraw->final_amount),
                'amount' => showAmount($withdraw->amount),
                'trx' => $withdraw->trx,
                'post_balance' => showAmount($user->withdrawal),
            ]);

            $payload = [
                'status'         => true,
                'app_message'  => 'Withdraw request sent successfully',
                'user_message' => 'Withdraw request sent successfully'
            ];
            return response()->json($payload, 200);
        }catch (\Exception $exception){
            $payload = [
                'status'         => false,
                'app_message'  => 'Please try again',
                'user_message' => 'Please try again'
            ];
            return response()->json($payload, 200);
        }

    }
    public function getWithdrawHistory($pageNo = null,$perPage = null,Request $request){
        $paginationData = [];
        $perPage = $perPage ?? 10;
        $withdraws = Withdrawal::where('user_id', auth()->id())
            ->where('status', '!=', Status::PAYMENT_INITIATE)
            ->when($request->search, function ($query, $search) {
                return $query->where('trx', $search);
            })
            ->when($request->dates, function ($query, $dates) {
                $dates = explode(" - ", $dates);
                $from = date('Y-m-d H:i:s', strtotime($dates[0]));
                $to = date('Y-m-d H:i:s', strtotime($dates[1]));
                return $query->whereBetween('created_at', [$from . ' 00:00:00', $to . ' 23:59:59']);
            })
            ->with('method', 'transectionProviders')
            ->orderBy('id', 'desc');
        if($pageNo){
            $skip = $pageNo == 1 ? 0 : $perPage * $pageNo;
            $withdraws = $withdraws->skip($skip)->take($perPage)->get();
            $paginationData = [
                'currentPage'         => $pageNo,
                'nextPage'         => $pageNo+1,
                'totalPages'         => round($withdraws->count()/$perPage),
                'totalItems'         => $withdraws->count(),
                'itemsPerPage'         => $perPage,
            ];
        }else{
            $withdraws = $withdraws->get();
        }
        Log::info($withdraws);
        $payload = [
            'status'            => true,
            'data'              => WithdrawHistoryCollection::collection($withdraws),
            'paginationData' =>  $paginationData,
            'app_message'       => 'Successfully Retrieve Data',
            'user_message'      => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);
    }
}
