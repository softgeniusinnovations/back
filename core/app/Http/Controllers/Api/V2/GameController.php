<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Resources\CasinoCollection;
use App\Models\Category;
use App\Models\Game;
use App\Models\League;
use Illuminate\Http\Request;

class GameController extends Controller
{
    public function getCasinoData(Request $request){

        $gameType   = $request->has('game_type') ? $request->game_type : 'running';
        $games      = Game::active()->$gameType();
        $categories = Category::getGames($gameType);
        $leagues      = [];
        $activeLeague = null;

        if($request->has('category_slug')){
            $activeCategory = $categories->where('slug', $request->category_slug)->first();
        }else{
            $activeCategory = $categories->where('games_count', $categories->max('games_count'))->first();
        }

        if($request->has('league_slug')){
            $activeLeague = League::where('slug', $request->league_slug)->active()->whereHas('category', function ($q) {
                $q->active();
            })->firstOrFail();

            $activeCategory = $activeLeague->category;
        }

        if ($activeCategory && $activeCategory->leagues->count()) {
            $leagues = $this->filterByLeagues($activeCategory, $gameType);
            if (!$request->has('league_slug')) {
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

        $payload = [
            'status'         => true,
            'data' => CasinoCollection::collection($games),
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    private function filterByLeagues($activeCategory, $gameType) {
        $leagues = $activeCategory->leagues();
        $gameType .= 'Game';
        return $leagues->withCount("$gameType as game_count")->orderBy('game_count', 'desc')->active()->get();
    }

    public function getGameCategory($gameType){
        $categories = Category::getGames($gameType);
        $payload = [
            'status'         => true,
            'data' => $categories,
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);

    }
}
