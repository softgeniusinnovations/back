<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\Withdrawal;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Commision;
use GuzzleHttp\Client;

class WithdrawalController extends Controller
{
    public function pending()
    {
        $pageTitle   = 'Pending Withdrawals';
        $withdrawals = $this->withdrawalData('pending');
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function approved()
    {
        $pageTitle   = 'Approved Withdrawals';
        $withdrawals = $this->withdrawalData('approved');
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function rejected()
    {
        $pageTitle   = 'Rejected Withdrawals';
        $withdrawals = $this->withdrawalData('rejected');
        
        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals'));
    }

    public function agentAssignedWithDrawl(Request $r, $id)
    {
        if ($r->local == 'local' && ($r->agent_id == 1 || $r->agent_id == '')) {
            $notify[] = ['error', 'Please select agent first'];
            return back()->withNotify($notify);
        }
        $withdrawal = Withdrawal::where('status', Status::PAYMENT_PENDING)->findOrFail($id);
        if ($withdrawal->provider_id && $withdrawal->assign_agent == 1) {
            $withdrawal->agent_id = 1;
        }
        $withdrawal->assign_agent = !$withdrawal->assign_agent;

        if ($r->agent_id) {
            $withdrawal->agent_id = $r->agent_id;
        }
        if ($withdrawal->save()) {

            if ($withdrawal->assign_agent == 1) {
                $telegramText = '';
                $amount = $withdrawal->amount;
                $telegramText .= 'ASSIGNED AGENT
                    Withdraw Request No: ' . $withdrawal->trx . '
                    Agent: ' . @$withdrawal->agent->name . '
                    Amount: ' . $amount . $withdrawal->currency . '
                    Customer: ' . $withdrawal->user->username . '
                    ';
                try {
                    telegramNotification($telegramText);

                    if ($withdrawal->agent_id != 1) {
                        if ($withdrawal->agent->telegram_link) {
                            if ($withdrawal->agent->bot_token && $withdrawal->agent->channel_id) {
                                try {
                                    $client = new Client();
                                    $response = $client->get("https://api.telegram.org/bot{$withdrawal->agent->bot_token}/sendMessage", [
                                        'query' => [
                                            'chat_id' => $withdrawal->agent->channel_id,
                                            'text' => $telegramText,
                                        ],
                                    ]);

                                    $responseData = json_decode($response->getBody(), true);
                                    if ($responseData['ok']) {
                                        $notify[] = ['success', 'Notify Admin via telegram '];
                                        return back()->withNotify($notify);
                                    } else {
                                        $notify[] = ['error', 'No direction'];
                                        return back()->withNotify($notify);
                                    }
                                } catch (\Exception $e) {
                                    $notify[] = ['error', 'Telegram issue'];
                                    return back()->withNotify($notify);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $notify[] = ['error', 'Telegram issues'];
                    return back()->withNotify($notify);
                }
            }


            $notify[] = ['success', 'Sucessfull'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Something went wrong'];
            return back()->withNotify($notify);
        }
    }

    public function log()
    {
        $pageTitle      = 'Withdrawals Log';
        $withdrawalData = $this->withdrawalData($scope = null, $summery = true);
        $withdrawals    = $withdrawalData['data'];
        $summery        = $withdrawalData['summery'];
        $successful     = $summery['successful'];
        $pending        = $summery['pending'];
        $rejected       = $summery['rejected'];

        return view('admin.withdraw.withdrawals', compact('pageTitle', 'withdrawals', 'successful', 'pending', 'rejected'));
    }

    protected function withdrawalData($scope = null, $summery = false)
    {
        if ($scope) {
            $withdrawals = Withdrawal::$scope()->with('agent');
        } else {
            $withdrawals = Withdrawal::where('status', '!=', Status::PAYMENT_INITIATE)->with('agent');
        }
        if (!Auth::user()->can('super-admin')) {
            $withdrawals->where('agent_id', Auth::id());
        }
        $withdrawals = $withdrawals->searchable(['trx', 'user:username'])->with('transectionProviders', 'agent')->dateFilter();
        $request     = request();

        if ($request->method) {
            $withdrawals = $withdrawals->where('method_id', $request->method);
        }
        if (!$summery) {
            return $withdrawals->with(['user', 'method'])->orderBy('id', 'desc')->paginate(getPaginate());
        } else {

            $successful = clone $withdrawals;
            $pending    = clone $withdrawals;
            $rejected   = clone $withdrawals;

            $successfulSummery = $successful->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
            $pendingSummery    = $pending->where('status', Status::PAYMENT_PENDING)->sum('amount');
            $rejectedSummery   = $rejected->where('status', Status::PAYMENT_REJECT)->sum('amount');

            return [
                'data'    => $withdrawals->with(['user', 'method'])->orderBy('id', 'desc')->paginate(getPaginate()),
                'summery' => [
                    'successful' => $successfulSummery,
                    'pending'    => $pendingSummery,
                    'rejected'   => $rejectedSummery,
                ],
            ];
        }
    }

    public function details($id)
    {
        $withdrawal = Withdrawal::where('id', $id)->where('status', '!=', Status::PAYMENT_INITIATE)->with(['user', 'method', 'transectionProviders', 'agent'])->firstOrFail();
        $pageTitle  = $withdrawal->user->username . ' Withdraw Requested ' . showAmount($withdrawal->amount) . ' ' . $withdrawal->user->currency;
        $details    = $withdrawal->withdraw_information ? json_encode($withdrawal->withdraw_information) : null;

        $agents = Admin::where('type', 1)->where('status', 1)->where('is_login', 1)->get();

        return view('admin.withdraw.detail', compact('pageTitle', 'withdrawal', 'details', 'agents'));
    }

    public function approve(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        DB::beginTransaction();
        try {
            $withdraw                 = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with(['agent', 'user', 'transectionProviders'])->firstOrFail();

            $agent = Admin::find($withdraw->agent->id);
            if ((float) $agent->balance < (float) $withdraw->amount) {
                $notify[] = ['error', 'Insufficient balance, Please topup your balance.'];
                return back()->withNotify($notify);
            }


            $agent->decrement('balance', $withdraw->amount);

            $withdraw->status         = Status::PAYMENT_SUCCESS;
            $withdraw->admin_feedback = $request->details;
            $withdraw->save();


            $commission = new Commision();
            $commission->user_id = $withdraw->user_id;
            $commission->agent_id = $withdraw->agent_id;
            $commission->withdraw_id = $withdraw->id;
            $commission->type = 'Withdraw';
            $commission->commision = $agent->withdraw_commission;
            $commission->amount = (float) $withdraw->amount;
            $commission->final_amount = ((float) $withdraw->amount * ($agent->withdraw_commission / 100));
            $commission->save();
            $agent->increment('balance', ((float) $withdraw->amount * ($agent->withdraw_commission / 100)));

            DB::commit();
            // dd((float) $withdraw->amount * ($agent->withdraw_commission / 100), $agent);

            try {
                $telegramText = '';
                $telegramText .= 'WITHDRAW REQUEST APPROVED
            Deposit Request No: ' . $withdraw->trx . '
            Agent: ' . @$withdraw->agent->name . '
            Payment number: ' . $withdraw->phone . '
            Amount: ' . $withdraw->amount . $withdraw->currency . '
            Customer: ' . @$withdraw->user->username;
                telegramNotification($telegramText);
                if ($withdraw->agent_id != 1) {
                    if ($withdraw->agent->telegram_link) {
                        if ($withdraw->agent->bot_token && $withdraw->agent->channel_id) {
                            try {
                                $client = new Client();
                                $response = $client->get("https://api.telegram.org/bot{$withdraw->agent->bot_token}/sendMessage", [
                                    'query' => [
                                        'chat_id' => $withdraw->agent->channel_id,
                                        'text' => $telegramText,
                                    ],
                                ]);

                                $responseData = json_decode($response->getBody(), true);
                                if (!$responseData['ok']) {
                                    $notify[] = ['error', 'No direction'];
                                    return back()->withNotify($notify);
                                }
                            } catch (\Exception $e) {
                                $notify[] = ['error', 'Telegram issue'];
                                return back()->withNotify($notify);
                            }
                        }
                    }
                }
            } catch (\Exception $r) {
                $notify[] = ['error', 'Telegram Issue'];
            }
            notify($withdraw->user, 'WITHDRAW_APPROVE', [
                'method_name'     => @$withdraw->transectionProviders->name ?? 'Cash Agent',
                'method_currency' => $withdraw->currency,
                'method_amount'   => showAmount($withdraw->final_amount),
                'amount'          => showAmount($withdraw->amount),
                'charge'          => showAmount($withdraw->charge),
                'rate'            => showAmount($withdraw->rate),
                'trx'             => $withdraw->trx,
                'admin_details'   => $request->details,
            ]);

            $notify[] = ['success', 'Withdrawal approved successfully'];
            return to_route('admin.withdraw.pending')->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', 'Something went wrong'];
            return back()->withNotify($notify);
        }
    }

    public function reject(Request $request)
    {
        $request->validate(['id' => 'required|integer']);
        $withdraw = Withdrawal::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->with('user')->firstOrFail();

        $withdraw->status         = Status::PAYMENT_REJECT;
        $withdraw->admin_feedback = $request->details;
        $withdraw->save();

        $user = $withdraw->user;
        $user->withdrawal += $withdraw->amount;
        $user->save();

        $transaction               = new Transaction();
        $transaction->user_id      = $withdraw->user_id;
        $transaction->amount       = $withdraw->amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx_type     = '+';
        $transaction->remark       = 'withdraw_reject';
        $transaction->details      = showAmount($withdraw->amount) . ' ' . $withdraw->currency . ' Refunded from withdrawal rejection';
        $transaction->trx          = $withdraw->trx;
        $transaction->save();
        
        // if ($withdrawals) {
        //     $telegramText = '';
        //     $amount = @$withdrawals->amount;
        //     $telegramText .= 'WITHDRAW REQUEST REJECTED
        //             Withdraw Request No: ' . @$withdrawals->trx . '
        //             Agent: ' . @$withdrawals->agent->name . '
        //             Amount: ' . $amount . @$withdrawals->currency . '
        //             Customer: ' . @$withdrawals->user->username . '
        //             ';
        //     try {
        //         telegramNotification($telegramText);
        //     } catch (\Exception $e) {
        //         $notify[] = ['error', 'Telegram issues'];
        //         return back()->withNotify($notify);
        //     }
        // }

        notify($user, 'WITHDRAW_REJECT', [
            'method_name'     =>
            @$withdraw->transectionProviders->name ?? 'Cash Agent',
            'method_currency' => $withdraw->currency,
            'method_amount'   => showAmount($withdraw->final_amount),
            'amount'          => showAmount($withdraw->amount),
            'charge'          => showAmount($withdraw->charge),
            'rate'            => showAmount($withdraw->rate),
            'trx'             => $withdraw->trx,
            'post_balance'    => showAmount($user->balance),
            'admin_details'   => $request->details,
        ]);

        $notify[] = ['success', 'Withdrawal rejected successfully'];
        return to_route('admin.withdraw.pending')->withNotify($notify);
    }


    public function withdrawCommission()
    {
        $pageTitle = 'Withdraw Commissions';
        $comissions = Commision::with(['agent', 'user', 'withdrawl'])->whereNotNull('withdraw_id')->latest();
        if (Auth::user()->hasRole('super-admin')) {
            $comissions = $comissions->paginate(10);
        } else {
            $comissions = $comissions->where('agent_id', Auth::id())->paginate(10);
        }
        return view('admin.withdraw.commission', compact('pageTitle', 'comissions'));
    }
}
