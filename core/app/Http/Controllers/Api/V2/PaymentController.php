<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\News;
use App\Models\User;
use App\Lib\Referral;
use App\Models\Admin;
use App\Models\Bonuse;
use GuzzleHttp\Client;
use App\Models\Deposit;
use App\Constants\Status;
use App\Lib\FormProcessor;
use App\Models\Transaction;
use App\Models\Threshold;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use App\Models\AdminNotification;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TransectionProviders;
use Illuminate\Support\Facades\Auth;
use Telegram\Bot\Laravel\Facades\Telegram;

class PaymentController extends Controller
{

    public function deposit()
    {
        $event_id = null;
        if (isset($_GET['id'])) {
            $event_id = decrypt($_GET['id']);
            session(['event_id' => $event_id]);
        }
        $gatewayCurrency = GatewayCurrency::whereHas('method', function ($gate) {
            $gate->where('status', Status::ENABLE);
        })->with('method')->orderby('method_code')->get();


        $providers = TransectionProviders::where('status', 1)->where('country_code', Auth::user()->country_code)->get();
        $pageTitle = 'Payment system in your region';
        return view($this->activeTemplate . 'user.payment.deposit', compact('gatewayCurrency', 'providers', 'pageTitle', 'event_id'));
    }

    // Get agent by provider
    public function getAgentByProvider(Request $request)
    {
        $providerId = $request->provider;
        if ($providerId) {
            $depositedAgents = Deposit::where('provider_id', $providerId)->where('user_id', Auth::id())->pluck('agent_id')->toArray();

            $freeAgent =
                DB::table('admin_transection_providers as atp')
                ->where('atp.transection_provider_id', $providerId)
                ->where('atp.status', 1)
                ->leftJoin('transection_providers as tp', 'atp.transection_provider_id', '=', 'tp.id')
                ->leftJoin('admins', 'atp.admin_id', '=', 'admins.id')
                ->where('admins.status', 1)
                ->select('atp.id as atp_id', 'atp.admin_id', 'atp.transection_provider_id', 'atp.wallet_name', 'atp.mobile', 'tp.note_dep as dep', 'tp.note_with as with_note', 'tp.name as method_name')
                ->whereNotIn('atp.admin_id', $depositedAgents)
                ->inRandomOrder()
                ->limit(1)
                ->get();
            if ($freeAgent->count() > 0) {
                $agents = $freeAgent;
            } else {
                $agents = DB::table('admin_transection_providers as atp')
                    ->where('atp.transection_provider_id', $providerId)
                    ->where('atp.status', 1)
                    ->leftJoin('transection_providers as tp', 'atp.transection_provider_id', '=', 'tp.id')
                    ->leftJoin('admins', 'atp.admin_id', '=', 'admins.id')
                    ->where('admins.status', 1)
                    ->select('atp.id as atp_id', 'atp.admin_id', 'atp.transection_provider_id', 'atp.wallet_name', 'atp.mobile', 'tp.note_dep as dep', 'tp.note_with as with_note', 'tp.name as method_name')
                    ->inRandomOrder()
                    ->limit(1)
                    ->get();
            }
        }
        if (count($agents) > 0) {
            return response()->json(['success' => $agents]);
        } else {
            return response()->json(['message' => 'No agent available right now.']);
        }
    }

    public function depositInsert(Request $request)
    {
        if ($request->payment_gateway) {
            if ($request->payment_gateway == 'local') {
                $request->validate([
                    'payment_gateway'   => ['required', 'in:local,cash'],
                    'provider' => 'required',
                    'agent'   => 'required|numeric',
                    'amount'   => 'required|numeric',
                    'transaction_id'   => 'required|unique:deposits,method_trx_number',
                    'payment_number'   => 'required',
                    'depositor_name'    => 'nullable'
                ]);

                $agentCheck = DB::table('admin_transection_providers')->where('transection_provider_id', $request->provider)->where('admin_id', $request->agent)->first();
                if (!$agentCheck) {
                    $notify[] = ['error', 'Please try again'];
                    return back()->withNotify($notify);
                }

                $data = new Deposit();
                $data->method_trx_number = $request->transaction_id;
                $data->depositor_name = $request->depositor_name;
                $data->user_id = Auth::user()->id;
                $data->agent_id = $request->agent;
                $data->gateway = $request->payment_gateway;
                $data->provider_id = $request->provider;
                $data->payment_number = $request->payment_number;
                $data->amount = $request->amount;
                $data->method_currency = Auth::user()->currency;
                $data->btc_amo         = 0;
                $data->btc_wallet      = "";
                $data->final_amo = $request->amount;
                $data->trx = getTrx();
                $data->status = Status::PAYMENT_PENDING;

            } else {
                $request->validate([
                    'payment_gateway'   => ['required', 'in:local,cash'],
                    'agent'   => 'required|numeric',
                    'depositor_name'   => 'nullable',
                    'phone'   => 'required',
                    'amount'   => 'required|numeric|gt:299|lt:20001',
                ]);


                $data = new Deposit();
                $data->method_trx_number = getTrx();
                $data->user_id = Auth::user()->id;
                $data->agent_id = $request->agent;
                $data->gateway = $request->payment_gateway;
                $data->amount = 0;
                $data->depositor_name = $request->depositor_name;
                $data->payment_number = $request->phone;
                $data->method_currency = Auth::user()->currency;
                $data->btc_amo         = 0;
                $data->btc_wallet      = "";
                $data->final_amo = 0;
                $data->trx = getTrx();
                $data->status = Status::PAYMENT_PENDING;
            }
        } else {
            $request->validate([
                'amount'   => 'required|numeric|gt:0',
                'gateway'  => 'required',
                'currency' => 'required',
            ]);

            $user = auth()->user();

            $gate = GatewayCurrency::whereHas('method', function ($gate) {
                $gate->where('status', Status::ENABLE);
            })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();


            if (!$gate) {
                $notify[] = ['error', 'Invalid gateway'];
                return back()->withNotify($notify);
            }

            if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
                $notify[] = ['error', 'Please follow deposit limit'];
                return back()->withNotify($notify);
            }

            $charge    = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
            $payable   = $request->amount + $charge;
            $final_amo = $payable * $gate->rate;

            $data                  = new Deposit();
            $data->user_id         = $user->id;
            $data->method_code     = $gate->method_code;
            $data->method_currency = strtoupper($gate->currency);
            $data->amount          = $request->amount;
            $data->charge          = $charge;
            $data->rate            = $gate->rate;
            $data->final_amo       = $final_amo;
            $data->btc_amo         = 0;
            $data->btc_wallet      = "";
            $data->trx             = getTrx();
        }
        if ($data->save()) {
            if(session('event_id') != null && $data->payment_gateway === 'local'){
                $this->getBonuse(Auth::user()->id, $request->amount, $data->id);
            }
            if ($request->payment_gateway) {
                $telegramText = '';
                if ($data->status == 2) {
                    $amount = $request->payment_gateway == 'local' ? $data->amount . $data->method_currency : 'wait for agent request';
                    $telegramText .= 'DEPOSIT REQUEST
                    Deposit Request No: ' . $data->trx . '
                    Agent: ' . @$data->agent->name . '
                    Payment number: ' . $data->payment_number . '
                    Amount: ' . $amount . '
                    Customer: ' . @$data->user->username . '
                    ext_trn_id: ' . $data->method_trx_number . '
                    ';
                    if ($request->payment_gateway == 'local') {
                        $telegramText .= 'আপনার ' . @$data->transectionProviders->name . ' ওয়ালেট নম্বর ' . $data->payment_number . ' আমরা শুধু ক্যাশ গ্রহণ করে থাকি';
                    } else {
                        $telegramText .= 'আমরা শুধু ক্যাশ গ্রহণ করে থাকি';
                    }
                    try {
                        telegramNotification($telegramText);
                    } catch (\Exception $e) {
                        $notify[] = ['error', 'Telegram issues'];
                        return back()->withNotify($notify);
                    }
                    if ($data->agent->telegram_link) {
                        if ($data->agent->bot_token && $data->agent->channel_id) {
                            try {
                                $client = new Client();
                                $response = $client->get("https://api.telegram.org/bot{$data->agent->bot_token}/sendMessage", [
                                    'query' => [
                                        'chat_id' => $data->agent->channel_id,
                                        'text' => $telegramText,
                                    ],
                                ]);

                                $responseData = json_decode($response->getBody(), true);
                                if ($responseData['ok']) {
                                    if($data->gateway !='local' ){
                                        return redirect('https://' . $data->agent->telegram_link);
                                    }
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
                session()->put('Track', $data->trx);
                $notify[] = ['success', 'Deposit has been under processing. Please wait for confirmation'];
                return back()->withNotify($notify);
            } else {
                session()->put('Track', $data->trx);
                return to_route('user.deposit.confirm');
            }
        } else {
            $notify[] = ['error', 'Something went wrong'];
            return back()->withNotify($notify);
        }
    }

    public function appDepositConfirm($hash)
    {
        try {
            $id = decrypt($hash);
        } catch (\Exception $ex) {
            return "Sorry, invalid URL.";
        }
        $data = Deposit::where('id', $id)->where('status', Status::PAYMENT_INITIATE)->orderBy('id', 'DESC')->firstOrFail();
        $user = User::findOrFail($data->user_id);
        auth()->login($user);
        session()->put('Track', $data->trx);
        return to_route('user.deposit.confirm');
    }

    public function depositConfirm()
    {
        $track   = session()->get('Track');
        $deposit = Deposit::where('trx', $track)->where('status', 0)->orderBy('id', 'DESC')->with('gateway')->firstOrFail();

        if ($deposit->method_code >= 1000) {
            return to_route('user.deposit.manual.confirm');
        }

        $dirName = $deposit->gateway->alias;
        $new     = __NAMESPACE__ . '\\' . $dirName . '\\ProcessController';

        $data = $new::process($deposit);
        $data = json_decode($data);

        if (isset($data->error)) {
            $notify[] = ['error', $data->message];
            return to_route(gatewayRedirectUrl())->withNotify($notify);
        }

        if (isset($data->redirect)) {
            return redirect($data->redirect_url);
        }

        // for Stripe V3
        if (@$data->session) {
            $deposit->btc_wallet = $data->session->id;
            $deposit->save();
        }

        $pageTitle = 'Payment Confirm';
        return view($this->activeTemplate . $data->view, compact('data', 'pageTitle', 'deposit'));
    }

    public static function userDataUpdate($deposit, $isManual = null)
    {
        if ($deposit->status == Status::PAYMENT_INITIATE || $deposit->status == Status::PAYMENT_PENDING) {
            $deposit->status = Status::PAYMENT_SUCCESS;
            $deposit->save();

            $user = User::find($deposit->user_id);
            $user->balance += $deposit->amount;
            $user->save();


            $transaction               = new Transaction();
            $transaction->user_id      = $deposit->user_id;
            $transaction->amount       = $deposit->amount;
            $transaction->post_balance = $user->balance;
            $transaction->charge       = $deposit->charge;
            $transaction->trx_type     = '+';
            $transaction->details      = 'Deposit Via ' . @$deposit->transectionProviders->name ?? 'Cash-agent';
            $transaction->trx          = $deposit->trx;
            $transaction->remark       = 'deposit';
            $transaction->save();

            if (!$isManual) {
                $adminNotification            = new AdminNotification();
                $adminNotification->user_id   = $user->id;
                $adminNotification->title     = 'Deposit successful via ' . $deposit->transectionProviders->name ?? 'Cash-agent';
                $adminNotification->click_url = urlPath('admin.deposit.successful');
                $adminNotification->save();
            }

            notify($user, $isManual ? 'DEPOSIT_APPROVE' : 'DEPOSIT_COMPLETE', [
                'method_name'     => $deposit->transectionProviders->name ?? 'Cash-agent',
                'method_currency' => $deposit->method_currency,
                'method_amount'   => showAmount($deposit->final_amo),
                'amount'          => showAmount($deposit->amount),
                'charge'          => showAmount($deposit->charge),
                'rate'            => showAmount($deposit->rate),
                'trx'             => $deposit->trx,
                'post_balance'    => showAmount($user->balance),
            ]);

            if (gs()->deposit_commission) {
                Referral::levelCommission($user, $deposit->amount, $deposit->trx, 'deposit');
            }
        }
    }

    public function manualDepositConfirm()
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }
        if ($data->method_code > 999) {

            $pageTitle = 'Confirm Deposit';
            $method    = $data->gatewayCurrency();
            $gateway   = $method->method;
            return view($this->activeTemplate . 'user.payment.manual', compact('data', 'pageTitle', 'method', 'gateway'));
        }
        abort(404);
    }

    public function manualDepositUpdate(Request $request)
    {
        $track = session()->get('Track');
        $data  = Deposit::with('gateway')->where('status', Status::PAYMENT_INITIATE)->where('trx', $track)->first();
        if (!$data) {
            return to_route(gatewayRedirectUrl());
        }
        $gatewayCurrency = $data->gatewayCurrency();
        $gateway         = $gatewayCurrency->method;
        $formData        = $gateway->form->form_data;

        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData = $formProcessor->processFormData($request, $formData);

        $data->detail = $userData;
        $data->status = Status::PAYMENT_PENDING;
        $data->save();

        $adminNotification            = new AdminNotification();
        $adminNotification->user_id   = $data->user->id;
        $adminNotification->title     = 'Deposit request from ' . $data->user->username;
        $adminNotification->click_url = urlPath('admin.deposit.details', $data->id);
        $adminNotification->save();

        notify($data->user, 'DEPOSIT_REQUEST', [
            'method_name'     => $data->gatewayCurrency()->name,
            'method_currency' => $data->method_currency,
            'method_amount'   => showAmount($data->final_amo),
            'amount'          => showAmount($data->amount),
            'charge'          => showAmount($data->charge),
            'rate'            => showAmount($data->rate),
            'trx'             => $data->trx,
        ]);

        $notify[] = ['success', 'You have deposit request has been taken'];
        return to_route('user.deposit.history')->withNotify($notify);
    }

    public function mobCash()
    {
        $pageTitle = 'Mob Cash agent in your region';
        // $agents = Admin::whereIn('type', ['2', '3'])->where('is_login', 1)->where('country_code', Auth::user('admin')->country_code)->where('status', 1)->get();
        return view($this->activeTemplate . 'user.payment.mob-cash', compact('pageTitle'));
    }
    public function withdrawMobCash()
    {
        $pageTitle = 'Mob Cash agent in your region';
        // $agents = Admin::whereIn('type', ['2', '3'])->where('is_login', 1)->where('country_code', Auth::user('admin')->country_code)->where('status', 1)->get();
        return view($this->activeTemplate . 'user.payment.mob-cash-with', compact('pageTitle'));
    }

    public function mobCashAgents(Request $r)
    {
        $agents = Admin::select('id','identity', 'address','telegram_link')->whereIn('type', ['2', '3'])->where('is_login', 1)->where('country_code', Auth::user('admin')->country_code)->where('identity', 'like', '%' . $r->admin_id . '%')->where('status', 1)->get();

         $payload = [
            'status' => true,
            'data' => $agents,
            'app_message' => 'Successfully Retrive Data',
            'user_message' => 'Successfully Retrive Data'
        ];
        return response()->json($payload, 200);
    }

    private function getBonuse($userId, $deposit_amount, $deposit_id){
        try {
            $bonusAmount = News::where('id', session('event_id'))->first();
            $data = new Bonuse();
            $data->user_id = $userId;
            $data->news_id = session('event_id');
            $data->bonus_type = 'deposit';
            $data->deposit_id = $deposit_id;
            $data->bonus_amount = $deposit_amount * ($bonusAmount->bonus_percentage / 100);
            $data->status = 2;
            $data->save();
        } catch (Exception $e) {
            // Handle the exception
            error_log($e->getMessage());
        } finally {
            session()->forget('event_id');
        }
    }
}
