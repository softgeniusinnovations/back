<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Game;
use App\Models\League;
use App\Models\Team;
use Carbon\Carbon;
use Illuminate\Http\Request;

class GameController extends Controller {
    protected $pageTitle;

    protected function gameData($scope = null) {

        if ($scope) {
            $games = Game::$scope();
        } else {
            $games = Game::query();
        }

        if (request()->start_time) {
            $games->DateTimeFilter('start_time');
        }

        if (request()->bet_start_time) {
            $games->DateTimeFilter('bet_start_time');
        }

        if (request()->bet_end_time) {
            $games->DateTimeFilter('bet_end_time');
        }

        $games = $games->with(['teamOne', 'teamTwo', 'league.category'])->filter(['league_id', 'team_one_id', 'team_two_id'])
            ->orderBy('id', 'desc')
            ->withCount(['questions'])
            ->paginate(getPaginate());

        $pageTitle = $this->pageTitle;

        $teamsOne = Team::rightJoin('games', 'teams.id', 'games.team_two_id')->select('teams.id', 'teams.name', 'teams.short_name')->distinct('teams.id')->get();
        $teamsTwo = Team::rightJoin('games', 'teams.id', 'games.team_one_id')->select('teams.id', 'teams.name', 'teams.short_name')->distinct('teams.id')->get();

        $teams = $teamsOne->union($teamsTwo)->unique();

        $leagues = League::whereHas('games')->get();

        return view('admin.game.index', compact('pageTitle', 'games', 'leagues', 'teams'));
    }

    public function index() {
        $this->pageTitle = 'All Games';
        return $this->gameData();
    }

    public function running() {
        $this->pageTitle = 'Running Games';
        return $this->gameData('running');
    }

    public function upcoming() {
        $this->pageTitle = 'Upcoming Games';
        return $this->gameData('upcoming');
    }

    public function Ended() {
        $this->pageTitle = 'Ended Games';
        return $this->gameData('expired');
    }

    public function create() {
        $pageTitle = 'Add New Game';
        $leagues   = League::with('category')->orderBy('name')->get();
        return view('admin.game.form', compact('pageTitle', 'leagues'));
    }

    public function teamsByCategory($categoryId) {
        $teams = Team::where('category_id', $categoryId)->orderBy('name')->get();

        if (count($teams)) {
            return response()->json([
                'teams' => $teams,
            ]);
        } else {
            return response()->json([
                'error' => 'No teams found for this league\'s category',
            ]);
        }
    }

    public function edit($id) {
        $game      = Game::findOrFail($id);
        $pageTitle = 'Update Game';
        $leagues   = League::latest()->with('category')->get();
        return view('admin.game.form', compact('game', 'pageTitle', 'leagues'));
    }

    public function store(Request $request, $id = 0) {
        $this->validation($request, $id);
        $league = League::findOrFail($request->league_id);

        if ($id) {
            $game         = Game::findOrFail($id);
            $notification = 'Game updated successfully';
        } else {
            $game         = new Game();
            $notification = 'Game added successfully';
        }

        $game->team_one_id    = $request->team_one_id;
        $game->team_two_id    = $request->team_two_id;
        $game->slug           = $request->slug;
        $game->league_id      = $league->id;
        $game->start_time     = Carbon::parse($request->start_time);
        $game->bet_start_time = Carbon::parse($request->bet_start_time);
        $game->bet_end_time   = Carbon::parse($request->bet_end_time);
        $game->save();

        $notify[] = ['success', $notification];

        if ($id) {
            return back()->withNotify($notify);
        }

        return to_route('admin.question.index', $game->id)->withNotify($notify);
    }

    public function updateStatus($id) {
        return Game::changeStatus($id);
    }

    protected function validation($request, $id) {
        $request->validate([
            'league_id'      => 'required|integer|gt:0',
            'team_one_id'    => 'required|integer|gt:0',
            'team_two_id'    => 'required|integer|gt:0|different:team_one_id',
            'slug'           => 'required|alpha_dash|max:255|unique:games,slug,' . $id,
            'start_time'     => 'required|date_format:Y-m-d h:i a',
            'bet_start_time' => 'required|date_format:Y-m-d h:i a',
            'bet_end_time'   => 'required|date_format:Y-m-d h:i a|after:bet_start_time',
        ], [
            'slug.alpha_dash'    => 'Only alpha numeric value. No space or special character is allowed',
            'bet_end_time.after' => 'Bet end time should be after the bet start time',
        ]);
    }
}
