<?php

namespace App\Http\Controllers\User;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\Referral;
use App\Models\Bet;
use App\Models\BetDetail;
use App\Models\UserBonusList;
use App\Models\TramcardUser;
use App\Models\Transaction;
use Illuminate\Http\Request;

class BetController extends Controller {

    public function placeBet(Request $request) {
        $status = implode(',', [Status::SINGLE_BET, Status::MULTI_BET]);
        $request->validate([
            'type'         => "required|integer|in:$status",
            'stake_amount' => 'required_if:type,2|nullable|numeric|gt:0',
            'amount_type' => 'required'
        ]);

        $user    = auth()->user();
        $betType = $request->type;
        $bets    = collect(session('bets'));

        $isSuspended = $bets->contains(function ($bet) {
            return isSuspendBet($bet);
        });

        if ($isSuspended) {
            $notify[] = ['error', 'You have to remove suspended bet from bet slip'];
            return back()->withNotify($notify);
        }

        if (blank($bets)) {
            $notify[] = ['error', 'No bet item found in bet slip'];
            return back()->withNotify($notify);
        }

        if ($bets->count() < 2 && $betType == Status::MULTI_BET) {
            $notify[] = ['error', 'Multi bet requires more than one bet'];
            return back()->withNotify($notify);
        }

        $totalStakeAmount = $betType == Status::SINGLE_BET ? getAmount($bets->sum('stake_amount'), 8) : $request->stake_amount;

        $minLimit = $betType == Status::SINGLE_BET ? gs('single_bet_min_limit') : gs('multi_bet_min_limit');
        $maxLimit = $betType == Status::SINGLE_BET ? gs('single_bet_max_limit') : gs('multi_bet_max_limit');

        if ($totalStakeAmount < $minLimit) {
            $notify[] = ['error', 'Min stake limit ' . $minLimit . ' ' . gs('cur_text')];
            return back()->withNotify($notify);
        }
        if ($totalStakeAmount > $maxLimit) {
            $notify[] = ['error', 'Max stake limit ' . $maxLimit . ' ' . gs('cur_text')];
            return back()->withNotify($notify);
        }
        if($request->amount_type == 1){
            if ($totalStakeAmount > $user->balance) {
                $notify[] = ['error', "You don't have sufficient balance"];
                return back()->withNotify($notify);
            }

            $user->balance -= $totalStakeAmount;
            $user->save();
        }

        if($request->amount_type == 2){
            $bonus = UserBonusList::where('user_id', $user->id)->first();
            $rule_one = 0;
            $rule_two = 0;
            $rule_three = 0;
            $rule_four = 0;
            $rule_five = 0;
            if($betType == Status::SINGLE_BET){
                $notify[] = ['error', "Your bonus only use for multibet"];
                return back()->withNotify($notify);
            }else{
                $rule_two = 1;
            }
            if($totalStakeAmount < $bonus->initial_amount){
                 $notify[] = ['error', "Your minimum bonus bet amount is ". $bonus->initial_amount];
                 return back()->withNotify($notify);
            }
            if($totalStakeAmount > $user->bonus_account){
                $notify[] = ['error', "You don't have sufficient balance"];
                return back()->withNotify($notify);
            }
            if($bets->contains('checker', 'Live')){
                $notify[] = ['error', "Your bonus balance use only for upcoming game"];
                return back()->withNotify($notify);
            }else{
                 $rule_one = 1;
            }
            if(count($bets) <= 2){
                $notify[] = ['error', "At least select 3 odds"];
                return back()->withNotify($notify);
            }else{
                $rule_three  = 1;
                if($bets->where('odds', '>=', 1.8)->count() == $bets->count()){
                    $rule_four = 1;
                    $bonus->increment('rollover',1);
                    if($bonus->rollover > 2){
                        $rule_five = 1;
                    }
                }else{
                    $notify[] = ['error', "All possible odds must have more than or equal 1.8 rate"];
                    return back()->withNotify($notify);
                }
            }

            $user->bonus_account -=$totalStakeAmount;
            $user->save();
            $bonus->rule_1 = $bonus->rule_1 == 1 ? 1 : $rule_one;
            $bonus->rule_2 = $bonus->rule_2 == 1 ? 1 : $rule_two;
            $bonus->rule_3 = $bonus->rule_3 == 1 ? 1 : $rule_three;
            $bonus->rule_4 = $bonus->rule_4 == 1 ? 1 : $rule_four;
            $bonus->rule_5 = $bonus->rule_5 == 1 ? 1 : $rule_five;
            $bonus->save();

        }

        if($request->amount_type == 3) {
            $tramcard = TramcardUser::where('user_id', $user->id)->first();
            $rule_one = 0;
            $rule_two = 0;
            $rule_three = 0;
            $rule_four = 0;
            if($betType == Status::SINGLE_BET){
                $notify[] = ['error', "Your tramcard only use for multibet"];
                return back()->withNotify($notify);
            }
            if($totalStakeAmount > $tramcard->amount){
                $notify[] = ['error', "You don't have sufficient balance"];
                return back()->withNotify($notify);
            }
            if($totalStakeAmount < $tramcard->amount){
                $notify[] = ['error', "Your stack amount is the tramcard amount"];
                return back()->withNotify($notify);
            }
            if($bets->contains('checker', 'Live')){
                $notify[] = ['error', "Your tramcard use only for upcoming game"];
                return back()->withNotify($notify);
            }else{
                 $rule_one = 1;
            }
            if($totalStakeAmount == $tramcard->amount){
                $rule_two = 1;
            }
            if(count($bets) <= 2){
                $notify[] = ['error', "At least select 3 odds"];
                return back()->withNotify($notify);
            }else{
                $rule_three  = 1;
                if($bets->where('odds', '>=', 1.8)->count() >= 3){
                    $rule_four = 1;
                }else{
                    $notify[] = ['error', "All possible odds must have more than or equal 1.8 rate"];
                    return back()->withNotify($notify);
                }
            }
            $tramcard->amount -=$totalStakeAmount;
            $tramcard->rule_1 = $tramcard->rule_1 == 1 ? 1 : $rule_one;
            $tramcard->rule_2 = $tramcard->rule_2 == 1 ? 1 : $rule_two;
            $tramcard->rule_3 = $tramcard->rule_3 == 1 ? 1 : $rule_three;
            $tramcard->rule_4 = $tramcard->rule_4 == 1 ? 1 : $rule_four;
            $tramcard->save();
        }


        $transaction               = new Transaction();
        $transaction->user_id      = $user->id;
        $transaction->amount       = $totalStakeAmount;
        $transaction->post_balance = $user->balance;
        $transaction->trx_type     = '-';
        $transaction->details      = 'For bet placing';
        $transaction->trx          = getTrx();
        $transaction->remark       = 'bet_placed';
        $transaction->save();

        if ($betType == Status::SINGLE_BET) {
            $this->placeSingleBet();
        } else {
            $this->placeMultiBet($request->amount_type);
        }

        if (gs('bet_commission')) {
            Referral::levelCommission($user, $totalStakeAmount, $transaction->trx, 'bet');
        }
        session()->forget('bets');
        $notify[] = ['success', 'Bet placed successfully'];
        return back()->withNotify($notify);
    }

    private function placeSingleBet() {
        $betData = collect(session('bets'));

        foreach ($betData as $betItem) {
            $returnAmount = $betItem->stake_amount * $betItem->odds;
            $bet          = $this->saveBetData(Status::SINGLE_BET, $betItem->stake_amount, $returnAmount);
            $this->saveBetDetail($bet->id, $betItem);
        }
    }

    private function placeMultiBet($amount_type = 1) {

        $bet          = $this->saveBetData(Status::MULTI_BET, request()->stake_amount, 0, $amount_type );
        $returnAmount = $bet->stake_amount;
        $betData      = collect(session('bets'));
        foreach ($betData as $betItem) {
            $returnAmount *= $betItem->odds;
            $this->saveBetDetail($bet->id, $betItem);
        }

        $bet->return_amount = $returnAmount;
        $bet->save();
    }

    private function saveBetData($type, $stakeAmount, $returnAmount = 0, $amount_type= 1) {
        $bet                = new Bet();
        $bet->bet_number    = getTrx(8);
        $bet->user_id       = auth()->id();
        $bet->type          = $type;
        $bet->amount_type          = $amount_type;
        $bet->stake_amount  = $stakeAmount;
        $bet->return_amount = $returnAmount;
        $bet->status        = Status::BET_PENDING;
        $bet->save();

        return $bet;
    }

    private function saveBetDetail($betId, $betItem) {
        $betDetail              = new BetDetail();
        $betDetail->bet_id      = $betId;
        $betDetail->question_id = $betItem->question_id;
        $betDetail->option_id   = $betItem->option_id;
        $betDetail->odds        = $betItem->odds;
        $betDetail->status      = Status::BET_PENDING;
        $betDetail->save();
    }
}
