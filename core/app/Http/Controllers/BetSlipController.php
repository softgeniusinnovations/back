<?php

namespace App\Http\Controllers;

use App\Constants\Status;
use App\Models\Option;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BetSlipController extends Controller {

    public function addToBetSlip(Request $request) {
        $types     = implode(',', [Status::SINGLE_BET, Status::MULTI_BET]);
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer|gt:0',
            'type'   => "required|in:$types",
            'amount' => "nullable|gt:0",
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $option = Option::availableForBet()->with('question', function($q){
            $q->with('game');
        })->where('id', $request->id)->first();

        if (!$option) {
            return response()->json(['error' => 'Invalid option selected or this option may not available now']);
        }

        $oldData  = collect(session()->get('bets'));
        $isExists = $oldData->where('option_id', $request->id)->first();

        if ($oldData->count() && $isExists) {
            return response()->json(['error' => 'This option already exists in your slip']);
        }

        $sessionData = [
            'option_id'     => $option->id,
            'question_id'   => $option->question_id,
            'odds'          => $option->odds,
            'stake_amount'  => $request->amount ?? 0,
            'return_amount' => $request->amount * $option->odds ?? 0,
            'checker' => ($option->question->game->bet_start_time >= now()) ? 'Upcoming' : 'Live'
        ];
        $bet = json_decode(json_encode($sessionData));
        session()->push('bets', $bet);

        return view($this->activeTemplate . 'partials.bet_slip_item', compact('bet', 'option'));
    }

    public function remove($id) {

        $filteredData = collect(session('bets', []))->filter(function ($item) use ($id) {
            return $item->option_id !== (int) $id;
        });

        session(['bets' => $filteredData->all()]);
        return response()->json([
            'status' => 'success',
            'notify' => 'Removed from bet slip successfully',
        ]);
    }

    public function removeAll() {
        session()->forget('bets');
        return response()->json([
            'status' => 'success',
            'notify' => 'All removed from bet slip successfully',
        ]);
    }

    public function update(Request $request) {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer',
            'amount' => "required|numeric|gt:0",
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }
        $data                  = collect(session('bets', []));
        $option                = $data->where('option_id', $request->id)->first();
        $option->stake_amount  = $request->amount;
        $option->return_amount = $request->amount * $option->odds;
        session(['bets' => $data->toArray()]);
        return response()->json(['success' => 'Bet slip updated']);
    }

    public function updateAll(Request $request) {
        $validator = Validator::make($request->all(), [
            'amount' => "required|numeric|gte:0",
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $stakeAmount = $request->amount;

        $updatedData = collect(session('bets', []))->map(function ($item) use ($stakeAmount) {
            $item->stake_amount  = $stakeAmount;
            $item->return_amount = $stakeAmount * $item->odds;
            return $item;
        });
        session(['bets' => $updatedData]);
        return response()->json(['success' => 'Bet slip updated']);
    }
}
