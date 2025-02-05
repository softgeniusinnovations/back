<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Option;
use App\Models\Question;
use App\Models\Game;
use App\Models\AffiliatePromos;
use App\Models\Promotion;
use App\Models\BetDetail;
use App\Models\GoalCategory;
use App\Models\UserBonusList;
use Illuminate\Http\Request;
use DB;
use GuzzleHttp\Client;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
class DeclareOutcomeController extends Controller {
    
    public $apiKey = "89b86665dc8348f5605008dc3da97a57";
    
    public function pendingGame() {
        $pageTitle = 'Pending Games';
        $games = Game::where('source', 'goalserve')->withCount(['questions' => function($q){
            $q->where('result', 0);
        }])->with('teamOne', 'teamTwo', 'league')
        ->where('game_end', 0)
        ->where('game_type', 'LIVE')
        ->orderBy('id', 'desc')
        ->searchable(['slug'])
        ->paginate(getPaginate());
        $type= "Pending";
        return view('admin.declare_outcomes.game_list', compact('pageTitle', 'games', 'type'));
    }
    public function pendingUpcomingGame() {
        $pageTitle = 'Upcoming Pending Games';
        $games = Game::where('source', 'goalserve')->withCount(['questions' => function($q){
            $q->where('result', 0);
        }])->with('teamOne', 'teamTwo', 'league')
        ->where('game_end', 0)
        ->where('game_type', 'UPCOMING')
        ->orderBy('id', 'desc')
        ->searchable(['slug'])
        ->paginate(getPaginate());
//        dd($games);
//        return $games;
        $type= "Upcoming";
        return view('admin.declare_outcomes.game_list', compact('pageTitle', 'games', 'type'));
    }
    
    public function gameEnd($id) {
        $game = Game::find($id);
        $game->game_end = 1;
        $game->save();
        $notify[] = ['success', 'Game End'];
        return back()->withNotify($notify);
    }

    public function getMatchid($gameid){
        $matchData = Game::where('games.id', $gameid)
            ->join('questions', 'questions.game_id', '=', 'games.id')
            ->join('bet_details', 'bet_details.question_id', '=', 'questions.id')
            ->join('bets', 'bets.id', '=', 'bet_details.bet_id')
            ->distinct()
            ->select('bets.matchid', 'bets.created_at')
            ->select('bets.matchid', 'bets.created_at')
            ->first();
            $matchId = $matchData->matchid;
            $createdAt = Carbon::parse($matchData->created_at)->format('Ym');;
        $pageTitle = 'Results ';
        if($matchId){
            $responseData = $this->getMatchResult($matchId, $createdAt);
            $results = is_array($responseData) && isset($responseData['success']) ?  $responseData['results'] : [];
            if($responseData){
                return view('admin.declare_outcomes.result', compact('results','pageTitle'));
            }
        }

        $notify[] = ['error', 'Result not found'];
        return back()->withNotify($notify);


//        $question_id = BetDetail::join('questions', 'bet_details.question_id', '=', 'questions.id')
//            ->join('games', 'questions.game_id', '=', 'games.id')
//            ->where('games.id', 7221)
//            ->whereIn('bet_details.bet_id', function($query) {
//                $query->select('id')
//                    ->from('bets')
//                    ;
//            })
//            ->distinct()
//            ->pluck('question_id')
//            ->all();
//        $matchId = Game::where('games.id', 7221)
//            ->join('questions', 'questions.game_id', '=', 'games.id')
//            ->join('bet_details', 'bet_details.question_id', '=', 'questions.id')
//            ->join('bets', 'bets.id', '=', 'bet_details.bet_id')
//
//            ->whereIn('questions.id', $question_id)
//            ->distinct()
//            ->value('bets.matchid');
//        dd($matchId);

    }
    
    
    public function pendingOutcomes($id) {
        $game = Game::find($id);
        $pageTitle = 'Pending Outcomes for '. $game->slug;
        $questions = Question::where('game_id', $id)->resultUndeclared()
            ->with([
                'options' => function ($bets) {
                    $bets->withCount('bets');
                },
                'game', 'game.teamOne', 'game.teamTwo','betDetails',
            ])
            ->withCount('betDetails')
            ->withCount([
                'betDetails as bet_details_status_pending_count' => function ($query) {
                    $query->where('status', 2);
                }
            ])
            // ->whereHas('betDetails', function ($query) {
            //     $query->where('bet_details.status', 2);
            // })
            ->searchable(['name'])
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());
//        return $questions;
        return view('admin.declare_outcomes.index', compact('pageTitle', 'questions'));
    }

    public function declaredOutcomes() {
        $pageTitle = 'Declared Outcomes';
        $questions = Question::resultDeclared()
            ->with([
                'options' => function ($bets) {
                    $bets->withCount('bets');
                },
                'game', 'game.teamOne', 'game.teamTwo', 'winOption:id,question_id,name',
            ])
            ->withCount('betDetails')
            ->whereHas('betDetails', function ($query) {
                $query->where('bet_details.status', '!=', 2);
            })
            ->searchable(['slug', 'title'])
            ->orderBy('id', 'desc')
            ->paginate(getPaginate());


        return view('admin.declare_outcomes.index', compact('pageTitle', 'questions'));
    }

    public function refund($id) {
        $question = Question::active()->resultUndeclared()
            ->with(['betDetails' => function ($query) {
                $query->pending()->with('bet.user');
            }])->find($id);

        if (!$question) {
            $notify[] = ['error', 'This selection is not refundable'];
            return back()->withNotify($notify);
        }

        $question->result = Status::DECLARED;
        $question->refund = Status::REFUND;
        $question->save();

        $betDetails = $question->betDetails;

        foreach ($betDetails as $detail) {
            $detail->status = Status::BET_REFUNDED;
            $detail->save();

            $bet = $detail->bet;
            if ($bet->type == Status::SINGLE_BET) {
                $bet->status          = Status::BET_REFUNDED;
                $bet->amount_returned = Status::YES;
                $bet->save();
            }
        }

        $notify[] = ['success', 'All bets for question : ' . $question->title . ' marked as refunded'];
        return back()->withNotify($notify);
    }
    
    
    public function makeAction(Request $request) // actionType = win, loss, refund
    {
        $id = $request->id;
        $type = $request->type;
        $comment = $request->comments;
        
        
        if($type == 'win')
        {
            $this->actionForWinner($id, $comment);
        }
        if($type == 'half_win')
        {
            $this->actionForHalfWinner($id, $comment);
        }

        
        else if($type == "loss")
        {
            $this->actionForLosser($id, $comment);
        }
        else if($type == "half_loss")
        {
            $this->actionForHalfLosser($id, $comment);
        }
        
        else if($type == "refund")
        {
            $this->actionForRefund($id, $comment);
        }
        
        else
        {
            $notify[] = ['errpr', 'Action type missmatched within [win, loss, refund]'];
            return back()->withNotify($notify);
        }
        
    }
    
    public function questionDeclared($id) // I want declare for result 
    {
        
        $question = Question::find($id);
        if ($question && $question->status == Status::UNDECLARED) // UNDECLARED = 0
        {
            $notify[] = ['error', 'Result already declared'];
            return back()->withNotify($notify);
        }
        $question->result        = Status::DECLARED;
        $question->save();
        
        $notify[] = ['success', 'Result  declared'];
        return back()->withNotify($notify);
    }
    
    public function actionForRefund($id, $comment)
    {
        $option = Option::availableForRefund()->with('question')->find($id);
        if (!$option) {
            $notify[] = ['error', 'Invalid option selected'];
            return back()->withNotify($notify);
        }
        $question = $option->question;

        if ($question && $question->status == Status::UNDECLARED) // UNDECLARED = 0
        {
            $notify[] = ['error', 'Result already declared'];
            return back()->withNotify($notify);
        }
        
        $redundBetDetails  = $question->betDetails()->where('option_id', '=', $option->id)->where('question_id', $question->id)->with('bet')->get();
        foreach ($redundBetDetails as $detail) {
            $detail->status = Status::BET_REFUNDED;
            $detail->save();

            $bet = $detail->bet;
            if ($bet->type == Status::SINGLE_BET) {
                $bet->status          = Status::BET_REFUNDED;
                $bet->amount_returned = Status::YES;
                $bet->comments          = $comment;
                $bet->save();
            }
        }
        $option->refund = 1;
        $option->button_action = Status::BUTTON_ACTION_REFUND;
        $option->save();
        $notify[] = ['success', "Successfuly refund action for ". $option->name];
        return back()->withNotify($notify);
    }
    
    public function actionForLosser($id, $comment)
    {
         $option = Option::availableForLosser()->with('question')->find($id);
        if (!$option) {
            $notify[] = ['error', 'Invalid option selected'];
            return back()->withNotify($notify);
        }
        $question = $option->question;

        if ($question && $question->status == Status::UNDECLARED) // UNDECLARED = 0
        {
            $notify[] = ['error', 'Result already declared'];
            return back()->withNotify($notify);
        }
        
        $loserBetDetails  = $question->betDetails()->where('option_id', '=', $option->id)->where('question_id', $question->id)->with('bet')->get();
        Log::info("outside loop loserBetDetails");
        Log::info($loserBetDetails);
        foreach ($loserBetDetails as $loserBetDetail) {
            $loserBetDetail->status = Status::BET_LOSE;
            $loserBetDetail->save();
            Log::info("inside loop loseBet loserBetDetail ");
            Log::info($loserBetDetail);

            $loseBet                  = $loserBetDetail->bet;
            $exists = BetDetail::where('bet_id', $loserBetDetail->bet_id)
                ->where('status', 2)
                ->exists();
            if($exists){
                $loseBet->status          = Status::BET_PENDING;
//                $loseBet->amount_returned = Status::YES;
                $loseBet->result_time     = now();
                $loseBet->comments     = $comment;
                $loseBet->save();
            }
            else{

                $loseBet->status          = Status::BET_LOSE;
                $loseBet->amount_returned = Status::YES;
                $loseBet->result_time     = now();
                $loseBet->comments     = $comment;
                $loseBet->save();
            }


//            $loseBet->bets()->update(['status' => Status::BET_LOSE]);
//            Log::info("inside loop loseBet after save");
//            Log::info($loseBet);


        }
        $option->losser = 1;
        $option->button_action = Status::BUTTON_ACTION_LOSS;
        $option->save();
        $notify[] = ['success', "Successfuly loss action for ". $option->name];
        return back()->withNotify($notify);
    }
    public function actionForHalfLosser($id, $comment)
    {
         $option = Option::availableForLosser()->with('question')->find($id);
        if (!$option) {
            $notify[] = ['error', 'Invalid option selected'];
            return back()->withNotify($notify);
        }
        $question = $option->question;

        if ($question && $question->status == Status::UNDECLARED) // UNDECLARED = 0
        {
            $notify[] = ['error', 'Result already declared'];
            return back()->withNotify($notify);
        }

        $loserBetDetails  = $question->betDetails()->where('option_id', '=', $option->id)->where('question_id', $question->id)->with('bet')->get();
        Log::info("outside loop loserBetDetails");
        Log::info($loserBetDetails);
        foreach ($loserBetDetails as $loserBetDetail) {
            $loserBetDetail->status = Status::BET_LOSE;
            $loserBetDetail->save();
            Log::info("inside loop loseBet loserBetDetail ");
            Log::info($loserBetDetail);

            $loseBet                  = $loserBetDetail->bet;
            $exists = BetDetail::where('bet_id', $loserBetDetail->bet_id)
                ->where('status', 2)
                ->exists();
            if($exists){
                $loseBet->status          = Status::BET_PENDING;
//                $loseBet->amount_returned = Status::YES;
                $loseBet->result_time     = now();
                $loseBet->comments     = $comment;
                $loseBet->save();
            }
            else{

                $loseBet->status          = Status::BET_LOSE;
                $loseBet->amount_returned = Status::YES;
                $loseBet->is_half = 1;
//                $loseBet->return_amount = $loseBet->stake_amount/2;
                $loseBet->result_time     = now();
                $loseBet->comments     = $comment;
                $loseBet->save();
            }


//            $loseBet->bets()->update(['status' => Status::BET_LOSE]);
//            Log::info("inside loop loseBet after save");
//            Log::info($loseBet);


        }
        $option->losser = 1;
        $option->button_action = Status::BUTTON_ACTION_HALF_LOSS;
        $option->save();
        $notify[] = ['success', "Successfuly loss action for ". $option->name];
        return back()->withNotify($notify);
    }

    public function actionForWinner($id, $comment = "Game Win"){
        $option = Option::availableForWinner()->with('question')->find($id);
        if (!$option) {
            $notify[] = ['error', 'Invalid option selected'];
            return back()->withNotify($notify);
        }
        $question = $option->question;
        
        $checkMultiWinner = $question->loadCount(['options' => function($winner){
            $winner->where('winner', 1);
        }]);
        
        // if($checkMultiWinner->options_count != 0){
        //     $notify[] = ['error', 'Winner already declared'];
        //     return back()->withNotify($notify);
        // }

        // if ($question && $question->status == Status::UNDECLARED) // UNDECLARED = 0
        // {
        //     $notify[] = ['error', 'Result already declared'];
        //     return back()->withNotify($notify);
        // }
        
        $winnerBetDetails = $question->betDetails()->where('option_id', $option->id)->where('question_id', $question->id)->with('bet')->get();
        
        $question->win_option_id = $option->id;
        $question->save();
        
        $option->winner = Status::WINNER;
        $option->button_action = Status::BUTTON_ACTION_WIN;
        $option->save();


        foreach ($winnerBetDetails as $betDetails) {
            $betDetails->status = Status::BET_WIN;
            $betDetails->save();

            $winBet = $betDetails->bet;

            $wonBets = collect();
            $refundMultiBetCount=0;
            $totalMultiBet=0;


            if ($winBet->type == Status::MULTI_BET) {

                Log::info("inside multi");
                Log::info($winBet);


                $totalMultiBet       = $winBet->bets()->count();
                $refundMultiBetCount = $winBet->bets()->where('status', Status::BET_REFUNDED)->count();
                $wonBets             = $winBet->bets()->where('status', Status::BET_WIN)->get();
//                dd($wonBets);

//                $winAmount=0.0;
//                if($winBet->amount_type==2){
//                    Log::info($winBet);
//                    Log::info("winbet");
//
//                    $user_bonus=UserBonusList::where('user_id',$winBet->user_id)->first();
//                    Log::info("amount type bonus".$user_bonus);
//                    Log::info("winamount1".$winAmount);
//                    if($user_bonus&&$user_bonus->rollover>2){
////                            $winAmount = User::where('id', $winBet->user_id)->first()->bonus_account;
//                        $winAmount = $user_bonus->initial_amount;
//                        Log::info("winamount inside rollover".$winAmount);
//                    }
//                    else{
//                        $winAmount               = $this->winAmount($winBet, $wonBets);
//                        $user = User::where('id', $winBet->user_id)->first();
//                        $user->bonus_account=$winAmount;
//                        $user->save();
//                        Log::info("winamount2".$winAmount);
//                    }
//                }
//                else{
//                    $winAmount               = $this->winAmount($winBet, $wonBets);
                }



                if ($totalMultiBet == $refundMultiBetCount + $wonBets->count()) {
                    Log::info("inside refundMultiBetCount ");
//                    $winAmount=0.0;
//                    if($winBet->amount_type==2){
//                        $user_bonus=UserBonusList::where('user_id',$winBet->user_id)->first();
//                        Log::info("winamount1".$winAmount);
//                        if($user_bonus&&$user_bonus->rollover>2){
////                            $winAmount = User::where('id', $winBet->user_id)->first()->bonus_account;
//                            $winAmount = $user_bonus->initial_amount;
//                            Log::info("winamount inside rollover".$winAmount);
//                        }
//                        else{
//                            $winAmount               = $this->winAmount($winBet, $wonBets);
//                            $user = User::where('id', $winBet->user_id)->first();
//                            $user->bonus_account=$winAmount;
//                            $user->save();
//                            Log::info("winamount2".$winAmount);
//                        }
//                    }
//                    else{
//                        $winAmount               = $this->winAmount($winBet, $wonBets);
//                    }

                    $winBet->return_amount   = $this->winAmount($winBet, $wonBets);
                    $lossexists = BetDetail::where('bet_id', $betDetails->bet_id)
                        ->where('status', 3)
                        ->exists();
                    if($lossexists){
                        $winBet->status          = Status::BET_LOSE;
                        $winBet->result_time     = now();
                        $winBet->comments     = $comment;
                        $winBet->save();
                    }
                    else{
                        $winBet->status          = Status::BET_WIN;
                        $winBet->amount_returned = Status::YES;
                        $winBet->result_time     = now();
                        $winBet->comments     = $comment;
                        $winBet->save();
                    }


//                    $affiliate_loss=$winAmount * ($promo_percentage_x / 100);
//                    if ($affiliate_promo) {
//                        $affiliate_loss = $winAmount * ($promo_percentage_x / 100);
//                        if ($affiliate_loss) {
//                            $user = User::where('id', $affiliate_user)->get();
//                            if ($user) {
//                                $user->affiliate_balance = $user->affiliate_balance - $affiliate_loss;
//                                $user->save();
//                            }
//                        }
//                    }


            }
                else {
                    Log::info("inside else");
                    $exists = BetDetail::where('bet_id', $betDetails->bet_id)
                        ->where('status', 2)
                        ->exists();
                    Log::info("inside else exists ");
                    Log::info($exists);

                    $lossexists = BetDetail::where('bet_id', $betDetails->bet_id)
                        ->where('status', 3)
                        ->exists();
                    Log::info("inside else lossexists ");
                    Log::info($lossexists);
                    if ($lossexists && $exists) {
                        // Case 1: Both "pending" and "lose" exist
                        $winBet->status = Status::BET_PENDING;
                        $winBet->result_time = now();
                        $winBet->comments = $comment;
                        $winBet->save();
                        Log::info("Both lose and pending exist - set winBet status to pending");
                    } elseif ($lossexists) {
                        // Case 2: Only "lose" exists
                        $winBet->status = Status::BET_LOSE;
                        $winBet->result_time = now();
                        $winBet->comments = $comment;
                        $winBet->save();
                        Log::info("Only lose exists - set winBet status to lose");
                    } elseif ($exists) {
                        // Case 3: Only "pending" exists
                        $winBet->status = Status::BET_PENDING;
                        $winBet->save();
                        Log::info("Only pending exists - set winBet status to pending");
                    } else {
                        // Case 4: Neither "pending" nor "lose" exists
                        $winBet->status = Status::BET_WIN;
                        $winBet->amount_returned = Status::YES;
                        $winBet->result_time = now();
                        $winBet->comments = $comment;
                        $winBet->save();
                        Log::info("Neither pending nor lose exists - set winBet status to win");
                    }




//                $affiliate_loss=$winAmount * ($promo_percentage_x / 100);
//                $user=User::where('id',$affiliate_user)->first();
//                $user->affiliate_balance=$user->affiliate_balance - $affiliate_loss;
//                $user->save();
            }
        }

        $notify[] = ['success', "Successfully win action for ". $option->name];
        return back()->withNotify($notify);
    }

    public function actionForHalfWinner($id, $comment = "Game Half Win"){


        $option = Option::availableForWinner()->with('question')->find($id);

        if (!$option) {
            $notify[] = ['error', 'Invalid option selected'];
            return back()->withNotify($notify);
        }
        $question = $option->question;

        $checkMultiWinner = $question->loadCount(['options' => function($winner){
            $winner->where('winner', 1);
        }]);

        // if($checkMultiWinner->options_count != 0){
        //     $notify[] = ['error', 'Winner already declared'];
        //     return back()->withNotify($notify);
        // }

        // if ($question && $question->status == Status::UNDECLARED) // UNDECLARED = 0
        // {
        //     $notify[] = ['error', 'Result already declared'];
        //     return back()->withNotify($notify);
        // }

        $winnerBetDetails = $question->betDetails()->where('option_id', $option->id)->where('question_id', $question->id)->with('bet')->get();

        $question->win_option_id = $option->id;
        $question->save();

        $option->winner = Status::WINNER;
        $option->button_action = Status::BUTTON_ACTION_HALF_WIN;
        $option->save();





        foreach ($winnerBetDetails as $betDetails) {
            $betDetails->status = Status::BET_HALF_WIN;
            $betDetails->save();



            $winBet = $betDetails->bet;
//            $affiliate_promo=AffiliatePromos::where('better_user_id',$winBet->user_id)->first();
//            $promo_percentage_x = 0;
//            $company_expenses_y = 0;
////            dd($affiliate_promo);
//            if($affiliate_promo){
//                $affiliate_user=$affiliate_promo->affliate_user_id;
//                $promo=Promotion::where('id',$affiliate_promo->promo_id)->first();
//
//                $promo_percentage_x=$promo->promo_percentage;
////                dd($promo_percentage_x);
//                $company_expenses_y=$promo->company_expenses;
//            }
            $wonBets = collect();
            $refundMultiBetCount=0;
            $totalMultiBet=0;

            if ($winBet->type == Status::MULTI_BET) {
//                Log::info($winBet);


                $totalMultiBet       = $winBet->bets()->count();
                $refundMultiBetCount = $winBet->bets()->where('status', Status::BET_REFUNDED)->count();
                $wonBets             = $winBet->bets()->where('status', Status::BET_WIN)->get();
//                dd($wonBets);

//                $winAmount=0.0;
//                if($winBet->amount_type==2){
//                    Log::info($winBet);
//                    Log::info("winbet");
//
//                    $user_bonus=UserBonusList::where('user_id',$winBet->user_id)->first();
//                    Log::info("amount type bonus".$user_bonus);
//                    Log::info("winamount1".$winAmount);
//                    if($user_bonus&&$user_bonus->rollover>2){
////                            $winAmount = User::where('id', $winBet->user_id)->first()->bonus_account;
//                        $winAmount = $user_bonus->initial_amount;
//                        Log::info("winamount inside rollover".$winAmount);
//                    }
//                    else{
//                        $winAmount               = $this->winAmount($winBet, $wonBets);
//                        $user = User::where('id', $winBet->user_id)->first();
//                        $user->bonus_account=$winAmount;
//                        $user->save();
//                        Log::info("winamount2".$winAmount);
//                    }
//                }
//                else{
//                    $winAmount               = $this->winAmount($winBet, $wonBets);
            }



            if ($totalMultiBet == $refundMultiBetCount + $wonBets->count()) {
//                Log::info("inside refundMultiBetCount ");
//                    $winAmount=0.0;
//                    if($winBet->amount_type==2){
//                        $user_bonus=UserBonusList::where('user_id',$winBet->user_id)->first();
//                        Log::info("winamount1".$winAmount);
//                        if($user_bonus&&$user_bonus->rollover>2){
////                            $winAmount = User::where('id', $winBet->user_id)->first()->bonus_account;
//                            $winAmount = $user_bonus->initial_amount;
//                            Log::info("winamount inside rollover".$winAmount);
//                        }
//                        else{
//                            $winAmount               = $this->winAmount($winBet, $wonBets);
//                            $user = User::where('id', $winBet->user_id)->first();
//                            $user->bonus_account=$winAmount;
//                            $user->save();
//                            Log::info("winamount2".$winAmount);
//                        }
//                    }
//                    else{
//                        $winAmount               = $this->winAmount($winBet, $wonBets);
//                    }

                $winBet->return_amount   = $this->halfWinAmount($winBet, $wonBets);
                $lossexists = BetDetail::where('bet_id', $betDetails->bet_id)
                    ->where('status', 3)
                    ->exists();
                if($lossexists){
                    $winBet->status          = Status::BET_LOSE;
                    $winBet->result_time     = now();
                    $winBet->comments     = $comment;
                    $winBet->save();
                }
                else{
                    $winBet->status          = Status::BET_HALF_WIN;
                    $winBet->amount_returned = Status::YES;
                    $winBet->result_time     = now();
                    $winBet->comments     = $comment;
                    $winBet->save();
                }


//                    $affiliate_loss=$winAmount * ($promo_percentage_x / 100);
//                    if ($affiliate_promo) {
//                        $affiliate_loss = $winAmount * ($promo_percentage_x / 100);
//                        if ($affiliate_loss) {
//                            $user = User::where('id', $affiliate_user)->get();
//                            if ($user) {
//                                $user->affiliate_balance = $user->affiliate_balance - $affiliate_loss;
//                                $user->save();
//                            }
//                        }
//                    }


            }
            else {
//                Log::info("inside else");
                $exists = BetDetail::where('bet_id', $betDetails->bet_id)
                    ->where('status', 2)
                    ->exists();
//                Log::info("inside else exists ");
//                Log::info($exists);

                $lossexists = BetDetail::where('bet_id', $betDetails->bet_id)
                    ->where('status', 3)
                    ->exists();
                Log::info("inside else lossexists ");
                Log::info($lossexists);
                if ($lossexists && $exists) {
                    // Case 1: Both "pending" and "lose" exist
                    $winBet->status = Status::BET_PENDING;
                    $winBet->result_time = now();
                    $winBet->comments = $comment;
                    $winBet->save();
                    Log::info("Both lose and pending exist - set winBet status to pending");
                } elseif ($lossexists) {
                    // Case 2: Only "lose" exists
                    $winBet->status = Status::BET_LOSE;
                    $winBet->result_time = now();
                    $winBet->comments = $comment;
                    $winBet->save();
                    Log::info("Only lose exists - set winBet status to lose");
                } elseif ($exists) {
                    // Case 3: Only "pending" exists
                    $winBet->status = Status::BET_PENDING;
                    $winBet->save();
                    Log::info("Only pending exists - set winBet status to pending");
                } else {
                    // Case 4: Neither "pending" nor "lose" exists
                    $winBet->status = Status::BET_HALF_WIN;
                    $winBet->amount_returned = Status::YES;
                    $winBet->result_time = now();
                    $winBet->comments = $comment;
                    $winBet->save();
//                    Log::info("Neither pending nor lose exists - set winBet status to win");
                }




//                $affiliate_loss=$winAmount * ($promo_percentage_x / 100);
//                $user=User::where('id',$affiliate_user)->first();
//                $user->affiliate_balance=$user->affiliate_balance - $affiliate_loss;
//                $user->save();
            }
        }

        $notify[] = ['success', "Successfully win action for ". $option->name];
        return back()->withNotify($notify);
    }
    
    
    

    public function winner($id) {

        $option = Option::availableForWinner()->with('question')->find($id);

        if (!$option) {
            $notify[] = ['error', 'Invalid option selected'];
            return back()->withNotify($notify);
        }

        $question = $option->question;

        if ($question && $question->status == Status::UNDECLARED) // UNDECLARED = 0
        {
            $notify[] = ['error', 'Result already declared'];
            return back()->withNotify($notify);
        }

        $winnerBetDetails = $question->betDetails()->where('option_id', $option->id)->where('question_id', $question->id)->with('bet')->get();
        $loserBetDetails  = $question->betDetails()->where('option_id', '!=', $option->id)->where('question_id', $question->id)->with('bet')->get();

        $question->result        = Status::DECLARED;
        $question->win_option_id = $option->id;
        $question->save();

        $option->winner = Status::WINNER;
        $option->save();

        foreach ($loserBetDetails as $loserBetDetail) {
            $loserBetDetail->status = Status::BET_LOSE;
            $loserBetDetail->save();

            $loseBet                  = $loserBetDetail->bet;
            $loseBet->status          = Status::BET_LOSE;
            $loseBet->amount_returned = Status::YES;
            $loseBet->result_time     = now();
            $loseBet->save();
            $loseBet->bets()->update(['status' => Status::BET_LOSE]);
        }

        foreach ($winnerBetDetails as $betDetails) {
            $betDetails->status = Status::BET_WIN;
            $betDetails->save();

            $winBet = $betDetails->bet;

            if ($winBet->type == Status::MULTI_BET) {

                $totalMultiBet       = $winBet->bets()->count();
                $refundMultiBetCount = $winBet->bets()->where('status', Status::BET_REFUNDED)->count();
                $wonBets             = $winBet->bets()->where('status', Status::BET_WIN)->get();

                if ($totalMultiBet == $refundMultiBetCount + $wonBets->count()) {
                    Log::info("inside loserbetdetails winner function");
                    $winAmount               = $this->winAmount($winBet, $wonBets);
                    Log::info("win amount".$winAmount);
                    $winBet->return_amount   = $winAmount;
                    $winBet->status          = Status::BET_WIN;
                    $winBet->amount_returned = Status::YES;
                    $winBet->result_time     = now();
                    $winBet->save();
                }
            } else {
                $winBet->status          = Status::BET_WIN;
                $winBet->amount_returned = Status::YES;
                $winBet->result_time     = now();
                $winBet->save();
            }
        }

        $notify[] = ['success', 'Outcome selected successfully'];
        return back()->withNotify($notify);
    }
    public function downloadPdf(Request $request)
    {
//        $results = json_decode($request->input('results'), true);
//        $pdf = PDF::loadView('admin.declare_outcomes.result_pdf', ['results' => $results]);
//        return $pdf->download('result_pdf.pdf');

        // Get the results from the request or generate them as needed
//        dd($request);
        $results = json_decode($request->input('results'));
        dd($results);

        $pdf = PDF::loadView('pdf.results', ['results' => $results]);

        // Force download the PDF with correct headers
        return response()->streamDownload(
            fn () => print($pdf->output()),
            'results.pdf',
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="results.pdf"'
            ]
        );

        // Generate the PDF
//        $pdf = PDF::loadView('admin.declare_outcomes.result_pdf', compact('results'));
//
//        return $pdf->download('results.pdf');
    }

    protected function winAmount($bet, $wonBets) {
        $totalOddsRate = 1;

        foreach ($wonBets as $betData) {
            $totalOddsRate *= $betData->odds;
        }

        $winAmount = getAmount($bet->stake_amount * $totalOddsRate, 8);
        return $winAmount;
    }
    protected function halfWinAmount($bet, $wonBets) {
//        Log::info("bet");
//        Log::info($bet);
//        Log::info("wonBets");
//        Log::info($wonBets);
        $totalOddsRate = 1;

        foreach ($wonBets as $betData) {
            $totalOddsRate *= $betData->odds;
        }

        $winAmount = getAmount($bet->return_amount / 2, 8);
        return $winAmount;
    }

    protected function getMatchResult($matchId, $createdAt){
        // dd($createdAt);
        $url = "http://inplay.goalserve.com/results/".$createdAt."/".$matchId.".json";
         $client = new Client();
        
        try {
            $response = $client->get($url);
            $responseBody = $response->getBody();


            return json_decode($responseBody, true);

        } catch (\Exception $e) {
            return false;

        }
    }
    
    protected function upcomingGameCategories(){
        $pageTitle = 'Categories';
        $categories = GoalCategory::where('is_in_play', 1)->paginate(getPaginate());
        return view('admin.declare_outcomes.categories', compact('pageTitle', 'categories'));
    }
    protected function upcomingGameCategoryWiseResult($name, $type)
    {
        $pageTitle = "Upcoming result for {$name} category with {$type} data";
        $url = "https://www.goalserve.com/getfeed/{$this->apiKey}/{$name}/{$type}?json=1";
        $client = new Client();
        try {
            $response = $client->get($url);
            $responseBody = $response->getBody();


             $xmlData = json_decode($responseBody, true);
             $xmlData= $this->cleanKeys($xmlData['scores']);
             return view('admin.declare_outcomes.upcoming_result', compact('pageTitle', 'xmlData','name'));

        } catch (\Exception $e) {
            return back();

        }
        
        
        // try {
        //     $response = Http::get($url);
    
        //     if ($response->successful()) {
        //         $xmlData = $response->body();
        //     } else {
        //         $xmlData = null;
        //         session()->flash('error', 'Failed to fetch data from API.');
        //     }
        // } catch (\Exception $e) {
        //     $xmlData = null;
        //     session()->flash('error', 'An error occurred: ' . $e->getMessage());
        // }
    
        // return view('admin.declare_outcomes.upcoming_result', compact('pageTitle', 'xmlData'));
    }
    
    protected function cleanKeys(array $array)
    {
        $cleanedArray = [];
        foreach ($array as $key => $value) {
            // Remove '@' from the key
            $newKey = ltrim($key, '@');
    
            // If value is an array, recursively clean keys
            if (is_array($value)) {
                $value = $this->cleanKeys($value);
            }
    
            $cleanedArray[$newKey] = $value;
        }
        return $cleanedArray;
    }
    
    public function upcomingSettlement($gameid) {
         $matchData = Game::where('games.id', $gameid)
            ->join('questions', 'questions.game_id', '=', 'games.id')
            ->join('bet_details', 'bet_details.question_id', '=', 'questions.id')
            ->join('bets', 'bets.id', '=', 'bet_details.bet_id')
            ->distinct()
            ->select('bets.matchid','games.category', 'bets.created_at')
            ->first();
            $matchId = $matchData->matchid;
            $category = $matchData->category;
            
            if(strlen($category) > 2) { 
                $cate = GoalCategory::where('name', $category)->orWhere('league', $category)->first();
                $category = $cate->id;
            }
            $createdAt = Carbon::parse($matchData->created_at)->format('Ym');;
            $pageTitle = 'Results ';
            // dd($category);
            
            if($matchId && $category){
                $data =  $this->getSettlementResult($category, $matchId);
                return view('admin.declare_outcomes.settlement_result', compact('pageTitle', 'data'));
            }
            $notify[] = ['error', 'Result not found'];
            return back()->withNotify($notify);
    }
    
    public function getSettlementResult($cat, $match){
        $url = "http://oddsfeed.goalserve.com/api/v1/odds/pre-game/settlements/matches?sportId={$cat}&matchesIds={$match}&k={$this->apiKey}&json=1";
        $client = new Client();
        
        try {
            $response = $client->get($url);
            $responseBody = $response->getBody();
            
            $data = json_decode($responseBody, true);
            return $data;

        } catch (\Exception $e) {
            return false;

        }
    }

}
