<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Bet;

class BetLogController extends Controller {
    protected $pageTitle;

    public function index($type = null) {
        $pageTitle = 'All Bets';
        if ($type) {
            try {
                $bets = Bet::$type();
                $pageTitle = ucfirst($type) . ' ' . 'Bets';
            } catch (\Exception $e) {
                abort(404);
            }
        } else {
            $bets = Bet::query();
        }
        $bets = $bets->where('user_id', auth()->id())->searchable(['bet_number'])->with(['bets' => function ($query) {
            $query->relationalData();
        }])->orderBy('id', 'desc')->paginate(getPaginate());
        return view($this->activeTemplate . 'user.bet.index', compact('pageTitle', 'bets'));
    }
}
