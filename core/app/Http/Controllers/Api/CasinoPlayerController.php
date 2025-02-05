<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AffiliateCommissionTransaction;
use App\Models\AffiliatePromos;
use App\Models\UserBonusList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
class CasinoPlayerController extends Controller
{
    public function casinoPlayer(Request $request)
    {

        if($request->isMethod('post')){
            if($request->cmd === 'getBalance'){
                $validator = Validator::make($request->all(), [
                    'cmd' => 'required|in:getBalance',
                    'hall' => 'required',
                    'key' => 'required',
                    'login' => 'required',
                ]);
                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'fail',
                        'error' => $validator->errors()->first(),
                    ]);
                }

                if($request->input('hall')==3205824){

                    $user = $this->fetchBalanceFromDatabase($request->login);

                    if ($user === null) {
                        return response()->json([
                            'status' => 'fail',
                            'error' => 'user_not_found',
                        ]);
                    }

                    return response()->json([
                        'status' => 'success',
                        'error' => '',
                        'login' => $request->login,
                        // 'balance' => str_replace(',', '', number_format((float) convertCurrency($user->balance, $user->currency, 'EUR'), 2)),
                        // 'currency' => "EUR",
                        'balance' => str_replace(',', '', number_format((float) $user->casino_bonus_account, 2)),
                        'currency' => "BDT",

                    ]);
                }
                else{
                    $user = $this->fetchBalanceFromDatabase($request->login);

                    if ($user === null) {
                        return response()->json([
                            'status' => 'fail',
                            'error' => 'user_not_found',
                        ]);
                    }

                    $totalBalance= $user->balance + $user->withdrawal;
                    return response()->json([
                        'status' => 'success',
                        'error' => '',
                        'login' => $request->login,
                        // 'balance' => str_replace(',', '', number_format((float) convertCurrency($user->balance, $user->currency, 'EUR'), 2)),
                        // 'currency' => "EUR",
                        'balance' => str_replace(',', '', number_format((float) $totalBalance, 2)),
                        'currency' => "BDT",

                    ]);

                }


            }

            if($request->cmd === 'writeBet'){
                $validator = Validator::make($request->all(), [
                    'cmd' => 'required|in:writeBet',
                    'login' => 'required|string',
                    'hall' => 'required|integer',
                    'key' => 'required|string',
                    'bet' => 'required|numeric|min:0',
                    'win' => 'required|numeric|min:0',
                    'tradeId' => 'required|string',
                    'betInfo' => 'nullable|string',
                    'gameId' => 'required|integer',
                    'matrix' => 'nullable|string',
                    'sessionId' => 'required|integer',
                    'date' => 'nullable|date_format:Y-m-d H:i:s',
                    'WinLines' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'fail',
                        'error' => $validator->errors()->first(),
                    ], 400);
                }
                if($request->input('hall')==3205824){


                    $login = $request->input('login');
                    $hall = $request->input('hall');
                    $key = $request->input('key');
                    $bet = (float) $request->input('bet'); // Convert to float
                    $win = (float) $request->input('win'); // Convert to float
                    $tradeId = $request->input('tradeId');
                    $betInfo = $request->input('betInfo');
                    $gameId = $request->input('gameId');
                    $matrix = $request->input('matrix');
                    $sessionId = $request->input('sessionId');

                    $user = $this->fetchBalanceFromDatabase($login);


                    if ($user === null) {
                        return response()->json([
                            'status' => 'fail',
                            'error' => 'user_not_found',
                        ],200);
                    }


                    // $currentBalance = str_replace(',', '', number_format((float) convertCurrency($user->balance, $user->currency, 'EUR'), 2));
                    $currentBalance = str_replace(',', '', number_format((float) $user->casino_bonus_account, 2));
                    $withdrawalBalance = str_replace(',', '', number_format((float) $user->withdrawal, 2));

                    // Create bet session
                    if($bet > 0){
                        Cache::put('bet_'.$sessionId.'_'.$gameId, $bet, 120 );
                        if (Cache::has('win_'.$user->id)) {
                            Log::info('Cache "win_" exists');
                            $cache_win = Cache::get('win_'.$user->id);
                            Log::info($cache_win);


                            Cache::increment('win_'.$user->id, $win);

                            $updatedwin = Cache::get('win_'.$user->id);
                            Log::info($updatedwin);
                        } else {
                            Log::info('Cache "win_" does not exist. Initializing...');
                            // Initialize cache value
                            Cache::put('win_'.$user->id, $win, 86400);
                        }
                    }


                    if ($betInfo === 'refund') {
                        // $convertBalance = convertCurrency($bet, 'EUR', $user->currency);
                        $convertBalance = $bet;

                        $refunded = User::where('user_id', $login)->first();
                        $refunded->casino_bonus_account +=  $convertBalance;
                        $refunded->save();

                        $user->increment('casino_bonus_account', $convertBalance);
                        return response()->json([
                            'status' => 'success',
                            'error' => '',
                            'login' => $login,
                            // 'balance' => str_replace(',', '', number_format((float) convertCurrency($refunded->balance, $refunded->currency, 'EUR'), 2)),
                            'balance' => str_replace(',', '', number_format((float) $refunded->casino_bonus_account, 2)),
                            'currency' => 'BDT',
                            'operationId' => rand(100, 9999999999),
                        ], 200);
                    }

                    if ($bet > $currentBalance || $currentBalance < 0) {
                        return response()->json([
                            'status' => 'fail',
                            'error' => 'fail_balance',
                        ], 200);
                    }
                    else{



//                        $stable = $currentBalance - $bet;
//                         $adjustment=0;
//                        // User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) convertCurrency($adjustment,'EUR',$user->currency), 2))]);
//
//
//                        // Session and gameId dhore kaj korte hobe, karon last response a bet er data dai na tai bet er data dhore rakhte hobe
                        if($bet == 0){

                            $playBet = Cache::get('bet_'.$sessionId.'_'.$gameId);
                            if($win >= $playBet){
                                $user_bonus_list=UserBonusList::where('user_id',$user->id)->first();
                                Log::info("inside if play bet");
//                                Log::info($user_bonus_list);
                                $updatedwin = Cache::get('win_'.$user->id);

                                if($user_bonus_list&&$updatedwin>=$user_bonus_list->initial_amount*$user_bonus_list->wager_limit){
                                    $user_bonus_list->rule_1=1;
                                    $user_bonus_list->save();
                                    $adjustment = $user_bonus_list->initial_amount+$user->withdrawal;
                                    User::where('user_id',$login)->update(['withdrawal'=> str_replace(',', '', number_format((float) $adjustment, 2))]);
                                    try {
                                        $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
                                        if ($affiliate_commission != null) {
//                    Log::info("outside bonus affiliate".$affiliate_commission);
//                    $commission_calculation = $winBet->stake_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                                            $commission_calculation = $user_bonus_list->initial_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                                            $affiliate_commission->affiliateUser->affiliate_temp_balance -= $commission_calculation;
                                            $affiliate_commission->affiliateUser->save();

//                    Log::info($commission_calculation);

                                            $affiliate_transaction_log = new AffiliateCommissionTransaction();
                                            $affiliate_transaction_log->user_id = $user->id;
                                            $affiliate_transaction_log->affiliate_id = $affiliate_commission->affliate_user_id;
                                            $affiliate_transaction_log->promo_id = $affiliate_commission->promo_id;
                                            $affiliate_transaction_log->amount = $commission_calculation;
                                            $affiliate_transaction_log->company_expenses = 0;
                                            $affiliate_transaction_log->result = 2;
                                            $affiliate_transaction_log->save();
//                    Log::info("out side bonus ".$commission_calculation);
                                        }
                                    } catch (\Exception $e) {
                                        Log::info($e);
                                        return notify('error', $e->getMessage());
                                    }
                                    Cache::forget('bet_'.$sessionId.'_'.$gameId);
                                }

//                                $winBalance = $withdrawalBalance + ($win - $playBet);

                            }
                        }
                        else{
                            if($win>0){

                                $user_bonus_list=UserBonusList::where('user_id',$user->id)->first();
//                            Log::info("inside else play bet");
                                Log::info($user_bonus_list);
//                            Log::info($user_bonus_list->wager_limit);
//                            Log::info($win);
//                            Log::info($win*$user_bonus_list->wager_limit);
                                $updatedwin = Cache::get('win_'.$user->id);
                                Log::info("update win in else");
                                Log::info($updatedwin);
                                if($user_bonus_list&&$currentBalance>=$user_bonus_list->initial_amount*$user_bonus_list->wager_limit){
//                                Log::info("calculate aff");
//                                Log::info($win);
//                                Log::info($win*$user_bonus_list->wager_limit);
                                    $user_bonus_list->rule_1=1;
                                    $user_bonus_list->save();
                                    $adjustment = $user_bonus_list->initial_amount+$withdrawalBalance;
                                    User::where('user_id',$login)->update(['withdrawal'=> str_replace(',', '', number_format((float) $adjustment, 2))]);
                                    $user->casino_bonus_account=0.0000;
                                    $user->save();
                                    $user_bonus_list->delete();
                                    try {
                                        $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
                                        if ($affiliate_commission != null) {
//                    Log::info("outside bonus affiliate".$affiliate_commission);
//                    $commission_calculation = $winBet->stake_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                                            $commission_calculation = $user_bonus_list->initial_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                                            $affiliate_commission->affiliateUser->affiliate_temp_balance -= $commission_calculation;
                                            $affiliate_commission->affiliateUser->save();

//                    Log::info($commission_calculation);

                                            $affiliate_transaction_log = new AffiliateCommissionTransaction();
                                            $affiliate_transaction_log->user_id = $user->id;
                                            $affiliate_transaction_log->affiliate_id = $affiliate_commission->affliate_user_id;
                                            $affiliate_transaction_log->promo_id = $affiliate_commission->promo_id;
                                            $affiliate_transaction_log->amount = $commission_calculation;
                                            $affiliate_transaction_log->company_expenses = 0;
                                            $affiliate_transaction_log->result = 2;
                                            $affiliate_transaction_log->save();
//                    Log::info("out side bonus ".$commission_calculation);
                                        }
                                    } catch (\Exception $e) {
                                        Log::info($e);
                                        return notify('error', $e->getMessage());
                                    }
                                }
                                else{
                                    Log::info("inside win but not wager");
                                    $adjustment = $currentBalance+$win;
                                    Log::info($adjustment);
                                    User::where('user_id',$login)->update(['casino_bonus_account'=> str_replace(',', '', number_format((float) $adjustment, 2))]);
                                }
                            }
                            else{
                                $playBet = Cache::get('bet_'.$sessionId.'_'.$gameId);
                                $adjustment = $currentBalance-$playBet;
                                User::where('user_id',$login)->update(['casino_bonus_account'=> str_replace(',', '', number_format((float) $adjustment, 2))]);
                                Log::info("loss");
                            }

                        }


                        // else{
                        //   $adjustment = $stable;
                        //   $winBalance = $withdrawalBalance;
                        // }



                        $updatedData = User::where('user_id', $login)->first();

                        return response()->json([
                            'status' => 'success',
                            'error' => '',
                            'login' => $login,
                            // 'balance' => str_replace(',', '', number_format((float) convertCurrency($updatedData->balance, $updatedData->currency, 'EUR'), 2)),
                            'balance' => str_replace(',', '', number_format((float) $updatedData->casino_bonus_account, 2)),
                            'currency' => 'BDT',
                            'operationId' => rand(100, 9999999999),
                        ], 200);
                    }
                }
                else{
                    $login = $request->input('login');
                    $hall = $request->input('hall');
                    $key = $request->input('key');
                    $bet = (float) $request->input('bet'); // Convert to float
                    $win = (float) $request->input('win'); // Convert to float
                    $tradeId = $request->input('tradeId');
                    $betInfo = $request->input('betInfo');
                    $gameId = $request->input('gameId');
                    $matrix = $request->input('matrix');
                    $sessionId = $request->input('sessionId');

                    $user = $this->fetchBalanceFromDatabase($login);

                    if ($user === null) {
                        return response()->json([
                            'status' => 'fail',
                            'error' => 'user_not_found',
                        ],200);
                    }


                    // $currentBalance = str_replace(',', '', number_format((float) convertCurrency($user->balance, $user->currency, 'EUR'), 2));
                    $currentBalance = str_replace(',', '', number_format((float) $user->balance, 2));
                    $withdrawalBalance = str_replace(',', '', number_format((float) $user->withdrawal, 2));

                    $haveTotalBalance = $currentBalance + $withdrawalBalance;

                    // Create bet session
                    if($bet > 0){
                        Cache::put('bet_play_'.$sessionId.'_'.$gameId, $bet , 360 );
                    }

                    if(0 < $bet && $currentBalance <= $bet && $bet <= $haveTotalBalance){
                        $balanceFromWithdrawal = (float) ($bet - $currentBalance);
                        Cache::put('bet_'.$sessionId.'_'.$gameId, $balanceFromWithdrawal , 360 ); // How much amount get from withdrawal balance
                    }


                    if ($betInfo === 'refund') {
                        // $convertBalance = convertCurrency($bet, 'EUR', $user->currency);
                        $convertBalance = $bet;

                        if(Cache::has('bet_'.$sessionId.'_'.$gameId))
                        {
                            $sessionWithdrawalBalance = Cache::get('bet_'.$sessionId.'_'.$gameId); // If has need more balance from withdrawal
                        } else {
                            $sessionWithdrawalBalance = 0;
                        }

                        $refunded = User::where('user_id', $login)->first();

                        if($sessionWithdrawalBalance >= 0){
                            $refunded->balance +=  ($convertBalance - $sessionWithdrawalBalance);
                            $refunded->withdrawal +=  $sessionWithdrawalBalance;
                            $refunded->save();
                            Cache::forget('bet_'.$sessionId.'_'.$gameId);
                        }else{
                            $refunded->balance +=  $convertBalance;
                            $refunded->withdrawal +=  0;
                            $refunded->save();
                        }

                        $afterRefundedBalance = $refunded->balance + $refunded->withdrawal;
                        return response()->json([
                            'status' => 'success',
                            'error' => '',
                            'login' => $login,
                            // 'balance' => str_replace(',', '', number_format((float) convertCurrency($refunded->balance, $refunded->currency, 'EUR'), 2)),
                            'balance' => str_replace(',', '', number_format((float) $afterRefundedBalance, 2)),
                            'currency' => 'BDT',
                            'operationId' => rand(100, 9999999999),
                        ], 200);
                    }

                    if ($bet > $haveTotalBalance || $haveTotalBalance < 0) {
                        return response()->json([
                            'status' => 'fail',
                            'error' => 'fail_balance',
                        ], 200);
                    }else{

                        if(Cache::has('bet_'.$sessionId.'_'.$gameId))
                        {
                            $sessionWithdrawalBalance = Cache::get('bet_'.$sessionId.'_'.$gameId); // If has need more balance from withdrawal
                        } else {
                            $sessionWithdrawalBalance = 0;
                        }
                        if($sessionWithdrawalBalance > 0){
                            $stableCurrentBalance = $currentBalance - ($bet  - $sessionWithdrawalBalance);
                            $stableWithdrawalBalance = $withdrawalBalance - $sessionWithdrawalBalance;
                        }else{
                            $stableCurrentBalance = $currentBalance - $bet;
                            $stableWithdrawalBalance = $withdrawalBalance - $sessionWithdrawalBalance;
                        }

                        // $stable = $currentBalance - $bet;
                        // $adjustment = $stable  + $win;
                        // User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) convertCurrency($adjustment,'EUR',$user->currency), 2))]);


                        // Session and gameId dhore kaj korte hobe, karon last response a bet er data dai na tai bet er data dhore rakhte hobe
                        // if($bet == 0){

                        //     $playBet = Cache::get('bet_'.$sessionId.'_'.$gameId);
                        //     if($win >= $playBet){


                        //         $adjustment = $stable  + $playBet;
                        //         $winBalance = $withdrawalBalance + ($win - $playBet);


                        //         User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) $adjustment, 2)), 'withdrawal' => str_replace(',', '', number_format((float) $winBalance, 2))]);
                        //         Cache::forget('bet_'.$sessionId.'_'.$gameId);
                        //     }
                        // }else{
                        //     $adjustment = $stable  + $win;


                        //     User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) $adjustment, 2))]);
                        // }

                        if($bet == 0){
                            $playBet = Cache::get('bet_play_'.$sessionId.'_'.$gameId);
                            if($win >= $playBet){
                                Log::info('inside win>=playbet');
                                User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) $stableCurrentBalance, 2)), 'withdrawal' => str_replace(',', '', number_format((float) $stableWithdrawalBalance + $win, 2))]);
                                try {
                                    $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
//                                    Log::info('affiliate_commission');
//                                    Log::info($affiliate_commission);
                                    if ($affiliate_commission != null) {

                                        $commission_calculation = $win * (optional($affiliate_commission->promo)->promo_percentage / 100);
                                        $affiliate_commission->affiliateUser->affiliate_temp_balance -= $commission_calculation;
                                        $affiliate_commission->affiliateUser->save();

//                    Log::info($commission_calculation);

                                        $affiliate_transaction_log = new AffiliateCommissionTransaction();
                                        $affiliate_transaction_log->user_id = $user->id;
                                        $affiliate_transaction_log->affiliate_id = $affiliate_commission->affliate_user_id;
                                        $affiliate_transaction_log->promo_id = $affiliate_commission->promo_id;
                                        $affiliate_transaction_log->amount = $commission_calculation;
                                        $affiliate_transaction_log->company_expenses = 0;
                                        $affiliate_transaction_log->result = 2;
                                        $affiliate_transaction_log->save();
//                    Log::info("out side bonus ".$commission_calculation);
                                    }
                                } catch (\Exception $e) {
                                    Log::info($e);
                                    return notify('error', $e->getMessage());
                                }
                            }
                            Cache::forget('bet_'.$sessionId.'_'.$gameId);

                        }else{
                            Log::info("inside else");
                            User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) $stableCurrentBalance, 2)), 'withdrawal' => str_replace(',', '', number_format((float) $stableWithdrawalBalance + $win, 2))]);

                            try {
                                $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
                                Log::info('affiliate_commission');
                                Log::info($affiliate_commission);
                                if ($affiliate_commission != null) {

                                    $player_loss=$bet;

                                    $company_expenses=$player_loss*(optional($affiliate_commission->promo)->company_expenses/100);

                                    $commission_calculation_temp_company_expense = $player_loss -$company_expenses;

                                    $commission_calculation = $commission_calculation_temp_company_expense*(optional($affiliate_commission->promo)->promo_percentage/100);

                                    $affiliate_commission->affiliateUser->affiliate_temp_balance += $commission_calculation;
                                    $affiliate_commission->affiliateUser->save();

                                    $affiliate_transaction_log = new AffiliateCommissionTransaction();
                                    $affiliate_transaction_log->user_id = $user->id;
                                    $affiliate_transaction_log->affiliate_id = $affiliate_commission->affliate_user_id;
                                    $affiliate_transaction_log->promo_id = $affiliate_commission->promo_id;
                                    $affiliate_transaction_log->amount = $commission_calculation;
                                    $affiliate_transaction_log->company_expenses = $commission_calculation_temp_company_expense;
                                    $affiliate_transaction_log->result = 1;
                                    $affiliate_transaction_log->save();

                                }
                            } catch (\Exception $e) {
                                return notify('error', $e->getMessage());
                            }
                        }


                        // else{
                        //   $adjustment = $stable;
                        //   $winBalance = $withdrawalBalance;
                        // }



                        $updatedData = User::where('user_id', $login)->first();
                        $finalBalance = $updatedData->balance + $updatedData->withdrawal;

                        return response()->json([
                            'status' => 'success',
                            'error' => '',
                            'login' => $login,
                            'balance' => str_replace(',', '', number_format((float) $finalBalance, 2)),
                            'currency' => 'BDT',
                            'operationId' => rand(100, 9999999999),
                        ], 200);
                    }

                }
            }
        }else{
            return response()->json([
                'status' => 'fail',
                'error' => 'Method Not Allowed',
            ], 405);
        }

    }
    // Your method to fetch balance from the real database
    private function fetchBalanceFromDatabase($login)
    {
        $user = User::select('id','user_id', 'balance','casino_bonus_account', 'currency', 'withdrawal')->where('user_id', $login)->first();
        if($user){
            return $user;
        }
        return null;
    }

}
