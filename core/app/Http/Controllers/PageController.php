<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Constants\Status;
use App\Models\AdminNotification;
use App\Models\Category;
use App\Models\Frontend;
use App\Models\Game;
use App\Models\Extension;
use App\Models\Language;
use App\Models\League;
use App\Models\Option;
use App\Models\SupportMessage;
use App\Models\SupportTicket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class PageController extends Controller
{
    public function events()
    {
        $pageTitle= "Events";
        $categories = $leagues = [];
        $today = Carbon::now()->toDateString();
        $news = News::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->orderBy('created_at', 'desc')
                ->paginate(getPaginate());
        // return view($this->activeTemplate . 'user.events', compact('pageTitle', 'news'));
        return view($this->activeTemplate . 'pages.events.events', compact('pageTitle', 'news', 'categories','leagues'));
    }

    public function eventDetails($id){
        $categories = $leagues = [];
        // $id = decrypt($id);
        $event = News::findOrFail($id);
        $pageTitle = $event->title;
        return response()->json($event);
    }

    public function livegame($categorySlug = null, $leagueSlug = null)
    {
        $pageTitle= "Live Game";

        $gameType   = session('game_type', 'running');

        $games      = Game::active()->$gameType();
        $categories = Category::getGames($gameType);

        if ($categorySlug) {
            $activeCategory = $categories->where('slug', $categorySlug)->first();
        } else {
            $activeCategory = $categories->where('games_count', $categories->max('games_count'))->first();
        }

        $leagues      = [];
        $activeLeague = null;

        if ($leagueSlug) {
            $activeLeague = League::where('slug', $leagueSlug)->active()->whereHas('category', function ($q) {
                $q->active();
            })->firstOrFail();

            $activeCategory = $activeLeague->category;
        }

        if ($activeCategory && $activeCategory->leagues->count()) {
            $leagues = $this->filterByLeagues($activeCategory, $gameType);
            if (!$leagueSlug) {
                $activeLeague = $leagues->first();
            }
        }

        $games = $games->where('league_id', @$activeLeague->id)->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->get();

        $liveGames =  Game::active()->running()->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->limit(60)->get();

        return view($this->activeTemplate . 'pages.games.live', compact('pageTitle', 'categories','leagues', 'games', 'activeCategory', 'activeLeague','liveGames'));
    }

    private function filterByLeagues($activeCategory, $gameType) {
        $leagues = $activeCategory->leagues();
        $gameType .= 'Game';
        return $leagues->withCount("$gameType as game_count")->orderBy('game_count', 'desc')->active()->get();
    }

    public function upcomming($categorySlug = null, $leagueSlug = null)
    {
        $pageTitle= "Upcomming Game";

        $gameType   = session('game_type', 'upcoming');

        $games      = Game::active()->$gameType();
        $categories = Category::getGames($gameType);

        if ($categorySlug) {
            $activeCategory = $categories->where('slug', $categorySlug)->first();
        } else {
            $activeCategory = $categories->where('games_count', $categories->max('games_count'))->first();
        }

        $leagues      = [];
        $activeLeague = null;

        if ($leagueSlug) {
            $activeLeague = League::where('slug', $leagueSlug)->active()->whereHas('category', function ($q) {
                $q->active();
            })->firstOrFail();

            $activeCategory = $activeLeague->category;
        }

        if ($activeCategory && $activeCategory->leagues->count()) {
            $leagues = $this->filterByLeagues($activeCategory, $gameType);
            if (!$leagueSlug) {
                $activeLeague = $leagues->first();
            }
        }

        $games = $games->where('league_id', @$activeLeague->id)->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->get();

        $upcommingGames =  Game::active()->upcoming()->with(['teamOne', 'teamTwo'])->with(['questions' => function ($q) {
            $q->active()
                ->resultUndeclared()->select('id', 'game_id', 'title', 'locked')
                ->withCount('betDetails')
                ->with('options', function ($option) {
                    $option->active();
                });
        }])->orderBy('id', 'desc')->limit(60)->get();

        return view($this->activeTemplate . 'pages.games.upcomming', compact('pageTitle', 'categories','leagues', 'games', 'activeCategory', 'activeLeague','upcommingGames'));
    }
}
