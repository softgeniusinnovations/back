<?php

namespace App\Http\Controllers\Api\V2;

use App\Constants\Status; // Add this line
use App\Http\Controllers\Controller;
use App\Http\Resources\BetCollection;
use App\Lib\Referral;
use App\Models\Bet;
use App\Models\BetDetail;
use App\Models\Category;
use App\Models\Game;
use App\Models\League;
use App\Models\Option;
use App\Models\Question;
use App\Models\Team;
use App\Models\TramcardUser;
use App\Models\Transaction;
use App\Models\UserBonusList;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Http\Response;

class BetController extends Controller
{
    public function betStore(Request $request)
    {
//        Log::info($request);
        $validator = Validator::make($request->all(), [
            'stake_amount' => 'required|gt:0',
            'amount_type' => 'required|int',
            'bet_type' => 'required|int',
            'bets' => 'required',
            'bets.*.category' => 'required',
            'bets.*.leauge' => 'required',
            'bets.*.oddId' => 'required',
            'bets.*.bookmarkId' => 'required',
            'bets.*.matchId' => 'required',
            'bets.*.odd_details' => 'required',
            'bets.*.odds' => 'required',
            'bets.*.odds_point' => 'required',
            'bets.*.odd_name' => 'nullable',
            'bets.*.stake_amount' => 'required',
            'bets.*.return_amount' => 'required',
            'bets.*.checker' => 'required',
            'bets.*.status' => 'nullable',
            'bets.*.api_source_type' => 'required',
            'bets.*.is_live' => 'required',
            'bets.*.team1' => 'required',
            'bets.*.team2' => 'required',
            'bets.*.market_name' => 'required',
        ]);

        if ($validator->fails()) {
            $errors = $validator->errors();
            $payload = [
                'status' => false,
                'notify_status'  => 'error',
                'notify' => $errors->first(),
                'app_message' => $errors->first(),
                'user_message' => $errors->first(),
            ];
            return response()->json($payload, 200);
        }


        $user = auth()->user();
        $betType = $request->bet_type;
        $bets = $request->bets;

        // $isSuspended = $bets->contains(function ($bet) {
        //     return isSuspendBet($bet);
        // });

        // if ($isSuspended) {
        //     $notify[] = ['error', 'You have to remove suspended bet from bet slip'];
        //     return back()->withNotify($notify);
        // }

        if (blank($bets)) {
            $payload = [
                'status' => false,
                'notify_status'  => 'error',
                'notify' => 'No bet item found in bet slip',
                'app_message' => 'No bet item found in bet slip',
                'user_message' => 'No bet item found in bet slip',
            ];
            return response()->json($payload, 200);
        }

        if (count($bets) < 2 && $betType == Status::MULTI_BET) {
            $payload = [
                'status' => false,
                'notify_status'  => 'error',
                'notify' => 'Multi bet requires more than one bet',
                'app_message' => 'Multi bet requires more than one bet',
                'user_message' => 'Multi bet requires more than one bet',
            ];
            return response()->json($payload, 200);
        }

        $totalStakeAmount = $betType == Status::SINGLE_BET ? getAmount(array_sum(array_column($bets, 'stake_amount')), 8) : $request->stake_amount;
        $minLimit = $betType == Status::SINGLE_BET ? gs('single_bet_min_limit') : gs('multi_bet_min_limit');
        $maxLimit = $betType == Status::SINGLE_BET ? gs('single_bet_max_limit') : gs('multi_bet_max_limit');

        if ($totalStakeAmount < $minLimit) {
            $payload = [
                'status' => false,
                'notify_status'  => 'error',
                'notify' => 'Min stake limit ' . $minLimit . ' ' . gs('cur_text'),
                'app_message' => 'Min stake limit ' . $minLimit . ' ' . gs('cur_text'),
                'user_message' => 'Min stake limit ' . $minLimit . ' ' . gs('cur_text'),
            ];
            return response()->json($payload, 200);
        }

        if ($totalStakeAmount > $maxLimit) {
            $payload = [
                'status' => false,
                'notify_status'  => 'error',
                'notify' => 'Max stake limit ' . $maxLimit . ' ' . gs('cur_text'),
                'app_message' => 'Max stake limit ' . $maxLimit . ' ' . gs('cur_text'),
                'user_message' => 'Max stake limit ' . $maxLimit . ' ' . gs('cur_text'),
            ];
            return response()->json($payload, 200);
        }


        if ($request->amount_type == 1) {
            if ($totalStakeAmount > $user->balance) {
                $payload = [
                    'status' => false,
                    'notify_status'  => 'error',
                    'notify' => "You don't have sufficient balance",
                    'app_message' => "You don't have sufficient balance",
                    'user_message' => "You don't have sufficient balance",
                ];
                return response()->json($payload, 200);
            }

            $user->balance -= $totalStakeAmount;
            $user->save();
            $transaction = new Transaction();
            $transaction->user_id = $user->id;
            $transaction->amount       = $totalStakeAmount;
            $transaction->post_balance = $user->balance;
            $transaction->trx_type     = '-';
            $transaction->transection_type     = 1;

            $transaction->details      = 'For bet placing';
            $transaction->trx          = getTrx();
            $transaction->remark       = 'bet_placed';
            $transaction->save();
        }

        if ($request->amount_type == 2) {
            $bonus = UserBonusList::where('user_id', $user->id)->first();
            if($bonus) {
                $rule_one = 0;
                $rule_two = 0;
                $rule_three = 0;
                $rule_four = 0;
                $rule_five = 0;
                if ($betType == Status::SINGLE_BET) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "Your bonus only use for multibet",
                        'app_message' => "Your bonus only use for multibet",
                        'user_message' => "Your bonus only use for multibet",
                    ];
                    return response()->json($payload, 200);
                }
                else {
                    $rule_two = 1;
                }
//                minimum betamount 
//                if ($totalStakeAmount < $bonus->initial_amount) {
//                    $payload = [
//                        'status' => false,
//                        'notify_status'  => 'error',
//                        'notify' => "Your minimum bonus bet amount is " . $bonus->initial_amount,
//                        'app_message' => "Your minimum bonus bet amount is " . $bonus->initial_amount,
//                        'user_message' => "Your minimum bonus bet amount is " . $bonus->initial_amount,
//                    ];
//                    return response()->json($payload, 200);
//                }
                if ($totalStakeAmount > $user->bonus_account) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "You don't have sufficient balance",
                        'app_message' => "You don't have sufficient balance",
                        'user_message' => "You don't have sufficient balance",
                    ];
                    return response()->json($payload, 200);
                }
                if (collect($bets)->contains('checker', 'Live')) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "Your bonus balance use only for upcoming game",
                        'app_message' => "Your bonus balance use only for upcoming game",
                        'user_message' => "Your bonus balance use only for upcoming game",
                    ];
                    return response()->json($payload, 200);
                } else {
                    $rule_one = 1;
                }

                if (count($bets) < ($bonus->min_bet_multi ?? 2)) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "At least select {$bonus->min_bet_multi} odds",
                        'app_message' => "At least select {$bonus->min_bet_multi} odds",
                        'user_message' => "At least select {$bonus->min_bet_multi} odds",
                    ];
                    return response()->json($payload, 200);
                } else {
                    $rule_three  = 1;
                    if (collect($bets)->where('odds', '>=', $bonus->minimum_odd ?? 1.8)->count() == collect($bets)->count()) {
                        $rule_four = 1;
                        $bonus->increment('rollover', 1);
//                        $bonus->initial_amount-=$totalStakeAmount;
                        if ($bonus->rollover >= $bonus->rollover_limit) {
                            $rule_five = 1;
                        }
                    } else {
                        $payload = [
                            'status' => false,
                            'notify_status'  => 'error',
                            'notify' => "All possible odds must have more than or equal {$bonus->minimum_odd} rate",
                            'app_message' => "All possible odds must have more than or equal {$bonus->minimum_odd} rate",
                            'user_message' => "All possible odds must have more than or equal {$bonus->minimum_odd}  rate",
                        ];
                        return response()->json($payload, 200);
                    }
                }

                $user->bonus_account -= $totalStakeAmount;
                $user->save();
                $bonus->rule_1 = $bonus->rule_1 == 1 ? 1 : $rule_one;
                $bonus->rule_2 = $bonus->rule_2 == 1 ? 1 : $rule_two;
                $bonus->rule_3 = $bonus->rule_3 == 1 ? 1 : $rule_three;
                $bonus->rule_4 = $bonus->rule_4 == 1 ? 1 : $rule_four;
                $bonus->rule_5 = $bonus->rule_5 == 1 ? 1 : $rule_five;
                $bonus->save();
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount       = $totalStakeAmount;
                $transaction->post_balance = $user->balance;
                $transaction->trx_type     = '-';
                $transaction->transection_type     = 2;

                $transaction->details      = 'For bet placing';
                $transaction->trx          = getTrx();
                $transaction->remark       = 'bet_placed';
                $transaction->save();
//                Log::info("bonus in bet controller".$bonus);
            }else{
                $user->bonus_account = 0;
                $user->save();
                $payload = [
                    'status' => true,
                    'notify_status'  => 'error',
                    'notify' => "No active bonus found right now",
                    'app_message' => "No active bonus found right now",
                    'user_message' => "No active bonus found right now",
                ];
                return response()->json($payload, 200);
            }
        }

        if ($request->amount_type == 3) {
            $tramcard = TramcardUser::where('user_id', $user->id)->first();
            if($tramcard){
                $rule_one = 0;
                $rule_two = 0;
                $rule_three = 0;
                $rule_four = 0;
                if ($betType == Status::SINGLE_BET) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "Your tramcard only use for multibet",
                        'app_message' => "Your tramcard only use for multibet",
                        'user_message' => "Your tramcard only use for multibet",
                    ];
                    return response()->json($payload, 200);
                }
                if ($totalStakeAmount > $tramcard->amount) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "You don't have sufficient balance",
                        'app_message' => "You don't have sufficient balance",
                        'user_message' => "You don't have sufficient balance",
                    ];
                    return response()->json($payload, 200);
                }
                if ($totalStakeAmount < $tramcard->amount) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "Your stack amount is the tramcard amount",
                        'app_message' => "Your stack amount is the tramcard amount",
                        'user_message' => "Your stack amount is the tramcard amount",
                    ];
                    return response()->json($payload, 200);
                }
                if (collect($bets)->contains('checker', 'Live')) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "Your tramcard use only for upcoming game",
                        'app_message' => "Your tramcard use only for upcoming game",
                        'user_message' => "Your tramcard use only for upcoming game",
                    ];
                    return response()->json($payload, 200);
                } else {
                    $rule_one = 1;
                }
                if ($totalStakeAmount == $tramcard->amount) {
                    $rule_two = 1;
                }
                if (count($bets) <= 2) {
                    $payload = [
                        'status' => false,
                        'notify_status'  => 'error',
                        'notify' => "At least select 3 odds",
                        'app_message' => "At least select 3 odds",
                        'user_message' => "At least select 3 odds",
                    ];
                    return response()->json($payload, 200);
                } else {
                    $rule_three  = 1;
                    if (collect($bets)->where('odds', '>=', 1.8)->count() >= 3) {
                        $rule_four = 1;
                    } else {
                        $payload = [
                            'status' => false,
                            'notify_status'  => 'error',
                            'notify' => "All possible odds must have more than or equal 1.8 rate",
                            'app_message' => "All possible odds must have more than or equal 1.8 rate",
                            'user_message' => "All possible odds must have more than or equal 1.8 rate",
                        ];
                        return response()->json($payload, 200);
                    }
                }
                $tramcard->amount -= $totalStakeAmount;
                $tramcard->rule_1 = $tramcard->rule_1 == 1 ? 1 : $rule_one;
                $tramcard->rule_2 = $tramcard->rule_2 == 1 ? 1 : $rule_two;
                $tramcard->rule_3 = $tramcard->rule_3 == 1 ? 1 : $rule_three;
                $tramcard->rule_4 = $tramcard->rule_4 == 1 ? 1 : $rule_four;
                $tramcard->save();
                $transaction = new Transaction();
                $transaction->user_id = $user->id;
                $transaction->amount       = $totalStakeAmount;
                $transaction->post_balance = $user->balance;
                $transaction->trx_type     = '-';
                $transaction->transection_type     = 3;

                $transaction->details      = 'For bet placing';
                $transaction->trx          = getTrx();
                $transaction->remark       = 'bet_placed';
                $transaction->save();
            } else {
                $payload = [
                    'status' => true,
                    'notify_status'  => 'error',
                    'notify' => "No active tramcard found right now",
                    'app_message' => "No tramcard bonus found right now",
                    'user_message' => "No tramcard bonus found right now",
                ];
                return response()->json($payload, 200);
            }
        }



        if ($betType == Status::SINGLE_BET) {
            $this->placeSingleBet($bets);
        } else {
//            Log::info($bets);
            $this->placeMultiBet($bets, $request->amount_type);
        }

        if (gs('bet_commission')) {
            Referral::levelCommission($user, $totalStakeAmount, $transaction->trx, 'bet');
        }

        $payload = [
            'status' => true,
            'notify_status'  => 'success',
            'notify' => 'Bet placed successfully',
            'app_message' => 'Bet placed successfully',
            'user_message' => 'Bet placed successfully',
        ];

        return response()->json($payload, 200);
    }

    public function placeSingleBet($bets)
    {
        // dd($bets);
        foreach ($bets as $bet) {
            $returnAmount = $bet['odds_point'] * $bet['stake_amount'];
            $bet          = $this->saveBetData(Status::SINGLE_BET, $bet, $bet['stake_amount'], $returnAmount);
        }
    }

    public function placeMultiBet($bets, $amount_type)
    {
        $bet          = $this->saveMutibet(Status::MULTI_BET, request()->stake_amount, 0, $amount_type);
        $returnAmount = $bet->stake_amount;
        foreach ($bets as $betItem) {
            $returnAmount *= $betItem['odds_point'];
            $this->saveMultiBetDetail($bet->id, $betItem);
        }

        $bet->return_amount = $returnAmount;
        $bet->save();
    }

    private function saveMutibet($type, $stake_amount, $return_amount = 0, $amount_type)
    {
        $bet = new Bet();
        $bet->user_id = auth()->id();
        $bet->bet_number    = getNumberTrx(10);
        $bet->stake_amount = $stake_amount;
        $bet->amount_type = $amount_type;
        $bet->return_amount = $return_amount;
        $bet->status = Status::BET_PENDING;
        $bet->api_source_type = 'api_source_type';
        $bet->type = $type;
        $bet->save();

        return $bet;
    }

    private function saveMultiBetDetail($betId, $betItem)
    {
        $category = $this->categoryExistOrCreate($betItem['category']);
        $team_one = $this->teamExistOrCreate($category->id, $betItem['team1']);
        $team_two = $this->teamExistOrCreate($category->id, $betItem['team2']);
        $league = $this->leagueExistOrCreate($category->id, $betItem['leauge']);
        $game = $this->gameExistOrCreate($betItem, $betItem['matchId'], $team_one, $team_two, $league->id);
        $question = $this->questionExistOrCreate($game->id, $betItem['market_name'].$betItem['bookmarkId']);
        $option = $this->optionExistOrCreate($question->id, $betItem['odd_details']);
        $odd = Option::where('question_id', $question->id)->where('name', $betItem['odd_name'])->where('odds', $betItem['odds_point'])->first();

        $betDetail              = new BetDetail();
        $betDetail->bet_id      = $betId;
        $betDetail->question_id = $question->id;
        $betDetail->option_id   = $odd->id;
        $betDetail->odds        = $betItem['odds_point'];
        $betDetail->status      = Status::BET_PENDING;
        $betDetail->details    = json_encode($betItem);
        $betDetail->save();
    }


    private function saveBetData($type, $betItem, $stake_amount, $return_amount = 0, $amount_type = 1)
    {
        // Log::info("bet item");
        // Log::info($betItem);

        $category = $this->categoryExistOrCreate($betItem['category']);
        $team_one = $this->teamExistOrCreate($category->id, $betItem['team1']);
        $team_two = $this->teamExistOrCreate($category->id, $betItem['team2']);
        $league = $this->leagueExistOrCreate($category->id, $betItem['leauge']);
        $game = $this->gameExistOrCreate($betItem, $betItem['matchId'], $team_one, $team_two, $league->id);
        $question = $this->questionExistOrCreate($game->id, $betItem['market_name'].$betItem['item_id']??"");
        $option = $this->optionExistOrCreate($question->id, $betItem['odd_details']);
        $odd = Option::where('question_id', $question->id)->where('name', $betItem['odd_name'])->where('odds', $betItem['odds_point'])->first();

        // Log::info("question");
        // Log::info($question);
        
        // Log::info("option");
        // Log::info($option);
        
        // Log::info("odd");
        // Log::info($odd);

        $bet = new Bet();
        $bet->user_id = auth()->id();
        $bet->bet_number    = getNumberTrx(10);
        $bet->stake_amount = $stake_amount;
        $bet->return_amount = $return_amount;
        $bet->status = Status::BET_PENDING;
        $bet->category = $betItem['category'];
        $bet->leauge = $betItem['leauge'];
        $bet->oddId = $betItem['oddId'];
        $bet->bookmarkId = $betItem['bookmarkId'];
        $bet->matchId = $betItem['matchId'];
        $bet->odd_details = json_encode($betItem['odd_details']);
        $bet->odds = $betItem['odds'];
        $bet->odds_point = $betItem['odds_point'];
        $bet->checker = $betItem['checker'];
        $bet->api_source_type = $betItem['api_source_type'];
        $bet->is_live = $betItem['is_live'];
        $bet->team1 = $betItem['team1'];
        $bet->team2 = $betItem['team2'];
        $bet->market_name = $betItem['market_name'];
        $bet->odds_name = $betItem['odd_name'];
        $bet->type = $type;

        $bet->save();
        
//         Log::info("bet");
//         Log::info($bet);
        
//         Log::info("bet id", ['bet_id' => $bet->id]);
// Log::info("question id", ['question_id' => optional($question)->id]);
// Log::info("odd id", ['odd_id' => optional($odd)->id]);

       

        $this->saveSingleBetDetail($bet->id, $question->id, $odd->id, $betItem);

        return $bet;
    }
    // Single Bet details
    private function saveSingleBetDetail($betId, $question, $odd, $betItem)
    {
        $betDetail              = new BetDetail();
        $betDetail->bet_id      = $betId;
        $betDetail->question_id = $question;
        $betDetail->option_id   = $odd;
        $betDetail->odds        = $betItem['odds_point'];
        $betDetail->status      = Status::BET_PENDING;
        $betDetail->details    = json_encode($betItem);
        $betDetail->save();
    }


    // Options create
//    public function optionExistOrCreate($questionId, $odds)
//    {
//        if (count($odds) > 0) {
//            foreach ($odds as $odd) {
//                if(isset($odd['odds']) && count($odd['odds']) > 0){
//                    foreach ($odd['odds'] as $key => $value) {
//                        $existingOdd = Option::where('question_id',$questionId)->where('name', $value['name'])->where('odds', $value['value'])->first();
//                        if(!$existingOdd){
//                            $option =  new Option();
//                            $option->question_id = $questionId;
//                            $option->source = 'goalserve';
//                            $option->name = $value['name'];
//                            $option->odds = $value['value'];
//                            $option->handcap_value = $value['handicap']??0;
//                            $option->save();
//                        }
//                    }
//                }else{
//                    $existingOdd = Option::where('question_id',$questionId)->where('name', $odd['name']??$odd['@name'])->where('odds', $odd['value']??$odd['@value']??$odd['value_eu'])->first();
//                    if(!$existingOdd){
//                        $option =  new Option();
//                        $option->question_id = $questionId;
//                        $option->source = 'goalserve';
//                        $option->name = $odd['name'];
//                        $option->odds = $odd['value']??$odd['value_eu'];
//                        $option->handcap_value = $value['handicap']??0;
//                        $option->save();
//                    }
//                }
//
//            }
//        }
//    }


    public function optionExistOrCreate($questionId, $odds)
    {
        Log::info("inter option");
        Log::info($questionId);
        if (is_array($odds) && count($odds) > 0) {
            foreach ($odds as $odd) {
                if (isset($odd['odds']) && is_array($odd['odds']) && count($odd['odds']) > 0) {

                    foreach ($odd['odds'] as $key => $value) {
                        if (is_array($value) && isset($value['name'], $value['value'])) {
                            $existingOdd = Option::where('question_id', $questionId)
                                ->where('name', $value['name'])
                                ->where('odds', $value['value']??$value['odds'])
                                ->first();

                            if (!$existingOdd) {
                                $option = new Option();
                                $option->question_id = $questionId;
                                $option->source = 'goalserve';
                                $option->name = $value['name'];
                                $option->odds = $value['value']??$value['odds'];
                                $option->handcap_value = $value['handicap'] ?? 0;
                                $option->save();
                            }
                        }
                    }
                } else {
                    // Handle case when 'odds' is not set or is not an array
                    $name = $odd['header']??$odd['name'] ?? $odd['@name'] ?? null;
                    $oddsValue = $odd['value'] ?? $odd['@value'] ?? $odd['value_eu']??$odd['odds'] ?? null;

                    if ($name && $oddsValue) {
                        $existingOdd = Option::where('question_id', $questionId)
                            ->where('name', $name)
                            ->where('odds', $oddsValue)
                            ->first();

                        if (!$existingOdd) {
                            $option = new Option();
                            $option->question_id = $questionId;
                            $option->source = 'goalserve';
                            $option->name=$name;
                            // $option->name = ($name == 1) ? "Home" : (($name == 2) ? "Away" : ((strtolower($name) == "tie") ? "Draw" : $name));
                            $option->odds = $oddsValue;
                            $option->handcap_value = $odd['handicap'] ?? 0;
                            $option->save();
                        }
                    }
                }
            }
        }
    }
    
    
// public function optionExistOrCreate($questionId, $odds)
// {
//     Log::info("Entering optionExistOrCreate method");
//     Log::info("Question ID: " . $questionId);

//     try {
//         if (is_array($odds) && count($odds) > 0) {
//             Log::info("Odds array is valid and contains " . count($odds) . " items");

//             foreach ($odds as $oddIndex => $odd) {
//                 Log::info("Processing odd at index $oddIndex: " . json_encode($odd));

//                 try {
//                     if (isset($odd['odds']) && is_array($odd['odds']) && count($odd['odds']) > 0) {
//                         Log::info("'odds' key is valid and contains " . count($odd['odds']) . " items");

//                         foreach ($odd['odds'] as $key => $value) {
//                             Log::info("Processing sub-odd with key $key: " . json_encode($value));

//                             if (is_array($value) && isset($value['name'], $value['value'])) {
//                                 Log::info("Valid sub-odd with name: " . $value['name'] . ", value: " . $value['value']);

//                                 $existingOdd = Option::where('question_id', $questionId)
//                                     ->where('name', $value['name'])
//                                     ->where('odds', $value['value'] ?? $value['odds'])
//                                     ->first();

//                                 if ($existingOdd) {
//                                     Log::info("Odd already exists in the database: " . json_encode($existingOdd));
//                                 } else {
//                                     Log::info("Odd does not exist. Creating a new Option");

//                                     $option = new Option();
//                                     $option->question_id = $questionId;
//                                     $option->source = 'goalserve';
//                                     $option->name = $value['name'];
//                                     $option->odds = $value['value'] ?? $value['odds'];
//                                     $option->handcap_value = $value['handicap'] ?? 0;
//                                     $option->save();

//                                     Log::info("Option created successfully: " . json_encode($option));
//                                 }
//                             } else {
//                                 Log::warning("Invalid sub-odd structure: " . json_encode($value));
//                             }
//                         }
//                     } else {
//                         Log::info("'odds' key is not valid or empty. Processing odd as a single item");

//                         $name = $odd['name'] ?? $odd['@name']??$odd['name2'] ?? null;
//                         $oddsValue = $odd['value'] ?? $odd['@value'] ?? $odd['value_eu'] ??$odd['odds']?? null;

//                         if ($name && $oddsValue) {
//                             Log::info("Valid single odd with name: $name, value: $oddsValue");

//                             $existingOdd = Option::where('question_id', $questionId)
//                                 ->where('name', $name)
//                                 ->where('odds', $oddsValue)
//                                 ->first();

//                             if ($existingOdd) {
//                                 Log::info("Single odd already exists in the database: " . json_encode($existingOdd));
//                             } else {
//                                 Log::info("Single odd does not exist. Creating a new Option");

//                                 $option = new Option();
//                                 $option->question_id = $questionId;
//                                 $option->source = 'goalserve';
//                                 $option->name = $name;
//                                 $option->odds = $oddsValue;
//                                 $option->handcap_value = $odd['handicap'] ?? 0;
//                                 $option->save();

//                                 Log::info("Option created successfully: " . json_encode($option));
//                             }
//                         } else {
//                             Log::info("Invalid single odd structure: " . json_encode($odd));
//                         }
//                     }
//                 } catch (\Exception $e) {
//                     Log::info("Error processing odd at index $oddIndex: " . json_encode($odd));
//                     Log::info("Exception message: " . $e->getMessage());
//                 }
//             }
//         } else {
//             Log::info("Odds array is invalid or empty");
//         }
//     } catch (\Exception $e) {
//         Log::info("Critical error in optionExistOrCreate method");
//         Log::info("Exception message: " . $e->getMessage());
//         Log::info("Stack trace: " . $e->getTraceAsString());
//     }

//     Log::info("Exiting optionExistOrCreate method");
// }





    // Question Create 
    public function questionExistOrCreate($gameId, $market)
    {
        
        $existingQuestion = Question::where('game_id', $gameId)->where('title', $market)->first();
        if($existingQuestion){
            return $existingQuestion;
        }else{
            $slug  = $this->slugify($market);
            $question = new Question();
            $question->game_id = $gameId;
            $question->source = 'goalserve';
            $question->slug = $slug;
            $question->title = $market;
            $question->save();
            return $question;
        }
        
        
    }
    // Game Exist or create and return game
    public function gameExistOrCreate($betItem, $matchId, $team_one, $team_two, $leagueId)
    {
        $slug = $this->slugify($team_one->name . ' ' . $team_two->name);
        $slugAdditional = $betItem['is_live'] == 'Live' ? '_live' : '_upcoming';
        $now = Carbon::now();
        $tomorrow = $now->addDay();
        $existingGame = Game::where('slug', $slug .'_'. $matchId.'_'.$now->format('Y_m_d').$slugAdditional)->first();
        // $existingGame = Game::where('game_id', $matchId)->first();


        if ($existingGame) {
            return $existingGame;
        } else {
            $game = new Game();
            $game->game_id = $matchId;
            $game->category = $betItem['category'];
            $game->team_one_id = $team_one->id;
            $game->team_two_id = $team_two->id;
            $game->league_id = $leagueId;
            $game->game_type = $betItem['is_live'] == 'Live' ? 'LIVE' : 'UPCOMING';
            $game->slug =  $slug .'_'. $matchId.'_'.$now->format('Y_m_d').$slugAdditional;
            $game->start_time = $now->format('Y-m-d H:i:s');
            $game->bet_start_time = $now->format('Y-m-d H:i:s');
            $game->bet_end_time = $tomorrow->format('Y-m-d H:i:s');
            $game->save();
            return $game;
        }
    }

    // Team Exist or create and return team
    public function teamExistOrCreate($categoryId, $name)
    {
        $slug = $this->slugify($name);
        $existingTeam = Team::where('slug', $slug)->first();
        if ($existingTeam) {
            return $existingTeam;
        } else {
            $team = new Team();
            $team->category_id = $categoryId;
            $team->name = $name;
            $team->slug = $slug;
            $team->short_name = $this->shortName($name);
            $team->save();
            return $team;
        }
    }

    // League Exist or create and return League
    public function leagueExistOrCreate($categoryId,  $name)
    {
        $slug = $this->slugify($name);
        $existingLeague = League::where('slug', $slug)->first();
        if ($existingLeague) {
            return $existingLeague;
        } else {
            $league = new League();
            $league->category_id = $categoryId;
            $league->name = $name;
            $league->slug = $slug;
            $league->short_name = $this->shortName($name);
            $league->save();
            return $league;
        }
    }
    public function slugify($string)
    {
        $string = trim($string);
        $string = strtolower($string);
        $string = str_replace(' ', '_', $string);
        $string = preg_replace('/[^a-z0-9_]+/', '', $string);

        return $string;
    }
    function shortName(string $text): string
    {
        if (empty($text)) {
            return "";
        }
        $words = array_map('ucfirst', explode(' ', $text));
        return implode('', $words);
    }
    // Category Exist or create and return category
    public function categoryExistOrCreate(string $name)
    {
        $slug = $this->slugify($name);
        $existingCategory  =  Category::where('slug', $slug)->first();
        if ($existingCategory) {
            return $existingCategory;
        } else {
            $category = new Category();
            $category->name = $name;
            $category->slug = $slug;
            $category->save();
            return $category;
        }
    }

    public function betHistory(Request $request)
    {
        $user = auth()->user();
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $bets = Bet::where('user_id', $user->id)->with('bets')->orderBy('id', 'desc')->when($request->filled('search'), function ($query) use ($request) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('bet_number', 'LIKE', '%' . $search . '%');
            });
        });
        if (is_numeric($page)) {
            $bets = $bets->paginate($perPage);
        } else {
            $bets = $bets->get();
        }

        return response()->json(
            responseBuilder(
                Response::HTTP_OK,
                "Success",
                [
                    'data'          => BetCollection::collection($bets),
                    "user"          => $user->id,
                    'per_page'      => $bets->perPage() ?? 10,
                    'current_page'  => $bets->currentPage() ?? 1,
                    'total'         => $bets->total() ?? count($bets),
                    'last_page'     => $bets->lastPage() ?? count($bets),

                ]
            ),
            Response::HTTP_OK
        );
        
     
    }
}
