<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\News;
use App\Models\User;
use App\Helpers\Helper;
use App\Models\Bonuse;
use GuzzleHttp\Client;
use App\Models\Deposit;
use App\Constants\Status;
use Illuminate\Http\Request;
use App\Models\GatewayCurrency;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\TransectionProviders;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Api\DepositStore;
use App\Http\Requests\Api\DepositSubmit;
use App\Http\Resources\PaymentMethodCollection;
use App\Http\Resources\DepositHistoryCollection;
use App\Models\DepositBonusSetting;
use App\Models\UserBonusList;
use App\Models\DepositBonusTracker;
use App\Notifications\TramcardSendNotification;
use App\Models\UserNotification;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{

    public function getDepositPaymentData()
    {
        $providers = TransectionProviders::where('status', 1)->where('country_code', auth()->user()->country_code)->get();
        $mostUsedProvider = DB::table('deposits')
            ->select('transection_providers.*')
            ->join('transection_providers', 'deposits.provider_id', '=', 'transection_providers.id') // Join the provider table
            ->where('deposits.user_id', auth()->user()->id)
            ->groupBy('deposits.provider_id', 'transection_providers.id')
            ->orderByRaw('COUNT(deposits.provider_id) DESC')
            ->first();
        $mostUsedProvider = $mostUsedProvider ? [$mostUsedProvider] : [];
        $payload = [
            'status' => true,
            'data' => [
                'payment_method' => PaymentMethodCollection::collection($providers),
                'mostUsedProvider' =>  PaymentMethodCollection::collection($mostUsedProvider),
                'mob_cash_agent' => '',
            ],
            'app_message' => 'Successfully Retrive Data',
            'user_message' => 'Successfully Retrive Data'
        ];
        return response()->json($payload, 200);
    }

    public function depositStore(DepositStore $request)
    {
        $msgtext = '';
        $user = auth()->user();
        if ($request->payment_gateway) {
            if ($request->payment_gateway === 'local') {
                $agentCheck = DB::table('admin_transection_providers')
                    ->where('transection_provider_id', $request->provider)
                    ->where('admin_id', $request->agent)->first();
                if (!$agentCheck) {
                    $payload = [
                        'status' => false,
                        'app_message' => 'Please try again',
                        'user_message' => 'Please try again'
                    ];
                    return response()->json($payload, 200);
                }

                $insertData = [
                    'user_id' => auth()->user()->id,
                    'method_trx_number' => $request->transaction_id,
                    'depositor_name' => $request->depositor_name,
                    'agent_id' => $request->agent,
                    'gateway' => $request->payment_gateway,
                    'provider_id' => $request->provider,
                    'payment_number' => $request->payment_number,
                    'amount' => $request->amount,
                    'final_amo' => $request->amount,
                    'method_currency' => Auth::user()->currency,
                    'btc_amo' => 0,
                    'btc_wallet' => '',
                    'status' => Status::PAYMENT_PENDING,
                    'trx' => getNumberTrx(),
                ];
            } else {
                $insertData = [
                    'user_id' => $user->id,
                    'method_trx_number' => getTrx(),
                    'depositor_name' => $user->depositor_name,
                    'agent_id' => $request->agent,
                    'gateway' => $request->payment_gateway,
                    'payment_number' => $request->phone,
                    'amount' => 0,
                    'final_amo' => $request->amount,
                    'method_currency' => Auth::user()->currency,
                    'btc_amo' => 0,
                    'btc_wallet' => '',
                    'status' => Status::PAYMENT_PENDING,
                    'trx' => getNumberTrx(),
                ];
            }
        } else {

            $gate = GatewayCurrency::whereHas('method', function ($gate) {
                $gate->where('status', Status::ENABLE);
            })->where('method_code', $request->gateway)->where('currency', $request->currency)->first();

            if (!$gate) {
                $payload = [
                    'status' => false,
                    'app_message' => 'Invalid gateway.',
                    'user_message' => 'Invalid gateway.'
                ];
                return response()->json($payload, 200);
            }
            if ($gate->min_amount > $request->amount || $gate->max_amount < $request->amount) {
                $payload = [
                    'status' => false,
                    'app_message' => 'Please follow deposit limit.',
                    'user_message' => 'Please follow deposit limit.'
                ];
                return response()->json($payload, 200);
            }


            $charge = $gate->fixed_charge + ($request->amount * $gate->percent_charge / 100);
            $payable = $request->amount + $charge;
            $final_amo = $payable * $gate->rate;

            $insertData = [
                'method_code' => $gate->method_code,
                'user_id' => $user->id,
                'method_currency' => strtoupper($gate->currency),
                'amount' => $request->amount,
                'charge' => $charge,
                'rate' => $gate->rate,
                'final_amo' => $final_amo,
                'btc_amo' => 0,
                'btc_wallet' => "0",
                'trx' => getNumberTrx(),
            ];
        }
        $depositCreate = Deposit::create($insertData);

        if ($depositCreate) {
            if ($request->has('bonus')) {

                $depositBonusId = $request->bonus;
                $depositBonus = DepositBonusSetting::where('id', $depositBonusId)->first();

                if ($depositBonus) {

                    if ($depositBonus->bonus_type == 'days') {

                        $days = json_decode($depositBonus->days, true);
                        $today = date('l');

                        if (is_array($days) && in_array($today, $days)) {

                            $is_valid=Helper::checkTodaysWithdraw(auth()->user()->id);

                            if($is_valid==true){


                                $depositCreate->get_bonus=0;
                                $depositCreate->save();
                                $userNotify = new UserNotification();
                                $userNotify->user_id = auth()->user()->id;
                                $userNotify->title = "sorry! You dont get you bonus . you have withdrawl today";
                                $userNotify->url = "/user/bonus";
                                $userNotify->save();
                            }
                            else{

                                $depositCreate->get_bonus=1;
                                $depositCreate->save();
                                $this->depositBonusAdd($depositCreate, $depositBonus, $user);
                            }

                        }
                    }

                    if ($depositBonus->bonus_type == 'providers') {
                        $providers = json_decode($depositBonus->providers, true);
                        if (is_array($providers) && in_array($request->provider, $providers)) {
                            $is_valid=Helper::checkTodaysWithdraw(auth()->user()->id,);
                            if($is_valid==true){

                                $depositCreate->get_bonus=0;
                                $depositCreate->save();
                                $userNotify = new UserNotification();
                                $userNotify->user_id = auth()->user()->id;
                                $userNotify->title = "sorry! You dont get you bonus . you have withdrawl today";
                                $userNotify->url = "/user/bonus";
                                $userNotify->save();
//
                            }
                            else{
                                $depositCreate->get_bonus=1;
                                $depositCreate->save();
                                $this->depositBonusAdd($depositCreate, $depositBonus, $user);
                            }
                        }
                    }
                }
            }
            if (Deposit::where('user_id', $depositCreate->user_id)->where('id', '<>', $depositCreate->id)->exists())
            {
//                Log::info("if first bonus");
                $depositCreate->first_deposit_bonus = 0;
                $depositCreate->save();
            }
            else {
//                Log::info("else first bonus");
                $user=User::where('id',$depositCreate->user_id)->first();
                if($user->first_bonus_check==1){
                    $depositCreate->first_deposit_bonus = 1;
                    $depositCreate->save();
//                $this->getBonuse(Auth::user()->id, $request->amount, $depositCreate->id);
                    $totalSeconds = 24 * 60 * 60;
                    $futureDateTime = Carbon::now()->addSeconds($totalSeconds);

                    $bonus = new DepositBonusTracker();
                    $bonus->user_id = $user->id;
                    $bonus->deposit_id = $depositCreate->id;
                    $bonus->initial_amount = $request->amount*(120/100) ;
                    $bonus->wager = 4;
                    $bonus->minimum_odd = 1.6;
                    $bonus->game_type = 3;
                    $bonus->currency = $user->currency;
                    $bonus->valid_time = $futureDateTime;
                    $bonus->duration = 1;
                    $bonus->duration_text = "24 Hours";
                    $bonus->save();
                    $userNotify = new UserNotification();
                    $userNotify->user_id = $user->id;
                    $userNotify->title = "Thanks for your deposit. Your deposit bonus waiting for approval.";
                    $userNotify->url = "/";
                    $userNotify->save();

                    if($bonus){


                        $bonus->is_claim=1;

                        $userNotify = new UserNotification();
                        $userNotify->user_id = $user->id;
                        $userNotify->title = "Congratulations! You  got you first deposit " . $bonus->initial_amount . $bonus->currency . " deposit bonus for " . $bonus->duration_text;
                        $userNotify->url = "/user/bonus";
                        $userNotify->save();
                    }
                    else {
                        $bonus->is_claim = 0;
                    }

                }



            }




            if ($request->event_id != null && $depositCreate->payment_gateway === 'local') {
                $is_valid=Helper::checkTodaysWithdraw(auth()->user()->id,);
                if($is_valid==true){

                    $depositCreate->get_bonus=0;
                    $depositCreate->save();
                    $userNotify = new UserNotification();
                    $userNotify->user_id = auth()->user()->id;
                    $userNotify->title = "sorry! You dont get you bonus . you have withdrawl today";
                    $userNotify->url = "/user/bonus";
                    $userNotify->save();
//
                }
                else{
                    $depositCreate->get_bonus=1;
                    $depositCreate->save();
                    $this->getBonuse(Auth::user()->id, $request->amount, $depositCreate->id);
                }
            }
            if ($request->payment_gateway) {
                $telegramText = '';
                if ($depositCreate->status == 2) {
                    $amount = $request->payment_gateway == 'local' ? $depositCreate->amount . $depositCreate->method_currency : 'wait for agent request';
                    $telegramText .= 'DEPOSIT REQUEST
                    Deposit Request No: ' . $depositCreate->trx . '
                    Agent: ' . @$depositCreate->agent->name . '
                    Payment number: ' . $depositCreate->payment_number . '
                    Amount: ' . $amount . '
                    Customer: ' . @$depositCreate->user->username . '
                    ext_trn_id: ' . $depositCreate->method_trx_number . '
                    ';
                    if ($request->payment_gateway == 'local') {
                        $telegramText .= 'আপনার ' . @$depositCreate->transectionProviders->name . ' ওয়ালেট নম্বর ' . $depositCreate->payment_number . ' আমরা শুধু ক্যাশ গ্রহণ করে থাকি';
                    } else {
                        $telegramText .= 'আমরা শুধু ক্যাশ গ্রহণ করে থাকি';
                    }
                    try {
                        telegramNotification($telegramText);
                    } catch (\Exception $e) {
                        $payload = [
                            'status' => true,
                            'notify_status' => 'error',
                            'telegram_link' => 'https://' . $depositCreate->agent->telegram_link,
                            'notify' => 'Telegram issues',

                            'app_message' => 'Successfully Stored Data',
                            'user_message' => 'Successfully Stored Data',
                            'first_bonus'=> $depositCreate->first_deposit_bonus,
                            'get_bonus'=>$depositCreate->get_bonus
                        ];
                        return response()->json($payload, 200);
                    }
                    if ($depositCreate->agent->telegram_link) {
                        if ($depositCreate->agent->bot_token && $depositCreate->agent->channel_id) {
                            try {
                                $client = new Client();
                                $response = $client->get("https://api.telegram.org/bot{$depositCreate->agent->bot_token}/sendMessage", [
                                    'query' => [
                                        'chat_id' => $depositCreate->agent->channel_id,
                                        'text' => $telegramText,
                                    ],
                                ]);

                                $responseData = json_decode($response->getBody(), true);
                                if ($responseData['ok']) {
                                    if ($depositCreate->gateway != 'local') {
                                        $payload = [
                                            'status' => true,
                                            'notify_status' => 'error',
                                            'telegram_link' => 'https://' . $depositCreate->agent->telegram_link,
                                            'notify' => 'No direction',
                                            'app_message' => 'Successfully Stored Data',
                                            'user_message' => 'Successfully Stored Data'
                                        ];
                                        return response()->json($payload, 200);
                                    }
                                } else {

                                    $payload = [
                                        'status' => true,
                                        'notify_status' => 'error',
                                        'telegram_link' => '',
                                        'notify' => 'No direction',
                                        'app_message' => 'Successfully Stored Data',
                                        'user_message' => 'Successfully Stored Data'
                                    ];
                                    return response()->json($payload, 200);
                                }
                            } catch (\Exception $e) {

                                $payload = [
                                    'status' => true,
                                    'notify_status' => 'error',
                                    'telegram_link' => '',
                                    'notify' => 'Telegram issue',
                                    'app_message' => 'Successfully Stored Data',
                                    'user_message' => 'Successfully Stored Data'
                                ];
                                return response()->json($payload, 200);
                            }
                        }
                    }
                }
                $payload = [
                    'status' => true,
                    'notify_status' => 'success',
                    'telegram_link' => '',
                    'notify' => 'Deposit has been under processing. Please wait for confirmation',
                    'app_message' => 'Successfully Stored Data',
                    'user_message' => 'Successfully Stored Data'
                ];
                return response()->json($payload, 200);
            }
        }
        $payload = [
            'status' => true,
            'notify_status' => '',
            'telegram_link' => '',
            'notify' => '',

            'app_message' => 'Successfully Stored Data ' . $msgtext,
            'user_message' => 'Successfully Stored Data ' . $msgtext
        ];
        return response()->json($payload, 200);
    }


    public function depositBonusAdd($depositCreate, $depositBonus, $user)
    {

        try {
            DB::beginTransaction();
            $amountCalculation = 0;
            $valid = [
                1 => "24 Hours",
                2 => "48 Hours",
                3 => "72 Hours",
                7 => "7 Days"
            ];

            $totalSeconds = $depositBonus->valid_time * 60 * 60;
            $futureDateTime = Carbon::now()->addSeconds($totalSeconds);

            if ($depositBonus->deposit_percentage > 0) {
                $amountCalculation = ($depositCreate->final_amo * $depositBonus->deposit_percentage) / 100; 
            } else {
                $amountCalculation = 0;
            }

            if ($amountCalculation < $depositBonus->min_bonus) {
                $amountCalculation = $depositBonus->min_bonus;
            }
            if ($amountCalculation > $depositBonus->max_bonus) {
                $amountCalculation = $depositBonus->max_bonus;
            }


            $bonus = new DepositBonusTracker();
            $bonus->user_id = $user->id;
            $bonus->deposit_id = $depositCreate->id;
            $bonus->initial_amount = $amountCalculation;
            $bonus->wager = $depositBonus->wager;
            $bonus->wager_limit = $depositBonus->wager;
            $bonus->rollover_limit = $depositBonus->rollover;
            $bonus->min_bet_multi = $depositBonus->minimum_bet;
            $bonus->minimum_odd = $depositBonus->odd_selection;
            $bonus->game_type = $depositBonus->game_type;
            $bonus->currency = $user->currency;
            $bonus->valid_time = $futureDateTime;
            $bonus->duration = $depositBonus->valid_time;
            $bonus->duration_text = $valid[$depositBonus->valid_time];
            $bonus->save();

            // Notify to user
            $userNotify = new UserNotification();
            $userNotify->user_id = $user->id;
            $userNotify->title = "Thanks for your deposit. Your deposit bonus waiting for approval.";
            $userNotify->url = "/";
            $userNotify->save();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }



    public function getDepositHistory($pageNo = null, $perPage = null)
    {
        $perPage = $perPage ?? 10;
        $paginationData = [];
        $deposits = auth()->user()->deposits()->searchable(['trx'])
            ->with(['gateway', 'transectionProviders'])
            ->orderBy('id', 'desc');

        $totalItems = $deposits->count();

        if ($pageNo) {
            $skip = $pageNo == 1 ? 0 : $perPage * ($pageNo - 1);
            $deposits = $deposits->skip($skip)->take($perPage)->get();
            $paginationData = [
                'currentPage' => $pageNo,
                'nextPage' => $pageNo + 1,
                'totalPages' => ceil($totalItems / $perPage),
                'totalItems' => $totalItems,
                'itemsPerPage' => $perPage,
            ];
        } else {
            $deposits = $deposits->get();
        }

        $payload = [
            'status' => true,
            'data' => DepositHistoryCollection::collection($deposits),
            'paginationData' => $paginationData,
            'app_message' => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }

    private function getBonuse($userId, $deposit_amount, $deposit_id)
    {
        try {
            $bonusAmount = News::where('id', session('event_id'))->first();
            $data = new Bonuse();
            $data->user_id = $userId;
            $data->news_id = session('event_id');
            $data->bonus_type = 'deposit';
            $data->deposit_id = $deposit_id;
            $data->bonus_amount = $deposit_amount * ($bonusAmount->bonus_percentage / 100);
//            $data->bonus_amount = $deposit_amount * (40 / 100);
            $data->status = 2;
            $data->save();



        } catch (Exception $e) {
            // Handle the exception
            error_log($e->getMessage());
        } finally {
            session()->forget('event_id');
        }
    }

    public function getAgentByProvider(Request $request)
    {
        $providerId = $request->provider;
        $agents = [];
    
        if ($providerId) {
            $depositedAgents = Deposit::where('provider_id', $providerId)->where('user_id', Auth::id())->pluck('agent_id')->toArray();


            $freeAgent =
                DB::table('admin_transection_providers as atp')
                ->where('atp.transection_provider_id', $providerId)
                ->where('atp.status', 1)
                ->leftJoin('transection_providers as tp', 'atp.transection_provider_id', '=', 'tp.id')
                ->leftJoin('admins', 'atp.admin_id', '=', 'admins.id')
                ->where('admins.status', 1)
                ->select('atp.id as atp_id', 'atp.admin_id', 'atp.transection_provider_id', 'atp.wallet_name', 'atp.mobile','atp.comment', 'tp.note_dep as dep', 'tp.note_with as with_note', 'tp.name as method_name')
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
                    ->select('atp.id as atp_id', 'atp.admin_id', 'atp.transection_provider_id', 'atp.wallet_name', 'atp.mobile','atp.comment', 'tp.note_dep as dep', 'tp.note_with as with_note', 'tp.name as method_name',)
                    ->inRandomOrder()
                    ->limit(1)
                    ->get();
            }
        }
        if (count($agents) > 0) {
            $payload = [
                'status' => true,
                'data' => $agents,
                'app_message' => 'Successfully Retrive Data',
                'user_message' => 'Successfully Retrive Data'
            ];
            return response()->json($payload, 200);
        } else {
            return response()->json(['status' => true, 'data' => [], 'message' => 'No agent available right now.']);
        }
    }
}
