<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\{AdminNotification, Transaction, TransectionProviders, Withdrawal, WithdrawMethod,};
use App\{
    Http\Controllers\Controller,
    Lib\FormProcessor,
};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Client;

class WithdrawController extends Controller
{

    public function withdrawMoney()
    {
        $providers = TransectionProviders::where('status', 1)->where('country_code', Auth::user()->country_code)->get();
        $pageTitle = 'Withdraw method in your region';
        return view($this->activeTemplate . 'user.withdraw.methods', compact('pageTitle', 'providers', 'providers'));
    }

    public function withdrawStore(Request $request)
    {
        
        // Withdraw fr affiliator 
        
        // if(Auth::user()->is_affiliate == 1 )
        // {
        //     if(\Carbon\Carbon::now()->format('l') != 'Monday' || ceil(convertCurrency($request->amount, Auth::user()->currency, 'USD')) < 30){
        //         $notify[] = ['error', 'Today is not Monday. You do not have sufficient balance to withdraw.'];
        //         return back()->withNotify($notify);
        //     }
        // }
        
        

        if ($request->payment_gateway) {
            if ($request->payment_gateway === 'local') {
                $request->validate([
                    'payment_gateway'   => ['required', 'in:local,cash'],
                    'provider' => 'required',
                    'agent'   => 'required|numeric',
                    'amount'   => 'required|numeric',
                    'phone' => 'required_if:payment_gateway,==,local'
                ]);

                $agentCheck = DB::table('admin_transection_providers')->where('transection_provider_id', $request->provider)->where('admin_id', $request->agent)->first();
                if (!$agentCheck) {
                    $notify[] = ['error', 'Please try again'];
                    return back()->withNotify($notify);
                }

                $withdraw = new Withdrawal();
                $withdraw->user_id = Auth::user()->id;
                $withdraw->agent_id = 1;
                $withdraw->phone = $request->phone;
                $withdraw->gateway = $request->payment_gateway;
                $withdraw->provider_id = $request->provider;
                $withdraw->amount = $request->amount;
                $withdraw->currency = Auth::user()->currency;
                $withdraw->final_amount = $request->amount;
                $withdraw->trx = getTrx();
                // $withdraw->status = Status::PAYMENT_PENDING;
            } else {
                $request->validate([
                    'payment_gateway'   => ['required', 'in:local,cash'],
                    'agent'   => 'required|numeric',
                    'amount'   => 'required|numeric',
                    'phone' => 'required_if:payment_gateway,==,local'
                ]);

                $withdraw = new Withdrawal();
                $withdraw->user_id = Auth::user()->id;
                $withdraw->agent_id = $request->agent;
                $withdraw->phone = $request->phone;
                $withdraw->gateway = $request->payment_gateway;
                $withdraw->amount = $request->amount;
                $withdraw->currency = Auth::user()->currency;
                $withdraw->final_amount = $request->amount;
                $withdraw->available_amount = Auth::user()->withdrawal;
                $withdraw->trx = getTrx();
                // $withdraw->status = Status::PAYMENT_PENDING;
            }
        } else {

            $this->validate($request, [
                'method_code' => 'required',
                'amount' => 'required|numeric'
            ]);
            $method = WithdrawMethod::where('id', $request->method_code)->where('status', Status::ENABLE)->firstOrFail();
            $user = auth()->user();
            if ($request->amount < $method->min_limit) {
                $notify[] = ['error', 'Your requested amount is smaller than minimum amount.'];
                return back()->withNotify($notify);
            }
            if ($request->amount > $method->max_limit) {
                $notify[] = ['error', 'Your requested amount is larger than maximum amount.'];
                return back()->withNotify($notify);
            }

            if ($request->amount > $user->withdrawal) {
                $notify[] = ['error', 'You do not have sufficient withdrawal balance for withdraw.'];
                return back()->withNotify($notify);
            }


            $charge = $method->fixed_charge + ($request->amount * $method->percent_charge / 100);
            $afterCharge = $request->amount - $charge;
            $finalAmount = $afterCharge * $method->rate;

            $withdraw = new Withdrawal();
            $withdraw->method_id = $method->id; // wallet method ID
            $withdraw->user_id = $user->id;
            $withdraw->amount = $request->amount;
            $withdraw->currency = $method->currency;
            $withdraw->rate = $method->rate;
            $withdraw->charge = $charge;
            $withdraw->final_amount = $finalAmount;
            $withdraw->after_charge = $afterCharge;
            $withdraw->available_amount = Auth::user()->withdrawal;
            $withdraw->trx = getTrx();
        }
        $withdraw->save();


        session()->put('wtrx', $withdraw->trx);
        return to_route('user.withdraw.preview');
    }

    public function withdrawPreview()
    {
        $withdraw = Withdrawal::with('method', 'user', 'agent', 'transectionProviders')->where('trx', session()->get('wtrx'))->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'desc')->firstOrFail();
        $pageTitle = 'Withdraw Preview';
        return view($this->activeTemplate . 'user.withdraw.preview', compact('pageTitle', 'withdraw'));
    }

    public function withdrawSubmit(Request $request)
    {

        $withdraw = Withdrawal::with('method', 'user')->where('trx', session()->get('wtrx'))->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'desc')->firstOrFail();

        // $method = $withdraw->method;
        // if ($method->status == Status::DISABLE) {
        //     abort(404);
        // }

        // $formData = $method->form->form_data;

        // $formProcessor = new FormProcessor();
        // $validationRule = $formProcessor->valueValidation($formData);
        // $request->validate($validationRule);
        // $userData = $formProcessor->processFormData($request, $formData);

        $user = auth()->user();
        if ($user->ts) {
            $response = verifyG2fa($user, $request->authenticator_code);
            if (!$response) {
                $notify[] = ['error', 'Wrong verification code'];
                return back()->withNotify($notify);
            }
        }

        if ($withdraw->amount > $user->withdrawal) {
            $notify[] = ['error', 'Your request amount is larger then your current withdrawal balance.'];
            return back()->withNotify($notify);
        }

        $withdraw->status = Status::PAYMENT_PENDING;
        $withdraw->withdraw_information = [];
        $withdraw->available_amount = Auth::user()->withdrawal;
        // $withdraw->withdraw_information = $userData;
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

        // try {
        //     $telegramText = '';
        //     $amount = $withdraw->amount;
        //     $telegramText .= 'WITHDRAW REQUEST
        //             Withdraw Request No: ' . $withdraw->trx . '
        //             Agent: ' . @$withdraw->agent->name . '
        //             Amount: ' . $amount . $withdraw->currency . '
        //             Customer: ' . $withdraw->user->username . '
        //             ';
        //     telegramNotification($telegramText);

        //     if ($withdraw->agent_id != 1) {
        //         if ($withdraw->agent->telegram_link) {
        //             if ($withdraw->agent->bot_token && $withdraw->agent->channel_id) {
        //                 try {
        //                     $client = new Client();
        //                     $response = $client->get("https://api.telegram.org/bot{$withdraw->agent->bot_token}/sendMessage", [
        //                         'query' => [
        //                             'chat_id' => $withdraw->agent->channel_id,
        //                             'text' => $telegramText,
        //                         ],
        //                     ]);

        //                     $responseData = json_decode($response->getBody(), true);
        //                     if ($responseData['ok']) {
        //                         return redirect('https://' . $withdraw->agent->telegram_link);
        //                     } else {
        //                         $notify[] = ['error', 'No direction'];
        //                         return back()->withNotify($notify);
        //                     }
        //                 } catch (\Exception $e) {
        //                     $notify[] = ['error', 'Telegram issue'];
        //                     return back()->withNotify($notify);
        //                 }
        //             }
        //         }
        //     }
        // } catch (\Exception $e) {
        //     $notify[] = ['error', 'Telegram issues'];
        //     return back()->withNotify($notify);
        // }

        notify($user, 'WITHDRAW_REQUEST', [
            'method_name' => @$withdraw->method->name ? @$withdraw->method->name : @$withdraw->transectionProviders->name ?? 'Cash agent',
            'method_currency' => $withdraw->currency,
            'method_amount' => showAmount($withdraw->final_amount),
            'amount' => showAmount($withdraw->amount),
            'trx' => $withdraw->trx,
            'post_balance' => showAmount($user->withdrawal),
        ]);

        $notify[] = ['success', 'Withdraw request sent successfully'];
        return to_route('user.withdraw.history')->withNotify($notify);
    }

    public function withdrawLog(Request $request)
    {
        $pageTitle = "Withdraw Log";
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
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());
        return view($this->activeTemplate . 'user.withdraw.log', compact('pageTitle', 'withdraws'));
    }


}
