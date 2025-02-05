<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Api\V2\ExternalApiController;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\Bet;
use App\Models\HomepageGame;
use App\Models\Deposit;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\TransectionProviders;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\GoalCategory;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Storage;
use App\Models\Currency;
use App\Models\Threshold;

class HomepageController extends Controller
{

    public function fetchData($url)
    {

        $client = new Client();

        try {

            $response = $client->get($url);

            $responseBody = $response->getBody()->getContents();

            return json_decode($responseBody, true);

        } catch (\Exception $e) {

            return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);

        }

    }

    public function getOrderIndex($name, $order)
    {

        $index = array_search($name, $order);

        return $index !== false ? $index : PHP_INT_MAX;

    }

    public function inPlayCategoryData($data)

    {

        foreach ($data['events'] as $eventId => &$eventData) {

            unset($eventData['stats'], $eventData['extra']);


            $filteredOdds = [

                'over_under' => null,

                'home_draw_away' => null,

                'home_away' => null,

                'Fulltime_Result' => null,

                'Double Chance' => null,

            ];


            if (isset($eventData['odds']) && is_array($eventData['odds'])) {

                foreach ($eventData['odds'] as &$oddsData) {

                    if (!isset($oddsData['participants']) || !is_array($oddsData['participants'])) {

                        continue;

                    }


                    // Filter for "Fulltime Result"

                    if (isset($oddsData['name']) && $oddsData['name'] === "Fulltime Result") {

                        $filteredOdds['Fulltime_Result'] = $this->filterParticipants($oddsData, ['Home', 'Draw', 'Away']);

                        continue;

                    }


                    // Filter for "Double Chance"

                    if (isset($oddsData['name']) && $oddsData['name'] === "Double Chance") {

                        $filteredOdds['Double Chance'] = $this->filterParticipants($oddsData, ['Home/Draw', 'Home/Away', 'Draw/Away']);

                        continue;

                    }


                    $participantNames = array_column($oddsData['participants'], 'name');

                    if ($this->isOverUnder($participantNames)) {

                        $filteredOdds['over_under'] = $this->filterParticipants($oddsData, ['Over', 'Under']);

                    } elseif ($this->isHomeDrawAway($participantNames)) {

                        $filteredOdds['home_draw_away'] = $this->filterParticipants($oddsData, ['Home', 'Draw', 'Away']);

                    } elseif ($this->isHomeAway($participantNames)) {

                        $filteredOdds['home_away'] = $this->filterParticipants($oddsData, ['Home', 'Away']);

                    }

                }

            }


            unset($eventData['odds']);

            $eventData['filtered_odds'] = $filteredOdds;

        }


        return $data;

    }


    private function isOverUnder($participantNames)

    {

        return in_array('Over', $participantNames) && in_array('Under', $participantNames);

    }


    private function isHomeDrawAway($participantNames)

    {

        return in_array('Home', $participantNames) && in_array('Draw', $participantNames) && in_array('Away', $participantNames);

    }


    private function isHomeAway($participantNames)

    {

        return in_array('Home', $participantNames) && in_array('Away', $participantNames);

    }


    private function filterParticipants($oddsData, $order)

    {

        $filteredParticipants = array_filter($oddsData['participants'], function ($participant) use ($order) {

            return in_array($participant['name'], $order);

        });


        usort($filteredParticipants, function ($a, $b) use ($order) {

            return array_search($a['name'], $order) - array_search($b['name'], $order);

        });


        $oddsData['participants'] = $filteredParticipants;

        return $oddsData;

    }


    public function index()
    {

        $pageTitle = 'Manage Homepage Game live';

        $cacheKey = 'odds_category_' . md5('category');

        if (Cache::has($cacheKey)) {

            $data = Cache::get($cacheKey);

        } else {
            $data = GoalCategory::where('status', 1)
                ->with('leagues')
                ->withCount('leagues')
                ->get();
            if ($data) {
                Cache::put($cacheKey, $data, 60 * 60 * 72);
            }
        }

        $homepagegames = HomepageGame::where('game_type', 1)->get();

//        return $homepagegames;


        return view('admin.game.managehomepage.index', compact('pageTitle', 'data', 'homepagegames'));

    }

    public function manageLiveGame(Request $request)
    {

        $requestData = json_decode($request->input('category_name'), true);
//        return $requestData;

        $inPlay = $requestData['in_play'] ?? null;
        $categoryName = $requestData['name'] ?? null;

        $pageTitle = 'Manage Homepage Game live';
        if ($categoryName == "Cricket") {
            $sport_id = 3;
            $type = 'live';

            $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

            try {
                $response = Http::get($apiUrl);


                if ($response->successful()) {
                    $data = $response->json();
                }
            } catch (\Exception $e) {
                $data = ['error' => 'Failed to fetch API data: ' . $e->getMessage()];
            }
        }
        else{
            $cacheKey = 'inplay_cache_' . md5($inPlay);
            if (Cache::has($cacheKey)) {
                $data = Cache::get($cacheKey);
            }
            else {
                $baseUrl = "http://inplay.goalserve.com/inplay-";
                switch ($inPlay) {
                    case "baseball":
                        $url = $baseUrl . "baseball.gz";
                        break;
                    case "basket":
                        $url = $baseUrl . "basket.gz";
                        break;
                    case "soccer":
                        $url = $baseUrl . "soccer.gz";
                        break;
                    case "tennis":
                        $url = $baseUrl . "tennis.gz";
                        break;
                    case "hockey":
                        $url = $baseUrl . "hockey.gz";
                        break;
                    case "volleyball":
                        $url = $baseUrl . "volleyball.gz";
                        break;
                    case "amfootball":
                        $url = $baseUrl . "amfootball.gz";
                        break;
                    case "esports":
                        $url = $baseUrl . "esports.gz";
                        break;
                    default:
                        $url = $baseUrl . "soccer.gz";
                        break;
                }
                $data = $this->fetchData($url);
                $modifiedData = $this->inPlayCategoryData($data);
                Cache::put($cacheKey, $modifiedData, 8);
                Cache::put($cacheKey . '_detail', $data, 8);
            }
        }


        $events = $data['events'] ?? $data;

//        return $events;

        $category = $inPlay??$categoryName;

        return view('admin.game.managehomepage.livegameselect', compact('events', 'pageTitle', 'category'));


    }

    public function storeLiveGame(Request $request)
    {
//        return $request;

        $category = $request->input('category');

        $eventIds = $request->input('event_ids', []);

        if($category=="Cricket"){
            if (!empty($eventIds)) {



                $insertData = [];

                foreach ($eventIds as $eventId => $eventData) {

                    $insertData[] = [

                        'game_type' => 1,

                        'category_name' => $category,

                        'match_id' => $eventData ?? null,

                        'match_name' => $eventData['info']['name'] ?? null,

                        'info' => json_encode($eventData['info'] ?? null),

                    ];

                }

                HomepageGame::insert($insertData);


//            $data['events'] = $filteredEvents;

            }
        }
        else{

            $category = $request->input('category');

            $eventIds = $request->input('event_ids', []);
            $cacheKey = 'inplay_cache_' . md5($category);

            if (Cache::has($cacheKey)) {


                $dataevents = Cache::get($cacheKey);

            }


            if (!empty($eventIds)) {

                $filteredEvents = array_filter($dataevents['events'], function ($eventId) use ($eventIds) {

                    return in_array($eventId, $eventIds);

                }, ARRAY_FILTER_USE_KEY);

                $insertData = [];

                foreach ($filteredEvents as $eventId => $eventData) {

                    $insertData[] = [

                        'game_type' => 1,

                        'category_name' => $category,

                        'match_id' => $eventData['info']['id'] ?? null,

                        'match_name' => $eventData['info']['name'] ?? null,

                        'info' => json_encode($eventData['info'] ?? null),

                    ];

                }

                HomepageGame::insert($insertData);


//            $data['events'] = $filteredEvents;

            }

        }




        $notify[] = ['success', 'Game save successfull'];

        return redirect()->route('admin.managehomegame')->withNotify($notify);

    }

    public function manageUpcomingGame(Request $request)
    {
        $data = [];


        $pageTitle = 'Manage Homepage Game Upcoming';

        if ($request->category_name === "Cricket") {
            $sport_id = 3;
            $type = 'upcoming';

            $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

            try {
                $response = Http::get($apiUrl);

                if ($response->successful()) {
                    $data = $response->json();
                }
            } catch (\Exception $e) {
                $data = ['error' => 'Failed to fetch API data: ' . $e->getMessage()];
            }
//            return $apiData;
        } else {
            $category = GoalCategory::select('id', 'name', 'in_play', 'image', 'league', 'game')->withCount('leagues')->with('leagues', function ($q) {

                return $q->select("id", "category", "category_id", "sub_cat_id", "name");

            })->where('status', 1)->where('name', $request->category_name)->first();

            $removeLiveGamesFromUpcoming = [];

            if ($category) {


                if ($category->in_play) {

                    $externalApiController = new ExternalApiController();

                    $removeLiveGamesFromUpcoming = $externalApiController->inPlayMatches($category->in_play);

                } else {

                    $removeLiveGamesFromUpcoming = [];

                }

                $filteredData = [];

                $cacheKey = 'odds_cache_' . md5($category->game);

//            if(Cache::has($cacheKey)) {

//

//                $data = Cache::get($cacheKey);

//            }

                $data = Cache::has($cacheKey) ? Cache::get($cacheKey) : [];


            }

        }


//        $events = $data['events'] ?? [];

//        $paginatedData = collect($data)->forPage($request->page ?? 1, 10);

        $category_name = $request->category_name;


//        return $data;

        return view('admin.game.managehomepage.upcominggameselect', compact('data', 'pageTitle', 'category_name'));


    }

    public function upcomingGame()
    {

        $pageTitle = 'Manage Homepage Game upcoming';

        $cacheKey = 'odds_category_' . md5('category');

        if (Cache::has($cacheKey)) {

            $data = Cache::get($cacheKey);

        } else {

            $data = GoalCategory::where('status', 1)
                ->with('leagues')
                ->withCount('leagues')
                ->get();


            if ($data) {


                Cache::put($cacheKey, $data, 60 * 60 * 72);

            }

        }

        $homepagegames = HomepageGame::where('game_type', 2)->get();

        return view('admin.game.managehomepage.upcoming', compact('pageTitle', 'data', 'homepagegames'));

    }

    public function storeUpcomingGame(Request $request)

    {

//        return $request;

        if ($request->category == "Soccer" || $request->category == "Football" || $request->category == "MMA" || $request->category == "Handball" || $request->category == "Volleyball" || $request->category == "Rugby Union" || $request->category == "Hockey" || $request->category == "Tennis") {

            $homegames = [];


            // Loop through selected matches grouped by category ID

            foreach ($request->selected_matches as $categoryId => $matchIds) {

                foreach ($matchIds as $matchId) {

                    $homegames[] = [

                        'game_type' => 2,

                        'category_name' => $request->category_name ?? 'Unknown',

                        'league_id' => $categoryId,

                        'match_id' => $matchId,

                    ];

                }

            }


            // Perform batch insert

            if (!empty($homegames)) {

                HomepageGame::insert($homegames);

                $notify[] = ['success', 'Game saved successfully'];

            } else {

                $notify[] = ['error', 'No games selected'];

            }


            return redirect()->route('admin.managehomegameup')->withNotify($notify);

        } else {

//            return $request;

            $homegames = [];


            // Ensure selected matches and league IDs are properly aligned

            foreach ($request->selected_match as $index => $matchId) {

                if (isset($request->league_ids[$index])) {


                    $homegames[] = [

                        'game_type' => 2,

                        'category_name' => $request->category_name,

                        'league_id' => $request->league_ids[$index], // Use corresponding league_id

                        'match_id' => $matchId,

                    ];

                }

            }


            // Perform batch insert

            HomepageGame::insert($homegames);


            $notify[] = ['success', 'Game saved successfully'];

            return redirect()->route('admin.managehomegameup')->withNotify($notify);


        }

//        return $request;


    }

    public function featuredGame()
    {

        $pageTitle = 'Manage Homepage Game Featured';

        $cacheKey = 'odds_category_' . md5('category');

        if (Cache::has($cacheKey)) {

            $data = Cache::get($cacheKey);

        } else {

            $data = GoalCategory::where('status', 1)
                ->with('leagues')
                ->withCount('leagues')
                ->get();


            if ($data) {


                Cache::put($cacheKey, $data, 60 * 60 * 72);

            }

        }

        $homepagegames = HomepageGame::where('game_type', 3)->get();

        return view('admin.game.managehomepage.featured', compact('pageTitle', 'data', 'homepagegames'));

    }


    public function manageFeatureGame(Request $request)
    {
//        return $request;


        if ($request->game_ype == 1) {

            $pageTitle = 'Manage Homepage Game Feature';

            if($request->category_name=="Cricket"){
                $sport_id = 3;
                $type = 'live';

                $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

                try {
                    $response = Http::get($apiUrl);


                    if ($response->successful()) {
                        $data = $response->json();
                    }
                } catch (\Exception $e) {
                    $data = ['error' => 'Failed to fetch API data: ' . $e->getMessage()];
                }
            }
            else{
                $cacheKey = 'inplay_cache_' . md5($request->category_name);


                if (Cache::has($cacheKey)) {

                    $data = Cache::get($cacheKey);

                }
                else {
                    $baseUrl = "http://inplay.goalserve.com/inplay-";

                    switch ($request->category_name) {

                        case "baseball":

                            $url = $baseUrl . "baseball.gz";

                            break;

                        case "basket":

                            $url = $baseUrl . "basket.gz";

                            break;

                        case "soccer":

                            $url = $baseUrl . "soccer.gz";

                            break;

                        case "tennis":

                            $url = $baseUrl . "tennis.gz";

                            break;

                        case "hockey":

                            $url = $baseUrl . "hockey.gz";

                            break;

                        case "volleyball":

                            $url = $baseUrl . "volleyball.gz";

                            break;

                        case "amfootball":

                            $url = $baseUrl . "amfootball.gz";

                            break;

                        case "esports":

                            $url = $baseUrl . "esports.gz";

                            break;

                        default:

                            // Handle unknown categories

                            $url = $baseUrl . "soccer.gz";

                            break;

                    }


                    $data = $this->fetchData($url);

                    $modifiedData = $this->inPlayCategoryData($data);

                    Cache::put($cacheKey, $modifiedData, 60 * 60 * 72);

                    Cache::put($cacheKey . '_detail', $data, 60 * 60 * 72);


                }
            }



//        if ($request->has('match_id')) {

//            $matchIds = explode(',', $request->match_id);

//            $filteredEvents = array_filter($data['events'], function ($eventId) use ($matchIds) {

//                return in_array($eventId, $matchIds);

//            }, ARRAY_FILTER_USE_KEY);

//

//

//            $data['events'] = $filteredEvents;

//        }


            $events = $data['events'] ?? $data;

            $category = $request->category_name;

//            return $events;

            return view('admin.game.managehomepage.featurelivegameselect', compact('events', 'pageTitle', 'category'));


        }
        else {

            $pageTitle = 'Manage Homepage Game Feature ';
            if($request->category_name=="Cricket"){
                $sport_id = 3;
                $type = 'upcoming';

                $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

                try {
                    $response = Http::get($apiUrl);

                    if ($response->successful()) {
                        $data = $response->json();
                    }
                } catch (\Exception $e) {
                    $data = ['error' => 'Failed to fetch API data: ' . $e->getMessage()];
                }
            }
            else{
                $category = GoalCategory::select('id', 'name', 'in_play', 'image', 'league', 'game')->withCount('leagues')->with('leagues', function ($q) {

                    return $q->select("id", "category", "category_id", "sub_cat_id", "name");

                })->where('status', 1)->where('name', $request->category_name)->first();

                $removeLiveGamesFromUpcoming = [];

                if ($category) {


                    if ($category->in_play) {

                        $externalApiController = new ExternalApiController();

                        $removeLiveGamesFromUpcoming = $externalApiController->inPlayMatches($category->in_play);

                    } else {

                        $removeLiveGamesFromUpcoming = [];

                    }

                    $filteredData = [];


                    $cacheKey = 'odds_cache_' . md5($category->game);


                    $data = Cache::has($cacheKey) ? Cache::get($cacheKey) : [];


                }

            }

            $category_name = $request->category_name;

//        return $data;
            return view('admin.game.managehomepage.featureupcominggameselect', compact('data', 'pageTitle', 'category_name'));


        }


    }


    public function storeFeatureGame(Request $request)
    {

//        return $request;

        $homegames = [];

        if ($request->type == "upcoming") {

            if ($request->category == "Soccer" || $request->category == "Football" || $request->category == "MMA" || $request->category == "Handball" || $request->category == "Volleyball" || $request->category == "Rugby Union" || $request->category == "Hockey" || $request->category == "Tennis") {

                $homegames = [];


                // Loop through selected matches grouped by category ID

                foreach ($request->selected_matches as $categoryId => $matchIds) {

                    foreach ($matchIds as $matchId) {

                        $homegames[] = [

                            'game_type' => 3,

                            'sub_type' => 2,

                            'category_name' => $request->category_name ?? 'Unknown',

                            'league_id' => $categoryId,

                            'match_id' => $matchId,

                        ];

                    }

                }


                // Perform batch insert

                if (!empty($homegames)) {

                    HomepageGame::insert($homegames);

                    $notify[] = ['success', 'Game saved successfully'];

                } else {

                    $notify[] = ['error', 'No games selected'];

                }


                return redirect()->route('admin.managehomegamefeatured')->withNotify($notify);

            } else {

//            return $request;

                $homegames = [];


                // Ensure selected matches and league IDs are properly aligned

                foreach ($request->selected_match as $index => $matchId) {

                    if (isset($request->league_ids[$index])) {


                        $homegames[] = [

                            'game_type' => 3,

                            'sub_type' => 2,

                            'category_name' => $request->category_name,

                            'league_id' => $request->league_ids[$index], // Use corresponding league_id

                            'match_id' => $matchId,

                        ];

                    }

                }


                // Perform batch insert

                HomepageGame::insert($homegames);


                $notify[] = ['success', 'Game saved successfully'];

                return redirect()->route('admin.managehomegamefeatured')->withNotify($notify);

            }

        } else {

            $match_ids = $request->event_ids ?? $request->selected_match;

            foreach ($match_ids as $matchId) {

                $homegames[] = [

                    'game_type' => 3,

                    'sub_type' => $request->sub_type,

                    'category_name' => $request->category ?? $request->category_name,

                    'league_id' => $request->league_id ?? '',

                    'match_id' => $matchId,

                ];

            }


            // Perform batch insert

            HomepageGame::insert($homegames);


            $notify[] = ['success', 'Game save successfull'];

            return redirect()->route('admin.managehomegamefeatured')->withNotify($notify);

        }


    }


    public function destroy($id)

    {

        $game = HomepageGame::find($id);

        if ($game) {

            $game->delete();

            $notify[] = ['success', 'Game delete successfull'];

            return redirect()->route('admin.managehomegame')->withNotify($notify);

        }

        return redirect()->back()->with('error', 'Game not found.');

    }

    public function destroyup($id)

    {

        $game = HomepageGame::find($id);

        if ($game) {

            $game->delete();

            $notify[] = ['success', 'Game delete successfull'];

            return redirect()->back()->withNotify($notify);

        }

        return redirect()->back()->with('error', 'Game not found.');

    }

    public function destroyfeature($id)

    {

        $game = HomepageGame::find($id);

        if ($game) {

            $game->delete();

            $notify[] = ['success', 'Game delete successfull'];

            return redirect()->back()->withNotify($notify);

        }

        return redirect()->back()->with('error', 'Game not found.');

    }

}




//class HomepageController extends Controller
//
//{
//
//    public function fetchData($url) {
//
//        $client = new Client();
//
//        try {
//
//            $response = $client->get($url);
//
//            $responseBody = $response->getBody()->getContents();
//
//            return json_decode($responseBody,true);
//
//        } catch (\Exception $e) {
//
//            return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);
//
//        }
//
//    }
//
//    public function getOrderIndex($name, $order) {
//
//        $index = array_search($name, $order);
//
//        return $index !== false ? $index : PHP_INT_MAX;
//
//    }
//
//
//
////    public function inPlayCategoryData($data){
//
////        $data = $data;
//
////        foreach ($data['events'] as $eventId => &$eventData) {
//
////            unset($eventData['stats']);
//
////            unset($eventData['extra']);
//
////
//
////            $filteredOdds = [
//
////                'over_under' => null,
//
////                'home_draw_away' => null,
//
////                'home_away'=>null,
//
////                'Fulltime_Result'=>null,
//
////                'Double Chance'=>null,
//
////            ];
//
////
//
////
//
////            if(isset($eventData['odds'])){
//
////                foreach ($eventData['odds'] as $oddsId => $oddsData) {
//
////                    if (isset($oddsData['participants']) && is_array($oddsData['participants'])) {
//
////                        $participantNames = array_column($oddsData['participants'], 'name');
//
////                    }
//
////
//
////                    if (in_array('Over', $participantNames) && in_array('Under', $participantNames)) {
//
////                        $overUnderParticipants = [];
//
////                        if(isset($oddsData['participants'])){
//
////                            foreach ($oddsData['participants'] as $participant) {
//
////                                if ($participant['name'] === 'Over' || $participant['name'] === 'Under') {
//
////                                    $overUnderParticipants[] = $participant;
//
////                                    if (count($overUnderParticipants) >= 2) {
//
////                                        break;
//
////                                    }
//
////                                }
//
////                            }
//
////                        }
//
////                        $order = ['Over', 'Under'];
//
////                        usort($overUnderParticipants, function($a, $b) use ($order) {
//
////                            return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//
////                        });
//
////                        $oddsData['participants'] = $overUnderParticipants;
//
////                        $filteredOdds['over_under'] = $oddsData;
//
////                    }
//
////                    if (in_array('Home', $participantNames) && in_array('Draw', $participantNames) && in_array('Away', $participantNames)) {
//
////                        $homeAwayParticipants = [];
//
////                        if(isset($oddsData['participants'])){
//
////                            foreach ($oddsData['participants'] as $participant) {
//
////                                if ($participant['name'] === 'Home' || $participant['name'] === 'Draw' || $participant['name'] === 'Away') {
//
////                                    $homeAwayParticipants[] = $participant;
//
////                                    if (count($homeAwayParticipants) >= 3) {
//
////                                        break;
//
////                                    }
//
////                                }
//
////                            }
//
////                        }
//
////                        $order = ['Home', 'Draw', 'Away'];
//
////                        usort($homeAwayParticipants, function($a, $b) use ($order) {
//
////                            return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//
////                        });
//
////                        $oddsData['participants'] = $homeAwayParticipants;
//
////
//
////                        $filteredOdds['home_draw_away'] = $oddsData;
//
////                        $filteredOdds['home_away'] = null;
//
////                    } elseif (in_array('Home', $participantNames) && in_array('Away', $participantNames)) {
//
////                        if (!$filteredOdds['home_draw_away']) {
//
////                            $homeAwayParticipants = [];
//
////                            if(isset($oddsData['participants'])){
//
////                                foreach ($oddsData['participants'] as $participant) {
//
////                                    if ($participant['name'] === 'Home' || $participant['name'] === 'Away') {
//
////                                        $homeAwayParticipants[] = $participant;
//
////                                        if (count($homeAwayParticipants) >= 2) {
//
////                                            break;
//
////                                        }
//
////                                    }
//
////                                }
//
////                            }
//
////                            $order = ['Home', 'Away'];
//
////                            usort($homeAwayParticipants, function($a, $b) use ($order) {
//
////                                return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//
////                            });
//
////                            $oddsData['participants'] = $homeAwayParticipants;
//
////                            $filteredOdds['home_away'] = $oddsData;
//
////                        }
//
////                    }
//
////                }
//
////            }
//
////            unset($eventData['odds']);
//
////            $eventData['filtered_odds'] = $filteredOdds;
//
////        }
//
////        return $data;
//
////    }
//
//    public function inPlayCategoryData($data)
//
//    {
//
//        foreach ($data['events'] as $eventId => &$eventData) {
//
//            unset($eventData['stats'], $eventData['extra']);
//
//
//
//            $filteredOdds = [
//
//                'over_under' => null,
//
//                'home_draw_away' => null,
//
//                'home_away' => null,
//
//                'Fulltime_Result' => null,
//
//                'Double Chance' => null,
//
//            ];
//
//
//
//            if (isset($eventData['odds']) && is_array($eventData['odds'])) {
//
//                foreach ($eventData['odds'] as &$oddsData) {
//
//                    if (!isset($oddsData['participants']) || !is_array($oddsData['participants'])) {
//
//                        continue;
//
//                    }
//
//
//
//                    // Filter for "Fulltime Result"
//
//                    if (isset($oddsData['name']) && $oddsData['name'] === "Fulltime Result") {
//
//                        $filteredOdds['Fulltime_Result'] = $this->filterParticipants($oddsData, ['Home', 'Draw', 'Away']);
//
//                        continue;
//
//                    }
//
//
//
//                    // Filter for "Double Chance"
//
//                    if (isset($oddsData['name']) && $oddsData['name'] === "Double Chance") {
//
//                        $filteredOdds['Double Chance'] = $this->filterParticipants($oddsData, ['Home/Draw', 'Home/Away', 'Draw/Away']);
//
//                        continue;
//
//                    }
//
//
//
//                    $participantNames = array_column($oddsData['participants'], 'name');
//
//                    if ($this->isOverUnder($participantNames)) {
//
//                        $filteredOdds['over_under'] = $this->filterParticipants($oddsData, ['Over', 'Under']);
//
//                    } elseif ($this->isHomeDrawAway($participantNames)) {
//
//                        $filteredOdds['home_draw_away'] = $this->filterParticipants($oddsData, ['Home', 'Draw', 'Away']);
//
//                    } elseif ($this->isHomeAway($participantNames)) {
//
//                        $filteredOdds['home_away'] = $this->filterParticipants($oddsData, ['Home', 'Away']);
//
//                    }
//
//                }
//
//            }
//
//
//
//            unset($eventData['odds']);
//
//            $eventData['filtered_odds'] = $filteredOdds;
//
//        }
//
//
//
//        return $data;
//
//    }
//
//
//
//    private function isOverUnder($participantNames)
//
//    {
//
//        return in_array('Over', $participantNames) && in_array('Under', $participantNames);
//
//    }
//
//
//
//    private function isHomeDrawAway($participantNames)
//
//    {
//
//        return in_array('Home', $participantNames) && in_array('Draw', $participantNames) && in_array('Away', $participantNames);
//
//    }
//
//
//
//    private function isHomeAway($participantNames)
//
//    {
//
//        return in_array('Home', $participantNames) && in_array('Away', $participantNames);
//
//    }
//
//
//
//    private function filterParticipants($oddsData, $order)
//
//    {
//
//        $filteredParticipants = array_filter($oddsData['participants'], function ($participant) use ($order) {
//
//            return in_array($participant['name'], $order);
//
//        });
//
//
//
//        usort($filteredParticipants, function ($a, $b) use ($order) {
//
//            return array_search($a['name'], $order) - array_search($b['name'], $order);
//
//        });
//
//
//
//        $oddsData['participants'] = $filteredParticipants;
//
//        return $oddsData;
//
//    }
//
//
//
//    public function index(){
//
//        $pageTitle='Manage Homepage Game live';
//
//        $cacheKey = 'odds_category_' . md5('category');
//
//        if (Cache::has($cacheKey)) {
//
//            $data = Cache::get($cacheKey);
//
//        }
//
//        else {
//
//            $data = GoalCategory::where('status', 1)
//
//                ->with('leagues')
//
//                ->withCount('leagues')
//
//                ->get();
//
//
//
//            if ($data) {
//
//
//
//                Cache::put($cacheKey, $data, 60 * 60 * 72);
//
//            }
//
//        }
//
//        $homepagegames=HomepageGame::where('game_type',1)->get();
//
////        return $homepagegames;
//
//
//
//        return view('admin.game.managehomepage.index',compact('pageTitle','data','homepagegames'));
//
//    }
//
//    public function manageLiveGame(Request $request){
//
////        return $request->category_name;
//
//        $pageTitle='Manage Homepage Game live';
//
//
//
//        $cacheKey = 'inplay_cache_' . md5($request->category_name);
//
//
//
//        if(Cache::has($cacheKey)) {
//
//
//
//            $data = Cache::get($cacheKey);
//
////            return $data;
//
//        }
//
//        else{
//
////            $url = "http://inplay.goalserve.com/inplay-baseball.gz";
//
//            $baseUrl = "http://inplay.goalserve.com/inplay-";
//
////            return $request->category_name;
//
//
//
//
//
//            switch ($request->category_name) {
//
//                case "baseball":
//
//                    $url = $baseUrl . "baseball.gz";
//
//                    break;
//
//                case "basket":
//
//                    $url = $baseUrl . "basket.gz";
//
//                    break;
//
//                case "soccer":
//
//                    $url = $baseUrl . "soccer.gz";
//
//                    break;
//
//                case "tennis":
//
//                    $url = $baseUrl . "tennis.gz";
//
//                    break;
//
//                case "hockey":
//
//                    $url = $baseUrl . "hockey.gz";
//
//                    break;
//
//                case "volleyball":
//
//                    $url = $baseUrl . "volleyball.gz";
//
//                    break;
//
//                case "amfootball":
//
//                    $url = $baseUrl . "amfootball.gz";
//
//                    break;
//
//                case "esports":
//
//                    $url = $baseUrl . "esports.gz";
//
//                    break;
//
//                default:
//
//                    // Handle unknown categories
//
//                    $url = $baseUrl . "soccer.gz";
//
//                    break;
//
//            }
//
//
//
//            $data =  $this->fetchData($url);
//
////            return $data;
//
//            $modifiedData = $this->inPlayCategoryData($data);
//
//            Cache::put($cacheKey, $modifiedData, 8 );
//
//            Cache::put($cacheKey.'_detail', $data, 8 );
//
//
//
//        }
//
////        if ($request->has('match_id')) {
//
////            $matchIds = explode(',', $request->match_id);
//
////            $filteredEvents = array_filter($data['events'], function ($eventId) use ($matchIds) {
//
////                return in_array($eventId, $matchIds);
//
////            }, ARRAY_FILTER_USE_KEY);
//
////
//
////
//
////            $data['events'] = $filteredEvents;
//
////        }
//
////        return $data;
//
//
//
//        $events = $data['events'] ?? [];
//
////        return $events;
//
//        $category=$request->category_name;
//
//        return view('admin.game.managehomepage.livegameselect', compact('events','pageTitle','category'));
//
//
//
//
//
//    }
//
//    public function storeLiveGame(Request $request){
//
//        $category = $request->input('category');
//
//        $eventIds = $request->input('event_ids', []);
//
//
//
//        $cacheKey = 'inplay_cache_' . md5($category);
//
//        if(Cache::has($cacheKey)) {
//
//
//
//            $dataevents = Cache::get($cacheKey);
//
//        }
//
//
//
//        if (!empty($eventIds)) {
//
//            $filteredEvents = array_filter($dataevents['events'], function ($eventId) use ($eventIds) {
//
//                return in_array($eventId, $eventIds);
//
//            }, ARRAY_FILTER_USE_KEY);
//
//            $insertData = [];
//
//            foreach ($filteredEvents as $eventId => $eventData) {
//
//                $insertData[] = [
//
//                    'game_type' => 1,
//
//                    'category_name' => $category,
//
//                    'match_id' => $eventData['info']['id'] ?? null,
//
//                    'match_name' => $eventData['info']['name'] ?? null,
//
//                    'info' => json_encode($eventData['info'] ?? null),
//
//                ];
//
//            }
//
//            HomepageGame::insert($insertData);
//
//
//
////            $data['events'] = $filteredEvents;
//
//        }
//
//        $notify[] = ['success','Game save successfull'];
//
//        return redirect()->route('admin.managehomegame')->withNotify($notify);
//
//    }
//
//    public function manageUpcomingGame(Request $request){
//
//        $pageTitle='Manage Homepage Game Upcoming';
//
//        $category = GoalCategory::select('id', 'name','in_play', 'image', 'league', 'game')->withCount('leagues')->with('leagues', function($q){
//
//            return $q->select("id","category","category_id","sub_cat_id","name");
//
//        })->where('status', 1)->where('name', $request->category_name)->first();
//
//        $removeLiveGamesFromUpcoming = [];
//
//        if($category) {
//
//
//
//            if ($category->in_play) {
//
//                $externalApiController = new ExternalApiController();
//
//                $removeLiveGamesFromUpcoming = $externalApiController->inPlayMatches($category->in_play);
//
//            } else {
//
//                $removeLiveGamesFromUpcoming = [];
//
//            }
//
//            $filteredData = [];
//
//            $cacheKey = 'odds_cache_' . md5($category->game);
//
////            if(Cache::has($cacheKey)) {
//
////
//
////                $data = Cache::get($cacheKey);
//
////            }
//
//            $data=Cache::has($cacheKey)?Cache::get($cacheKey):[];
//
//
//
//
//
//        }
//
//
//
////        $events = $data['events'] ?? [];
//
////        $paginatedData = collect($data)->forPage($request->page ?? 1, 10);
//
//        $category_name=$request->category_name;
//
////        return $data;
//
//        return view('admin.game.managehomepage.upcominggameselect', compact('data','pageTitle','category_name'));
//
//
//
//
//
//    }
//
//    public function upcomingGame(){
//
//        $pageTitle='Manage Homepage Game upcoming';
//
//        $cacheKey = 'odds_category_' . md5('category');
//
//        if (Cache::has($cacheKey)) {
//
//            $data = Cache::get($cacheKey);
//
//        }
//
//        else {
//
//            $data = GoalCategory::where('status', 1)
//
//                ->with('leagues')
//
//                ->withCount('leagues')
//
//                ->get();
//
//
//
//            if ($data) {
//
//
//
//                Cache::put($cacheKey, $data, 60 * 60 * 72);
//
//            }
//
//        }
//
//        $homepagegames=HomepageGame::where('game_type',2)->get();
//
//        return view('admin.game.managehomepage.upcoming',compact('pageTitle','data','homepagegames'));
//
//    }
//
//    public function storeUpcomingGame(Request $request)
//
//    {
//
////        return $request;
//
//        if ($request->category == "Soccer"||$request->category == "Football"||$request->category == "MMA"|| $request->category == "Handball"||$request->category == "Volleyball"||$request->category == "Rugby Union"||$request->category == "Hockey"||$request->category == "Tennis") {
//
//            $homegames = [];
//
//
//
//            // Loop through selected matches grouped by category ID
//
//            foreach ($request->selected_matches as $categoryId => $matchIds) {
//
//                foreach ($matchIds as $matchId) {
//
//                    $homegames[] = [
//
//                        'game_type' => 2,
//
//                        'category_name' => $request->category_name ?? 'Unknown',
//
//                        'league_id' => $categoryId,
//
//                        'match_id' => $matchId,
//
//                    ];
//
//                }
//
//            }
//
//
//
//            // Perform batch insert
//
//            if (!empty($homegames)) {
//
//                HomepageGame::insert($homegames);
//
//                $notify[] = ['success', 'Game saved successfully'];
//
//            } else {
//
//                $notify[] = ['error', 'No games selected'];
//
//            }
//
//
//
//            return redirect()->route('admin.managehomegameup')->withNotify($notify);
//
//        }
//
//
//
//        else{
//
////            return $request;
//
//            $homegames = [];
//
//
//
//            // Ensure selected matches and league IDs are properly aligned
//
//            foreach ($request->selected_match as $index => $matchId) {
//
//                if (isset($request->league_ids[$index])) {
//
//
//
//                    $homegames[] = [
//
//                        'game_type' => 2,
//
//                        'category_name' => $request->category_name,
//
//                        'league_id' => $request->league_ids[$index], // Use corresponding league_id
//
//                        'match_id' => $matchId,
//
//                    ];
//
//                }
//
//            }
//
//
//
//            // Perform batch insert
//
//            HomepageGame::insert($homegames);
//
//
//
//            $notify[] = ['success', 'Game saved successfully'];
//
//            return redirect()->route('admin.managehomegameup')->withNotify($notify);
//
//
//
//        }
//
////        return $request;
//
//
//
//    }
//
//    public function featuredGame(){
//
//        $pageTitle='Manage Homepage Game Featured';
//
//        $cacheKey = 'odds_category_' . md5('category');
//
//        if (Cache::has($cacheKey)) {
//
//            $data = Cache::get($cacheKey);
//
//        }
//
//        else {
//
//            $data = GoalCategory::where('status', 1)
//
//                ->with('leagues')
//
//                ->withCount('leagues')
//
//                ->get();
//
//
//
//            if ($data) {
//
//
//
//                Cache::put($cacheKey, $data, 60 * 60 * 72);
//
//            }
//
//        }
//
//        $homepagegames=HomepageGame::where('game_type',3)->get();
//
//        return view('admin.game.managehomepage.featured',compact('pageTitle','data','homepagegames'));
//
//    }
//
//
//
//    public function manageFeatureGame(Request $request){
//
//        if($request->game_ype==1){
//
//            $pageTitle='Manage Homepage Game Feature';
//
//            $cacheKey = 'inplay_cache_' . md5($request->category_name);
//
//
//
//            if(Cache::has($cacheKey)) {
//
//
//
//                $data = Cache::get($cacheKey);
//
//            }
//
//            else{
//
////            $url = "http://inplay.goalserve.com/inplay-baseball.gz";
//
//                $baseUrl = "http://inplay.goalserve.com/inplay-";
//
//
//
//
//
//
//
//                switch ($request->category_name) {
//
//                    case "baseball":
//
//                        $url = $baseUrl . "baseball.gz";
//
//                        break;
//
//                    case "basket":
//
//                        $url = $baseUrl . "basket.gz";
//
//                        break;
//
//                    case "soccer":
//
//                        $url = $baseUrl . "soccer.gz";
//
//                        break;
//
//                    case "tennis":
//
//                        $url = $baseUrl . "tennis.gz";
//
//                        break;
//
//                    case "hockey":
//
//                        $url = $baseUrl . "hockey.gz";
//
//                        break;
//
//                    case "volleyball":
//
//                        $url = $baseUrl . "volleyball.gz";
//
//                        break;
//
//                    case "amfootball":
//
//                        $url = $baseUrl . "amfootball.gz";
//
//                        break;
//
//                    case "esports":
//
//                        $url = $baseUrl . "esports.gz";
//
//                        break;
//
//                    default:
//
//                        // Handle unknown categories
//
//                        $url = $baseUrl . "soccer.gz";
//
//                        break;
//
//                }
//
//
//
//                $data =  $this->fetchData($url);
//
//                $modifiedData = $this->inPlayCategoryData($data);
//
//                Cache::put($cacheKey, $modifiedData, 60 * 60 * 72 );
//
//                Cache::put($cacheKey.'_detail', $data, 60 * 60 * 72 );
//
//
//
//            }
//
////        if ($request->has('match_id')) {
//
////            $matchIds = explode(',', $request->match_id);
//
////            $filteredEvents = array_filter($data['events'], function ($eventId) use ($matchIds) {
//
////                return in_array($eventId, $matchIds);
//
////            }, ARRAY_FILTER_USE_KEY);
//
////
//
////
//
////            $data['events'] = $filteredEvents;
//
////        }
//
//
//
//            $events = $data['events'] ?? [];
//
//            $category=$request->category_name;
//
//            return view('admin.game.managehomepage.featurelivegameselect', compact('events','pageTitle','category'));
//
//
//
//        }
//
//        else{
//
//            $pageTitle='Manage Homepage Game Feature ';
//
//            $category = GoalCategory::select('id', 'name','in_play', 'image', 'league', 'game')->withCount('leagues')->with('leagues', function($q){
//
//                return $q->select("id","category","category_id","sub_cat_id","name");
//
//            })->where('status', 1)->where('name', $request->category_name)->first();
//
//            $removeLiveGamesFromUpcoming = [];
//
//            if($category) {
//
//
//
//                if ($category->in_play) {
//
//                    $externalApiController = new ExternalApiController();
//
//                    $removeLiveGamesFromUpcoming = $externalApiController->inPlayMatches($category->in_play);
//
//                } else {
//
//                    $removeLiveGamesFromUpcoming = [];
//
//                }
//
//                $filteredData = [];
//
//                $cacheKey = 'odds_cache_' . md5($category->game);
//
////            if(Cache::has($cacheKey)) {
//
////
//
////                $data = Cache::get($cacheKey);
//
////            }
//
//                $data=Cache::has($cacheKey)?Cache::get($cacheKey):[];
//
//
//
//
//
//            }
//
//
//
////        $events = $data['events'] ?? [];
//
////        $paginatedData = collect($data)->forPage($request->page ?? 1, 10);
//
//            $category_name=$request->category_name;
//
////        return $data;
//
//            return view('admin.game.managehomepage.featureupcominggameselect', compact('data','pageTitle','category_name'));
//
//
//
//        }
//
//
//
//    }
//
//
//
//    public function storeFeatureGame(Request $request){
//
////        return $request;
//
//        $homegames = [];
//
//        if($request->type=="upcoming"){
//
//            if ($request->category == "Soccer"||$request->category == "Football"||$request->category == "MMA"|| $request->category == "Handball"||$request->category == "Volleyball"||$request->category == "Rugby Union"||$request->category == "Hockey"||$request->category == "Tennis") {
//
//                $homegames = [];
//
//
//
//                // Loop through selected matches grouped by category ID
//
//                foreach ($request->selected_matches as $categoryId => $matchIds) {
//
//                    foreach ($matchIds as $matchId) {
//
//                        $homegames[] = [
//
//                            'game_type' => 3,
//
//                            'sub_type' => 2,
//
//                            'category_name' => $request->category_name ?? 'Unknown',
//
//                            'league_id' => $categoryId,
//
//                            'match_id' => $matchId,
//
//                        ];
//
//                    }
//
//                }
//
//
//
//                // Perform batch insert
//
//                if (!empty($homegames)) {
//
//                    HomepageGame::insert($homegames);
//
//                    $notify[] = ['success', 'Game saved successfully'];
//
//                } else {
//
//                    $notify[] = ['error', 'No games selected'];
//
//                }
//
//
//
//                return redirect()->route('admin.managehomegamefeatured')->withNotify($notify);
//
//            }
//
//
//
//            else {
//
////            return $request;
//
//                $homegames = [];
//
//
//
//                // Ensure selected matches and league IDs are properly aligned
//
//                foreach ($request->selected_match as $index => $matchId) {
//
//                    if (isset($request->league_ids[$index])) {
//
//
//
//                        $homegames[] = [
//
//                            'game_type' => 3,
//
//                            'sub_type' => 2,
//
//                            'category_name' => $request->category_name,
//
//                            'league_id' => $request->league_ids[$index], // Use corresponding league_id
//
//                            'match_id' => $matchId,
//
//                        ];
//
//                    }
//
//                }
//
//
//
//                // Perform batch insert
//
//                HomepageGame::insert($homegames);
//
//
//
//                $notify[] = ['success', 'Game saved successfully'];
//
//                return redirect()->route('admin.managehomegamefeatured')->withNotify($notify);
//
//            }
//
//        }
//
//        else{
//
//            $match_ids=$request->event_ids??$request->selected_match;
//
//            foreach ($match_ids as $matchId) {
//
//                $homegames[] = [
//
//                    'game_type' => 3,
//
//                    'sub_type' => $request->sub_type,
//
//                    'category_name' => $request->category??$request->category_name,
//
//                    'league_id' => $request->league_id??'',
//
//                    'match_id' => $matchId,
//
//                ];
//
//            }
//
//
//
//            // Perform batch insert
//
//            HomepageGame::insert($homegames);
//
//
//
//            $notify[] = ['success','Game save successfull'];
//
//            return redirect()->route('admin.managehomegamefeatured')->withNotify($notify);
//
//        }
//
//
//
//    }
//
//
//
//    public function destroy($id)
//
//    {
//
//        $game = HomepageGame::find($id);
//
//        if ($game) {
//
//            $game->delete();
//
//            $notify[] = ['success','Game delete successfull'];
//
//            return redirect()->route('admin.managehomegame')->withNotify($notify);
//
//        }
//
//        return redirect()->back()->with('error', 'Game not found.');
//
//    }
//
//    public function destroyup($id)
//
//    {
//
//        $game = HomepageGame::find($id);
//
//        if ($game) {
//
//            $game->delete();
//
//            $notify[] = ['success','Game delete successfull'];
//
//            return redirect()->back()->withNotify($notify);
//
//        }
//
//        return redirect()->back()->with('error', 'Game not found.');
//
//    }
//
//    public function destroyfeature($id)
//
//    {
//
//        $game = HomepageGame::find($id);
//
//        if ($game) {
//
//            $game->delete();
//
//            $notify[] = ['success','Game delete successfull'];
//
//            return redirect()->back()->withNotify($notify);
//
//        }
//
//        return redirect()->back()->with('error', 'Game not found.');
//
//    }
//
//
//
//
//
//}