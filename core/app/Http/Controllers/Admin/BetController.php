<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\Question;

class BetController extends Controller {
    private $pageTitle;

    public function index() {
        $this->pageTitle = 'All';
        return $this->betData('');
    }

    public function pending() {
        $this->pageTitle = 'Pending';
        return $this->betData('pending');
    }

    public function won() {
        $this->pageTitle = 'Won';
        return $this->betData('won');
    }

    public function lose() {
        $this->pageTitle = 'Lose';
        return $this->betData('lose');
    }

    public function refunded() {
        $this->pageTitle = 'Refunded';
        return $this->betData('refunded');
    }

    protected function betData($scope) {
        $pageTitle = $this->pageTitle . ' Bets';
        if ($scope) {
            $bets = Bet::$scope();
        } else {
            $bets = Bet::query();
        }
        $bets = $bets->searchable(['bet_number'])->with(['user', 'bets' => function ($query) {
            $query->relationalData();
        }])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.bet.index', compact('pageTitle', 'bets'));
    }

    public function getByQuestion($id) {
        $question  = Question::with('betDetails')->findOrFail($id);
        $pageTitle = 'All bet for - ' . $question->title;
        $betNumber = $question->betDetails->pluck('bet_id')->unique();
        $bets      = Bet::whereIn('id', $betNumber)->searchable(['bet_number'])->with(['user', 'bets' => function ($query) {
            $query->relationalData();
        }])->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.bet.index', compact('pageTitle', 'bets'));
    }
}
