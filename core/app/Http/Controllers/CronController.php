<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bet;
use App\Lib\Referral;
use App\Constants\Status;
use App\Models\Transaction;
use App\Models\GeneralSetting;
use App\Models\AffiliatePromos;
use App\Models\AffiliateCommissionTransaction;
use App\Models\TramcardUser;
use App\Models\BetDetail;
use App\Models\UserBonusList;
use App\Notifications\TramcardSendNotification;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Log;

class CronController extends Controller
{
    public function win()
    {
        $general                = GeneralSetting::first();
        $general->last_win_cron = Carbon::now();
        $general->save();
        $winBets = Bet::win()->amountReturnable()->orderBy('result_time', 'asc')->with('user')->limit(10)->get();
        $affiliate_commission = null;
        foreach ($winBets as $winBet) {

            $winBet->amount_returned = Status::NO;
            $winBet->result_time     = null;
            $winBet->save();
//            Log::info("return amount status".$winBet);
//            Log::info("inside loop");

            $user = $winBet->user;
            $betWithBonusStatus = Bet::join('user_bonus_lists', 'bets.user_id', '=', 'user_bonus_lists.user_id')
                ->where('bets.user_id', $user->id)
                ->where('bets.status', 2)
                ->get();

            $allStatusOne = BetDetail::where('bet_id', $winBet->id)
                ->where('status', '!=', 1)
                ->doesntExist();
//            Log::info("status 2".$allStatusOne);

            if($winBet->amount_type == 1){
                $user->withdrawal += $winBet->return_amount;
                $user->save();

                $transaction               = new Transaction();
                $transaction->user_id      = $user->id;
                $transaction->amount       = $winBet->return_amount;
                $transaction->post_balance = $user->balance + $winBet->return_amount;
                $transaction->trx_type     = '+';
                $transaction->trx          = $winBet->bet_number;
                $transaction->transection_type          = 1;
                $transaction->remark       = 'bet_won';
                $transaction->details      = 'For bet winning';
                $transaction->save();
                try {
                    $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
                    if ($affiliate_commission != null) {
//                    Log::info("outside bonus affiliate".$affiliate_commission);
//                    $commission_calculation = $winBet->stake_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                        $commission_calculation = $winBet->return_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
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
            // Bonus amount
            if($winBet->amount_type == 2){

//                Log::info("betWithBonusStatus".$betWithBonusStatus);
//                Log::info("amouny type bonus");
                $activeBonus = UserBonusList::where('user_id', $user->id)->first();
//                Log::info("active bonus".$activeBonus);
                if($activeBonus){
//                    if($activeBonus->is_returned!=1&&$activeBonus->rule_5!=1){
//                        $user->withdrawal += $winBet->return_amount;
//                        $user->save();
//                    }

                    if($activeBonus->rule_5==1||$activeBonus->rollover==$activeBonus->rollover_limit){
//                            Log::info("inside rulr_5");


                            if($betWithBonusStatus->isEmpty()){
//                                Log::info("user has no pending");
//                            $user->balance += $activeBonus->initial_amount;
                                $user->withdrawal += $activeBonus->initial_amount;
                                $user->bonus_account = 0;
                                $user->wining_bonus_amount += $activeBonus->initial_amount;
//                                Log::info("balance double");
                                $user->save();

                                $activeBonus->is_returned=1;
                                $activeBonus->save();
                                $transaction               = new Transaction();
                                $transaction->user_id      = $user->id;
                                $transaction->amount       = $winBet->return_amount;
                                $transaction->post_balance = $user->balance + $winBet->return_amount;
                                $transaction->trx_type     = '+';
                                $transaction->trx          = $winBet->bet_number;
                                $transaction->transection_type          = 2;
                                $transaction->remark       = 'bet_won';
                                $transaction->details      = 'For bet winning';
                                $transaction->save();

                                try {
                                    $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
                                    if ($affiliate_commission != null) {
//                    Log::info("outside bonus affiliate".$affiliate_commission);
//                    $commission_calculation = $winBet->stake_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                                        $company_expense_temp = $activeBonus->initial_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
//                                        $company_expenses=$activeBonus->initial_amount-$company_expense_temp;
//                                        $commission_calculation_bonus=$company_expenses*(optional($affiliate_commission->promo)->promo_percentage / 100);
                                        $affiliate_commission->affiliateUser->affiliate_temp_balance -= $company_expense_temp;
                                        $affiliate_commission->affiliateUser->save();

                                        $affiliate_commission->wining_bonus_amount+=$activeBonus->initial_amount;
                                        $affiliate_commission->save();

//                    Log::info($commission_calculation);

                                        $affiliate_transaction_log = new AffiliateCommissionTransaction();
                                        $affiliate_transaction_log->user_id = $user->id;
                                        $affiliate_transaction_log->affiliate_id = $affiliate_commission->affliate_user_id;
                                        $affiliate_transaction_log->promo_id = $affiliate_commission->promo_id;
                                        $affiliate_transaction_log->amount = $company_expense_temp;
//                                        $affiliate_transaction_log->company_expenses = $company_expenses;
                                        $affiliate_transaction_log->result = 2;
                                        $affiliate_transaction_log->save();
//                    Log::info("out side bonus ".$commission_calculation);
                                    }
                                } catch (\Exception $e) {
                                    Log::info($e);
                                    return notify('error', $e->getMessage());
                                }

                            }
                            if($allStatusOne && $activeBonus->is_returned!=1){
//                                Log::info("all status rule-5".$allStatusOne);
                                $user->bonus_account += $winBet->return_amount;
                                $user->save();
                            }
                            else{
//                                Log::info("user bonus =0 ");
                                $user->bonus_account =0 ;
                                $user->save();
                            }



                            // Send Notification
                            $userNotify = new UserNotification;
                            $userNotify->user_id = $user->id;
                            $userNotify->title = "Congratulations! You won the game, Please check the bonus balance ";
                            $userNotify->url = "";
                            $userNotify->save();

                            $userNotify->notify(new TramcardSendNotification($userNotify));

                        }
                    else{
//                            Log::info("outside rule_5");
                            if($allStatusOne){
//                                Log::info("all status 1".$allStatusOne);
                                $user->bonus_account += $winBet->return_amount;
                                $user->save();
                            }
                            else{
//                                Log::info("status have pending".$allStatusOne);
                                $user->bonus_account += 0;
                                $user->save();
                            }

                            $userNotify = new UserNotification;
                            $userNotify->user_id = $user->id;
                            $userNotify->title = "You didnt satisfied the rollover condition ";
                            $userNotify->url = "";
                            $userNotify->save();

                            $userNotify->notify(new TramcardSendNotification($userNotify));
                        }

                }
            }
            
            // Bet win and tramcard data update
            if($winBet->amount_type == 3){
                $tramcard = TramcardUser::where('user_id', $user->id)->first();
                if($tramcard){
                    $tramcard->amount += $winBet->stake_amount;
                    $tramcard->is_win = 1;
                    $tramcard->save();

                    $transaction               = new Transaction();
                    $transaction->user_id      = $user->id;
                    $transaction->amount       = $winBet->return_amount;
                    $transaction->post_balance = $user->balance + $winBet->return_amount;
                    $transaction->trx_type     = '+';
                    $transaction->trx          = $winBet->bet_number;
                    $transaction->transection_type          = 3;
                    $transaction->remark       = 'bet_won';
                    $transaction->details      = 'For bet winning';
                    $transaction->save();

                    try {
                        $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
                        if ($affiliate_commission != null) {
//                    Log::info("outside bonus affiliate".$affiliate_commission);
//                    $commission_calculation = $winBet->stake_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                            $commission_calculation = $winBet->return_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
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
                    
                     // Send Notification
                     $userNotify = new UserNotification;
                     $userNotify->user_id = $user->id;
                     $userNotify->title = "Congratulations! You won the game please claim the tramcard amount ";
                     $userNotify->url = "/user/tramcards";
                     $userNotify->save();
                    
                     $userNotify->notify(new TramcardSendNotification($userNotify));
                }
            }
//            else{
//                Log::info("croncontroller else");
//                if($winBet->amount_type=2){
//                    if($betWithBonusStatus->isEmpty()){
//                        Log::info("winBet->amount_type2 and no pending ");
//                        $user->withdrawal += 0;
//                        $user->save();
//                    }
//                    else{
//                        Log::info("winBet->amount_type2 and has pending ");
//                        $user->withdrawal += 0;
//                        $user->save();
//                    }
//
//                }
//                else{
//                    Log::info("croncontroller else else ");
//                    $user->withdrawal += $winBet->return_amount;
//                    $user->save();
//                }
//
//            }
            

//            $transaction               = new Transaction();
//            $transaction->user_id      = $user->id;
//            $transaction->amount       = $winBet->return_amount;
//            $transaction->post_balance = $user->balance + $winBet->return_amount;
//            $transaction->trx_type     = '+';
//            $transaction->trx          = $winBet->bet_number;
//            $transaction->remark       = 'bet_won';
//            $transaction->details      = 'For bet winning';
//            $transaction->save();

            //affiliate commision

            if ($general->win_commission) {
                Referral::levelCommission($user, $winBet->return_amount, $winBet->bet_number, 'win');
            }

            notify($user, 'BET_WIN', [
                'username'   => $user->username,
                'amount'     => $winBet->return_amount,
                'bet_number' => $winBet->bet_number,
            ]);
        }

        return 'executed';
    }

    public function lose()
    {
        $general                 = GeneralSetting::first();
        $general->last_lose_cron = Carbon::now();
        $general->save();
        $affiliate_commission = null;

        $loseBets = Bet::lose()->amountReturnable()->orderBy('result_time', 'asc')->with('user')->limit(10)->get();
//        Log::info("outside loop losebet");
//        Log::info($loseBets);

        foreach ($loseBets as $loseBet) {
            if($loseBet->is_half==1){
                $loseBet->amount_returned = Status::NO;
                $loseBet->save();

                $user = $loseBet->user;
                $user->balance += $loseBet->stake_amount/2;
                $user->save();

            }
            else{
                $loseBet->amount_returned = Status::NO;
                $loseBet->save();

                $user = $loseBet->user;
//            Log::info("inside loop user");
//            Log::info($user);

                //affiliate commision
                try {
                    $affiliate_commission = AffiliatePromos::where('better_user_id', $user->id)->first();
//                Log::info("try affiliate_commission");
//                Log::info($affiliate_commission);
                    if ($affiliate_commission != null) {
//                    $commission_calculation = $loseBet->stake_amount * (optional($affiliate_commission->promo)->promo_percentage / 100);
                        $player_loss=$loseBet->stake_amount;
//                    Log::info("player_loss stack amount");
//                    Log::info($player_loss);
//                    Log::info("promo info");
//                    Log::info($affiliate_commission->promo->company_expenses);
                        $company_expenses=$player_loss*(optional($affiliate_commission->promo)->company_expenses/100);
//                    Log::info("company_expenses");
//                    Log::info($company_expenses);
                        $commission_calculation_temp_company_expense = $player_loss -$company_expenses;
//                    Log::info("commission_calculation_temp");
//                    Log::info($commission_calculation_temp_company_expense);
                        $commission_calculation = $commission_calculation_temp_company_expense*(optional($affiliate_commission->promo)->promo_percentage/100);
//                    Log::info("commission_calculation");
//                    Log::info($commission_calculation);
//                    Log::info("affiliate_commission->affiliateUser->affiliate_temp_balance");
//                    Log::info($affiliate_commission->affiliateUser->affiliate_temp_balance);
                        $affiliate_commission->affiliateUser->affiliate_temp_balance += $commission_calculation;
                        $affiliate_commission->affiliateUser->save();
//                    Log::info("affiliate_commission->affiliateUser");
//                    Log::info($affiliate_commission->affiliateUser);

                        $affiliate_transaction_log = new AffiliateCommissionTransaction();
                        $affiliate_transaction_log->user_id = $user->id;
                        $affiliate_transaction_log->affiliate_id = $affiliate_commission->affliate_user_id;
                        $affiliate_transaction_log->promo_id = $affiliate_commission->promo_id;
                        $affiliate_transaction_log->amount = $commission_calculation;
                        $affiliate_transaction_log->company_expenses = $commission_calculation_temp_company_expense;
                        $affiliate_transaction_log->result = 1;
                        $affiliate_transaction_log->save();
//                    Log::info("affiliate_transaction_log save");
//                    Log::info($affiliate_transaction_log);
                    }
                } catch (\Exception $e) {
                    return notify('error', $e->getMessage());
                }

            }

            notify($user, 'BET_LOSE', [
                'username'   => $user->username,
                'amount'     => $loseBet->stake_amount,
                'bet_number' => $loseBet->bet_number,
            ]);
        }

        return 'executed';
    }

    public function refund()
    {
        $general                   = GeneralSetting::first();
        $general->last_refund_cron = Carbon::now();
        $general->save();

        $refundBets = Bet::refunded()->amountReturnable()->orderBy('result_time', 'asc')->with('user')->limit(10)->get();

        foreach ($refundBets as $refundBet) {
            $refundBet->amount_returned = Status::NO;
            $refundBet->save();

            $user = $refundBet->user;
            if($refundBet->amount_type == 3){
                $tramcard = TramcardUser::where('user_id', $user->id)->first();
                if($tramcard){
                    $tramcard->amount += $refundBet->stake_amount;
                    $tramcard->is_win = 0;
                    $tramcard->save();
                }
            }
            if($refundBet->amount_type == 2){
                $activeBonus = UserBonusList::where('user_id', $user->id)->first();
                if($activeBonus){
                    $user->bonus_account += $refundBet->stake_amount;
                    $user->save();
                }
            }
            else{
                $user->balance += $refundBet->stake_amount;
                $user->save();
            }
           
            $transaction               = new Transaction();
            $transaction->user_id      = $user->id;
            $transaction->amount       = $refundBet->stake_amount;
            $transaction->post_balance = $user->balance;
            $transaction->trx_type     = '+';
            $transaction->trx          = $refundBet->bet_number;
            $transaction->remark       = 'bet_refunded';
            $transaction->details      = 'For bet refund';
            $transaction->save();

            notify($user, 'BET_REFUNDED', [
                'username'   => $user->username,
                'amount'     => $refundBet->stake_amount,
                'bet_number' => $refundBet->bet_number,
            ]);
        }
    }
}
