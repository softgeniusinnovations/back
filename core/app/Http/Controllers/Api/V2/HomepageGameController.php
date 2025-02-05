<?php

namespace App\Http\Controllers\Api\V2;

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

class HomepageGameController extends Controller
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

//    public function inPlayCategoryData($data)
//    {
//        $data = $data;
//        foreach ($data['events'] as $eventId => &$eventData) {
//            unset($eventData['stats']);
//            unset($eventData['extra']);
//
//            $filteredOdds = [
//                'over_under' => null,
//                'home_draw_away' => null,
//                'home_away' => null
//            ];
//
//
//            if (isset($eventData['odds'])) {
//                foreach ($eventData['odds'] as $oddsId => $oddsData) {
//                    if (isset($oddsData['participants']) && is_array($oddsData['participants'])) {
//                        $participantNames = array_column($oddsData['participants'], 'name');
//                    }
//
//                    if (in_array('Over', $participantNames) && in_array('Under', $participantNames)) {
//                        $overUnderParticipants = [];
//                        if (isset($oddsData['participants'])) {
//                            foreach ($oddsData['participants'] as $participant) {
//                                if ($participant['name'] === 'Over' || $participant['name'] === 'Under') {
//                                    $overUnderParticipants[] = $participant;
//                                    if (count($overUnderParticipants) >= 2) {
//                                        break;
//                                    }
//                                }
//                            }
//                        }
//                        $order = ['Over', 'Under'];
//                        usort($overUnderParticipants, function ($a, $b) use ($order) {
//                            return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//                        });
//                        $oddsData['participants'] = $overUnderParticipants;
//                        $filteredOdds['over_under'] = $oddsData;
//                    }
//                    if (in_array('Home', $participantNames) && in_array('Draw', $participantNames) && in_array('Away', $participantNames)) {
//                        $homeAwayParticipants = [];
//                        if (isset($oddsData['participants'])) {
//                            foreach ($oddsData['participants'] as $participant) {
//                                if ($participant['name'] === 'Home' || $participant['name'] === 'Draw' || $participant['name'] === 'Away') {
//                                    $homeAwayParticipants[] = $participant;
//                                    if (count($homeAwayParticipants) >= 3) {
//                                        break;
//                                    }
//                                }
//                            }
//                        }
//                        $order = ['Home', 'Draw', 'Away'];
//                        usort($homeAwayParticipants, function ($a, $b) use ($order) {
//                            return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//                        });
//                        $oddsData['participants'] = $homeAwayParticipants;
//
//                        $filteredOdds['home_draw_away'] = $oddsData;
//                        $filteredOdds['home_away'] = null;
//                    } elseif (in_array('Home', $participantNames) && in_array('Away', $participantNames)) {
//                        if (!$filteredOdds['home_draw_away']) {
//                            $homeAwayParticipants = [];
//                            if (isset($oddsData['participants'])) {
//                                foreach ($oddsData['participants'] as $participant) {
//                                    if ($participant['name'] === 'Home' || $participant['name'] === 'Away') {
//                                        $homeAwayParticipants[] = $participant;
//                                        if (count($homeAwayParticipants) >= 2) {
//                                            break;
//                                        }
//                                    }
//                                }
//                            }
//                            $order = ['Home', 'Away'];
//                            usort($homeAwayParticipants, function ($a, $b) use ($order) {
//                                return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//                            });
//                            $oddsData['participants'] = $homeAwayParticipants;
//                            $filteredOdds['home_away'] = $oddsData;
//                        }
//                    }
//                }
//            }
//            unset($eventData['odds']);
//            $eventData['filtered_odds'] = $filteredOdds;
//
//        }
//        return $data;
//    }

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
                    if (isset($oddsData['name']) && $oddsData['name'] == "Fulltime Result") {
                        $filteredOdds['Fulltime_Result'] = $this->filterParticipants($oddsData, ['Home', 'Draw', 'Away']);
                        continue;
                    }

                    // Filter for "Double Chance"
                    if (isset($oddsData['name']) && $oddsData['name'] == "Double Chance") {
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


    public function getLivegames()
    {
        $match_ids = HomepageGame::where('game_type', 1)->get();
        $allFilteredData = [];
//        return $match_ids;
        foreach ($match_ids as $match_id) {

            if ($match_id->category_name == "Cricket") {
                $sport_id = 3;
                $type = 'live';
                $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

                try {
                    $response = Http::get($apiUrl);
                    if (!$response->successful()) {
                        Log::error('API call failed', ['url' => $apiUrl, 'response' => $response->body()]);
                        continue;
                    }

                    $data = $response->json();

                    if (!isset($data['data']['results']) || !is_array($data['data']['results'])) {
                        continue;
                    }

                    foreach ($data['data']['results'] as $match) {
                        if ($match['id'] == $match_id->match_id) {
                            $match = [$match]; // Ensure it's an array
                            if (!isset($allFilteredData[$match_id->category_name])) {
                                $allFilteredData[$match_id->category_name] = $data;
                                $allFilteredData[$match_id->category_name]['events'] = $match;
                            } else {
                                $allFilteredData[$match_id->category_name]['events'] = array_merge(
                                    $allFilteredData[$match_id->category_name]['events'],
                                    $match
                                );
                            }
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('API request error', ['message' => $e->getMessage()]);
                    continue;
                }
            }

            else{
                $cacheKey = 'inplay_cache_' . md5($match_id->category_name);

                if (Cache::has($cacheKey)) {
                    $data = Cache::get($cacheKey);
                    $matchIdsArray = is_array($match_id->match_id) ? $match_id->match_id : explode(',', $match_id->match_id);

                    $filteredEvents = array_filter($data['events'], function ($eventId) use ($matchIdsArray) {
                        return in_array($eventId, $matchIdsArray);
                    }, ARRAY_FILTER_USE_KEY);
                    $data['events'] = $filteredEvents;
                    // Merge filtered data into the result set, grouped by category name
                    if (!isset($allFilteredData[$match_id->category_name])) {
                        $allFilteredData[$match_id->category_name] = $data;
                    } else {
                        // Merge events if the category is already processed
                        $allFilteredData[$match_id->category_name]['events'] = array_merge(
                            $allFilteredData[$match_id->category_name]['events'],
                            $filteredEvents
                        );
                    }
                } else {
//
                    $baseUrl = "http://inplay.goalserve.com/inplay-";


                    switch ($match_id->category_name) {
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

        }
//        return $allFilteredData;

        return response()->json([
            'message' => 'Data fetch successfully',
            'data' => $allFilteredData
        ], 200);
    }


    public function getUpcomingGamestest(Request $request)
    {
        $games = HomepageGame::where('game_type', 2)->get();
        Log::info("upcoming game");
        $allFilteredData = [];

        foreach ($games as $game) {
            if ($game->category_name == "Cricket") {
                $sport_id = 3;
                $type = 'upcoming';
                $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

                try {
                    $response = Http::get($apiUrl);
                    if (!$response->successful()) {
                        Log::error('API call failed', ['url' => $apiUrl, 'response' => $response->body()]);
                        continue;
                    }

                    $data = $response->json();

                    if (!isset($data['data']['results']) || !is_array($data['data']['results'])) {
                        continue;
                    }

                    foreach ($data['data']['results'] as $match) {
                        if ($match['id'] == $game->match_id) {
                            $allFilteredData[] = [
                                'league_id' => $match['league']['id'] ?? null,
                                'league_name' => $match['league']['name'] ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } catch (\Exception $e) {
                    Log::error('API request error', ['message' => $e->getMessage()]);
                    continue;
                }
            }
            else{
                $category = GoalCategory::select('id', 'name', 'in_play', 'image', 'league', 'game')
                    ->where('status', 1)
                    ->where('name', $game->category_name)
                    ->first();



                if (!$category) {
                    continue; // Skip if no matching category found
                }

                // Retrieve cached data for this category
                $cacheKey = 'odds_cache_' . md5($category->game);
                $data = Cache::get($cacheKey, null);

                if (!$data || !isset($data->scores->category)) {
                    continue; // Skip if no valid data in cache
                }

                // Process each league in the cached data
                foreach ($data->scores->category as $league) {
                    $leagueId = $league->gid ?? $league->id ?? null;

                    // Ensure league ID matches
                    if ($leagueId != $game->league_id) {
                        continue;
                    }

                    // Check matches inside the league
                    if (isset($league->matches->match)) {
                        $matches = $league->matches->match;

                        // Handle array of matches
                        if (is_array($matches)) {
                            foreach ($matches as $match) {
                                if (isset($match->id) && $match->id == $game->match_id) {
                                    $allFilteredData[] = [
                                        'league_id' => $leagueId,
                                        'league_name' => $league->name ?? 'Unknown League',
                                        'category_name' => $game->category_name,
                                        'match' => $match,
                                    ];
                                }
                            }
                        } // Handle single match as object
                        elseif (is_object($matches) && isset($matches->id) && $matches->id == $game->match_id) {
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $matches,
                            ];
                        }
                    }
                }
            }


        }



        return response()->json($allFilteredData);
    }

//    public function getUpcomingGames(Request $request)
//    {
//        $games = HomepageGame::where('game_type', 2)->get();
////        Log::info("games");
////        Log::info($games);
//        $allFilteredData = [];
//        $addedMatchIds = [];
//
//        foreach ($games as $game) {
//            // Fetch the category for the current game
//            $category = GoalCategory::select('id', 'name', 'in_play', 'image', 'league', 'game')
//                ->where('status', 1)
//                ->where('name', $game->category_name)
//                ->first();
//
//
//
//            if (!$category) {
//                continue;
//            }
//
//            // Retrieve cached data for this category
//            $cacheKey = 'odds_cache_' . md5($category->game);
//            $data = Cache::get($cacheKey, null);
////            return $data;
//
//            if (!$data || !isset($data->scores->category)) {
//                continue;
//            }
//
//
//            switch ($category->game) {
//                case 'baseball_10':
//                    $this->processBaseballData($data, $game, $allFilteredData, $addedMatchIds);
//                    break;
//
//                case 'football_10':
//                    $this->processFootballData($data, $game, $allFilteredData, $addedMatchIds);
//                    break;
//                case 'soccer_10':
////                    Log::info($category->game);
//                    $this->processSoccarData($data,$game, $allFilteredData, $addedMatchIds);
//                    break;
//                case 'cricket_10':
////                    Log::info($category->game);
//                    $this->processCricketData($data,$game, $allFilteredData, $addedMatchIds);
//                    break;
//                default:
//
//                    Log::warning('Unknown game type encountered', ['game' => $category->game]);
//                    break;
//            }
//        }
//        return response()->json([
//            'message' => 'Data fetch successfully',
//            'data' => $allFilteredData
//        ], 200);
//
//
//    }

    public function getUpcomingGames(Request $request)
    {
        $allFilteredData = [];
        $addedMatchIds = [];

        // Preload GoalCategory data into memory to reduce queries
        $categories = GoalCategory::select('id', 'name', 'in_play', 'image', 'league', 'game')
            ->where('status', 1)
            ->get()
            ->keyBy('name');

        // Process games in chunks to avoid memory exhaustion
        HomepageGame::where('game_type', 2)->chunk(100, function ($games) use ($categories, &$allFilteredData, &$addedMatchIds) {
            foreach ($games as $game) {
                if ($game->category_name == "Cricket") {
                    $sport_id = 3;
                    $type = 'upcoming';
                    $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

                    try {
                        $response = Http::get($apiUrl);
                        if (!$response->successful()) {
                            Log::error('API call failed', ['url' => $apiUrl, 'response' => $response->body()]);
                            continue;
                        }

                        $data = $response->json();

                        if (!isset($data['data']['results']) || !is_array($data['data']['results'])) {
                            continue;
                        }

                        foreach ($data['data']['results'] as $match) {
                            if ($match['id'] == $game->match_id) {
                                $allFilteredData[] = [
                                    'league_id' => $match['league']['id'] ?? null,
                                    'league_name' => $match['league']['name'] ?? 'Unknown League',
                                    'category_name' => $game->category_name,
                                    'match' => $match,
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('API request error', ['message' => $e->getMessage()]);
                        continue;
                    }
                }
                else{
                    $this->processGame($game, $categories, $allFilteredData, $addedMatchIds);
                }


            }
        });

        // Stream response to avoid loading large datasets into memory
        return response()->stream(function () use ($allFilteredData) {
            echo json_encode([
                'message' => 'Data fetch successfully',
                'data' => $allFilteredData,
            ]);
        }, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * Process a single game and append data to filtered results.
     */
    private function processGame($game, $categories, &$allFilteredData, &$addedMatchIds)
    {
        // Fetch the category for the current game
        $category = $categories->get($game->category_name);

        if (!$category) {
            return; // Skip if category doesn't exist
        }

        // Retrieve cached data for this category
        $cacheKey = 'odds_cache_' . md5($category->game);

        $data = Cache::get($cacheKey);

//        Log::info((array) $data);

        // Skip if cached data is unavailable or improperly structured
//        if (!$data || !isset($data->scores->category)) {
//            return;
//        }

        // Process data based on game type
        switch ($category->game) {
            case 'baseball_10':
                $this->processBaseballData($data, $game, $allFilteredData, $addedMatchIds);
                break;

            case 'football_10':
                $this->processFootballData($data, $game, $allFilteredData, $addedMatchIds);
                break;

            case 'soccer_10':
//                Log::info("inside soccer_10");
                $this->processSoccarData($data, $game, $allFilteredData, $addedMatchIds);
                break;

            case 'cricket_10':
                $this->processCricketData($data, $game, $allFilteredData, $addedMatchIds);
                break;
            case 'basket_10':
                $this->processBusketballData($data, $game, $allFilteredData, $addedMatchIds);
                break;
            case 'tennis_10':
                $this->processtennisData($data, $game, $allFilteredData, $addedMatchIds);
                break;
            case 'hockey_10':
                $this->processhockyData($data, $game, $allFilteredData, $addedMatchIds);
                break;
            case 'handball_10':
                $this->processhandballData($data, $game, $allFilteredData, $addedMatchIds);
                break;
            case 'volleyball_10':
                $this->processvolleyData($data, $game, $allFilteredData, $addedMatchIds);
                break;
            case 'rugby_10':
                $this->processrugby_10Data($data, $game, $allFilteredData, $addedMatchIds);
                break;
            case 'mma_10':
                $this->processmma_10Data($data, $game, $allFilteredData, $addedMatchIds);
                break;
            default:
//                Log::warning('Unknown game type encountered', ['game' => $category->game]);
                break;
        }
    }


// Process Baseball Data
    private function processBaseballData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $matchId = $match->id ?? $match->{'@id'} ?? null;
                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {
                    $matchId = $matches->id ?? $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    private function processBusketballData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $matchId = $match->id ?? $match->{'@id'} ?? null;
                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {
                    $matchId = $matches->id ?? $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    private function processCricketData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $matchId = $match->id ?? $match->{'@id'} ?? null;
                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {
                    $matchId = $matches->id ?? $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    private function processSoccarData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
//        Log::info($game);
        foreach ($data->scores->categories as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->gid ?? $league->id ?? null;


            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches)) {
                $matches = $league->matches;

                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $matchId = $match->id ?? $match->id ?? null;
                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? $league->name ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {
                    $matchId = $matches->id ?? $matches->id ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? $league->name ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    private function processtennisData($data, $game, &$allFilteredData, &$addedMatchIds)
    {

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? null;


            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches)) {
                $matches = $league->matches->match;
//                Log::info('Matches:', is_object($matches) ? json_decode(json_encode($matches), true) : $matches);

                if (is_array($matches)) {
                    foreach ($matches as $match) {
//
                        $matchId = $match->id ?? null;

                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {

                    $matchId = $matches->id ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    private function processhockyData($data, $game, &$allFilteredData, &$addedMatchIds)
    {

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;


            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches)) {
                $matches = $league->matches->match;
//                Log::info('Matches:', is_object($matches) ? json_decode(json_encode($matches), true) : $matches);

                if (is_array($matches)) {
                    foreach ($matches as $match) {
//
                        $matchId = $match->id ?? $match->{'@id'} ?? null;

                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {

                    $matchId = $matches->id ?? $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,

                        ];
                    }
                }
            }
        }
    }

    private function processhandballData($data, $game, &$allFilteredData, &$addedMatchIds)
    {

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;


            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches)) {
                $matches = $league->matches->match;
//                Log::info('Matches:', is_object($matches) ? json_decode(json_encode($matches), true) : $matches);

                if (is_array($matches)) {
                    foreach ($matches as $match) {
//
                        $matchId = $match->id ?? $match->{'@id'} ?? null;

                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {

                    $matchId = $matches->id ?? $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,

                        ];
                    }
                }
            }
        }
    }

    private function processvolleyData($data, $game, &$allFilteredData, &$addedMatchIds)
    {

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;


            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches)) {
                $matches = $league->matches->match;
//                Log::info('Matches:', is_object($matches) ? json_decode(json_encode($matches), true) : $matches);

                if (is_array($matches)) {
                    foreach ($matches as $match) {
//
                        $matchId = $match->id ?? $match->{'@id'} ?? null;

                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {

                    $matchId = $matches->id ?? $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,

                        ];
                    }
                }
            }
        }
    }


// Process Football Data
    private function processFootballData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        foreach ($data->scores->category as $league) {
            $leagueId = $league->{'@gid'} ?? $league->{'@id'} ?? null;

            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $matchId = $match->{'@id'} ?? null;
                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {
                    $matchId = $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    private function processrugby_10Data($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? null;

            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $matchId = $match->id ?? null;
                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->name ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {
                    $matchId = $matches->id ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->name ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    private function processmma_10Data($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        foreach ($data->scores->category as $league) {
            $leagueId = $league->{'@gid'} ?? $league->{'@id'} ?? null;

            if ($leagueId != $game->league_id) {
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $matchId = $match->{'@id'} ?? null;
                        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                            $addedMatchIds[] = $matchId;
                            $allFilteredData[] = [
                                'league_id' => $leagueId,
                                'league_name' => $league->{'@name'} ?? 'Unknown League',
                                'category_name' => $game->category_name,
                                'match' => $match,
                            ];
                        }
                    }
                } elseif (is_object($matches)) {
                    $matchId = $matches->{'@id'} ?? null;
                    if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
                        $addedMatchIds[] = $matchId;
                        $allFilteredData[] = [
                            'league_id' => $leagueId,
                            'league_name' => $league->{'@name'} ?? 'Unknown League',
                            'category_name' => $game->category_name,
                            'match' => $matches,
                        ];
                    }
                }
            }
        }
    }

    public function getfeatureGames(Request $request)
    {
        $games = HomepageGame::where('game_type', 3)->get();

        $allFilteredData = [
            'sub_type_1' => [],
            'sub_type_2' => [],
        ];

        $addedMatchIds = [];

        foreach ($games as $game) {
            if ($game->sub_type == 1) {
                if($game->category_name=="Cricket"){
                    $sport_id = 3;
                    $type = 'live';
                    $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

                    try {
                        $response = Http::get($apiUrl);
                        if (!$response->successful()) {
                            Log::error('API call failed', ['url' => $apiUrl, 'response' => $response->body()]);
                            continue;
                        }

                        $data = $response->json();


                        if (!isset($data['data']['results']) || !is_array($data['data']['results'])) {
                            continue;
                        }

                        foreach ($data['data']['results'] as $match) {
                            if ($match['id'] == $game->match_id) {
                                $match = [$match]; // Ensure it's an array
                                if (!isset($allFilteredData['sub_type_1'][$game->category_name])) {
                                    $allFilteredData['sub_type_1'][$game->category_name] = $data;
                                    $allFilteredData['sub_type_1'][$game->category_name]['events'] = $match;
                                } else {
                                    $allFilteredData['sub_type_1'][$game->category_name]['events'] = array_merge(
                                        $allFilteredData['sub_type_1'][$game->category_name]['events'],
                                        $match
                                    );
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('API request error', ['message' => $e->getMessage()]);
                        continue;
                    }
                }
                else{
                    $cacheKey = 'inplay_cache_' . md5($game->category_name);

                    if (Cache::has($cacheKey)) {
                        $data = Cache::get($cacheKey);
                        $matchIdsArray = is_array($game->match_id) ? $game->match_id : explode(',', $game->match_id);

                        $filteredEvents = array_filter($data['events'], function ($eventId) use ($matchIdsArray) {
                            return in_array($eventId, $matchIdsArray);
                        }, ARRAY_FILTER_USE_KEY);

                        $data['events'] = $filteredEvents;

                        if (!isset($allFilteredData['sub_type_1'][$game->category_name])) {
                            $allFilteredData['sub_type_1'][$game->category_name] = $data;
                        } else {
                            $allFilteredData['sub_type_1'][$game->category_name]['events'] = array_merge(
                                $allFilteredData['sub_type_1'][$game->category_name]['events'],
                                $filteredEvents
                            );
                        }
                    }
                    else {
                        $baseUrl = "http://inplay.goalserve.com/inplay-";
                        $url = "";

                        // Determine the URL based on the category name
                        switch ($game->category_name) {
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
                        Cache::put($cacheKey, $modifiedData, 60 * 60 * 72);
                        Cache::put($cacheKey . '_detail', $data, 60 * 60 * 72);

                        $allFilteredData['sub_type_1'][$game->category_name] = $modifiedData;
                    }
                }

            }
            else {
                if($game->category_name=="Cricket"){
                    $sport_id = 3;
                    $type = 'upcoming';
                    $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type]);

                    try {
                        $response = Http::get($apiUrl);
                        if (!$response->successful()) {
                            Log::error('API call failed', ['url' => $apiUrl, 'response' => $response->body()]);
                            continue;
                        }

                        $data = $response->json();

                        if (!isset($data['data']['results']) || !is_array($data['data']['results'])) {
                            continue;
                        }

                        foreach ($data['data']['results'] as $match) {
                            if ($match['id'] == $game->match_id) {
                                $addedMatchIds[] = $match['id'];
                                $allFilteredData['sub_type_2'][] = [
                                    'league_id' => $match['league']['id'] ?? null,
                                    'league_name' => $match['league']['name'] ?? 'Unknown League',
                                    'category_name' => $game->category_name,
                                    'match' => $match,
                                ];
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('API request error', ['message' => $e->getMessage()]);
                        continue;
                    }
                }
                else{
                    $category = GoalCategory::select('id', 'name', 'in_play', 'image', 'league', 'game')
                        ->where('status', 1)
                        ->where('name', $game->category_name)
                        ->first();

                    if (!$category) {
                        continue;
                    }

                    $cacheKey = 'odds_cache_' . md5($category->game);
                    $data = Cache::get($cacheKey, null);


//                if (!$data || !isset($data->scores->category)) {
//                    continue;
//                }

                    switch ($category->game) {
                        case 'baseball_10':
                            $this->processBaseballData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        case 'basket_10':
                            $this->processBusketballfetureData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        case 'football_10':
                            $this->processFootballfeatureData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        case 'soccer_10':
                            $this->processSoccarfeatureData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        case 'tennis_10':
                            $this->processtennisfeaturedData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        case 'hockey_10':
                            $this->processhockeyfeaturedData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        case 'handball_10':
                            $this->processhandballfeaturedData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        case 'volleyball_10':
                            $this->processvolleyballfeaturedData($data, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                            break;
                        default:
//                        Log::warning('Unknown game type encountered', ['game' => $category->game]);
                            break;
                    }
                }

            }
        }

        return response()->json([
            'message' => 'Data fetched successfully',
            'data' => $allFilteredData,
        ], 200);
    }


    private function processBusketballfetureData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        // Ensure `sub_type_2` is initialized as an array
//        if (!isset($allFilteredData['sub_type_2'])) {
//            $allFilteredData['sub_type_2'] = [];
//        }

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

//            Log::info('League ID and Game League ID', ['leagueId' => $leagueId, 'gameLeagueId' => $game->league_id]);

            if ($leagueId != $game->league_id) {
//                Log::info('Skipping league due to ID mismatch');
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                // Process multiple matches if present
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $this->processSingleMatch($match, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                    }
                } elseif (is_object($matches)) {
                    // Process a single match object
                    $this->processSingleMatch($matches, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                }
            }
        }

        // Log final data for debugging
//        Log::info('Final filtered data', ['filteredData' => $allFilteredData['sub_type_2']]);
    }
    private function processFootballfeatureData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        // Ensure `sub_type_2` is initialized as an array
//        if (!isset($allFilteredData['sub_type_2'])) {
//            $allFilteredData['sub_type_2'] = [];
//        }

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

//            Log::info('League ID and Game League ID', ['leagueId' => $leagueId, 'gameLeagueId' => $game->league_id]);

            if ($leagueId != $game->league_id) {
//                Log::info('Skipping league due to ID mismatch');
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                // Process multiple matches if present
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $this->processSingleMatch($match, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                    }
                } elseif (is_object($matches)) {
                    // Process a single match object
                    $this->processSingleMatch($matches, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                }
            }
        }

        // Log final data for debugging
//        Log::info('Final filtered data', ['filteredData' => $allFilteredData['sub_type_2']]);
    }
    private function processvolleyballfeaturedData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        // Ensure `sub_type_2` is initialized as an array
//        if (!isset($allFilteredData['sub_type_2'])) {
//            $allFilteredData['sub_type_2'] = [];
//        }

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

//            Log::info('League ID and Game League ID', ['leagueId' => $leagueId, 'gameLeagueId' => $game->league_id]);

            if ($leagueId != $game->league_id) {
//                Log::info('Skipping league due to ID mismatch');
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                // Process multiple matches if present
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $this->processSingleMatch($match, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                    }
                } elseif (is_object($matches)) {
                    // Process a single match object
                    $this->processSingleMatch($matches, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                }
            }
        }

        // Log final data for debugging
//        Log::info('Final filtered data', ['filteredData' => $allFilteredData['sub_type_2']]);
    }
    private function processhandballfeaturedData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        // Ensure `sub_type_2` is initialized as an array
//        if (!isset($allFilteredData['sub_type_2'])) {
//            $allFilteredData['sub_type_2'] = [];
//        }

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

//            Log::info('League ID and Game League ID', ['leagueId' => $leagueId, 'gameLeagueId' => $game->league_id]);

            if ($leagueId != $game->league_id) {
//                Log::info('Skipping league due to ID mismatch');
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                // Process multiple matches if present
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $this->processSingleMatch($match, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                    }
                } elseif (is_object($matches)) {
                    // Process a single match object
                    $this->processSingleMatch($matches, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                }
            }
        }

        // Log final data for debugging
        //Log::info('Final filtered data', ['filteredData' => $allFilteredData['sub_type_2']]);
    }
    private function processhockeyfeaturedData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        // Ensure `sub_type_2` is initialized as an array
//        if (!isset($allFilteredData['sub_type_2'])) {
//            $allFilteredData['sub_type_2'] = [];
//        }

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

//            Log::info('League ID and Game League ID', ['leagueId' => $leagueId, 'gameLeagueId' => $game->league_id]);

            if ($leagueId != $game->league_id) {
//                Log::info('Skipping league due to ID mismatch');
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                // Process multiple matches if present
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $this->processSingleMatch($match, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                    }
                } elseif (is_object($matches)) {
                    // Process a single match object
                    $this->processSingleMatch($matches, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                }
            }
        }

        // Log final data for debugging
//        Log::info('Final filtered data', ['filteredData' => $allFilteredData['sub_type_2']]);
    }
    private function processtennisfeaturedData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
        // Ensure `sub_type_2` is initialized as an array
//        if (!isset($allFilteredData['sub_type_2'])) {
//            $allFilteredData['sub_type_2'] = [];
//        }

        foreach ($data->scores->category as $league) {
            $leagueId = $league->gid ?? $league->id ?? $league->{'@gid'} ?? $league->{'@id'} ?? null;

//            Log::info('League ID and Game League ID', ['leagueId' => $leagueId, 'gameLeagueId' => $game->league_id]);

            if ($leagueId != $game->league_id) {
//                Log::info('Skipping league due to ID mismatch');
                continue;
            }

            if (isset($league->matches->match)) {
                $matches = $league->matches->match;

                // Process multiple matches if present
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $this->processSingleMatch($match, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                    }
                } elseif (is_object($matches)) {
                    // Process a single match object
                    $this->processSingleMatch($matches, $leagueId, $league, $game, $allFilteredData['sub_type_2'], $addedMatchIds);
                }
            }
        }

        // Log final data for debugging
//        Log::info('Final filtered data', ['filteredData' => $allFilteredData['sub_type_2']]);
    }
    private function processSoccarfeatureData($data, $game, &$allFilteredData, &$addedMatchIds)
    {
//        Log::info('Processing soccer data for game:', ['game' => $game]);
//        if (!isset($allFilteredData['sub_type_2'])) {
//            $allFilteredData['sub_type_2'] = [];
//        }

        foreach ($data->scores->categories as $league) {
            $leagueId = $league->gid ?? $league->id ?? null;
//            Log::info('Processing league:', ['league_id' => $leagueId]);

            // Skip leagues that don't match the game league ID
            if ($leagueId != $game->league_id) {
//                Log::info('Skipping league due to league_id mismatch', ['game_league_id' => $game->league_id, 'league_id' => $leagueId]);
                continue;
            }

            if (isset($league->matches)) {
                $matches = $league->matches;
//                Log::info('Matches found in league:', ['league_id' => $leagueId, 'matches' => $matches]);

                // Process multiple matches
                if (is_array($matches)) {
                    foreach ($matches as $match) {
                        $this->processSingleMatch($match, $leagueId, $league, $game, $allFilteredData, $addedMatchIds);
                    }
                }
                // Process a single match object
                elseif (is_object($matches)) {
                    $this->processSingleMatch($matches, $leagueId, $league, $game, $allFilteredData, $addedMatchIds);
                }
            } else {
//                Log::info('No matches found in league:', ['league_id' => $leagueId]);
            }
        }

//        Log::info('Filtered data after processing:', ['filteredData' => $allFilteredData]);
    }



    private function processSingleMatch($match, $leagueId, $league, $game, &$filteredData, &$addedMatchIds)
    {
        $matchId = $match->id ?? $match->{'@id'} ?? null;

//        Log::info('Match ID and Game Match ID', ['matchId' => $matchId, 'gameMatchId' => $game->match_id]);
//        Log::info('Added Match IDs', ['addedMatchIds' => $addedMatchIds]);

        if ($matchId == $game->match_id && !in_array($matchId, $addedMatchIds)) {
            $addedMatchIds[] = $matchId;

            $filteredData[] = [
                'league_id' => $leagueId,
                'league_name' => $league->name ?? $league->{'@name'} ?? 'Unknown League',
                'category_name' => $game->category_name,
                'match' => $match,
            ];

//            Log::info('Filtered data added', ['matchId' => $matchId, 'filteredData' => $filteredData]);
        }
    }



    public function filteredCricketOdds(Request $request)
    {
        $request_type = $request->get('type');
        $sport_id = 3;
        $fi = $request->get('FI');

        if ($request_type === 'live') {
            return $this->handleLiveOdds($sport_id, $fi);
        } else {

//            return $request;
            return $this->handlePrematchOdds($sport_id, $fi);
        }
    }

    private function handleLiveOdds($sport_id, $fi)
    {
        $type = 'live-details';
        $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type, 'FI' => $fi]);

        return $this->fetchAndFilterOdds($apiUrl, ['Match Winner 2-Way', 'Match Winner 3-Way'], 'live_'.$fi);
    }

    private function handlePrematchOdds($sport_id, $fi)
    {
        $type = 'prematch';
        $apiUrl = route('api.bet.games.data', ['sport_id' => $sport_id, 'type' => $type, 'event' => $fi]);

        return $this->fetchAndFilterOddsPrematch($apiUrl, ["To Win the Match"], 'prematch_'.$fi);
    }

    private function fetchAndFilterOddsPrematch($apiUrl, $filterNames, $cacheKey)
    {
        try {
//            $response = Http::get($apiUrl);
            $response = Http::retry(1, 10000)->get($apiUrl);

            if (!$response->successful()) {
                return response()->json(['error' => 'Failed to fetch data'], 500);
            }

            $data = $response->json();


            if (!isset($data['data']['results']) || !is_array($data['data']['results'])) {
                return response()->json(['error' => 'Invalid data structure'], 500);
            }

            $eventSections = [];


            foreach ($data['data']['results'] as $event) {

                if (isset($event['1st_over']['sp']) && is_array($event['1st_over']['sp'])) {
                    $eventSections[] = $event['1st_over']['sp'];
                }

                if (isset($event['main']['sp']) && is_array($event['main']['sp'])) {
                    $eventSections[] = $event['main']['sp'];
                }


            }


            $allMarkets = [];

            foreach ($eventSections as $section) {
                foreach ($section as $marketKey => $market) {

                    if (!empty($market['odds']) && isset($market['name']) && in_array($market['name'], $filterNames)) {
                        $allMarkets[] = [
                             $market
                        ];
                    }
                }
            }

            return $allMarkets;

        } catch (\Exception $e) {
            Log::error('API request error', ['message' => $e->getMessage()]);
            return response()->json(['error' => 'An error occurred'], 500);
        }
    }


    private function fetchAndFilterOdds($apiUrl, $filterNames, $cacheKey)
    {

        return Cache::remember($cacheKey, now()->addMinutes(1), function () use ($apiUrl, $filterNames) {
            try {

                $response = Http::retry(1, 2000)->get($apiUrl);

                if (!$response->successful()) {
                    return response()->json(['error' => 'Failed to fetch data'], 500);
                }

                $data = $response->json();

                if (!isset($data['data']['results']) || !is_array($data['data']['results']) || empty($data['data']['results'])) {
                    return response()->json(['error' => 'Invalid API response'], 500);
                }

                $results = $data['data']['results'][0] ?? [];

                if (!is_array($results) || empty($results)) {
                    return response()->json(['error' => 'Invalid API response: Empty results set'], 500);
                }

                return $this->filterOddsData($results, $filterNames);

            } catch (\Exception $e) {
                Log::error('API request error', ['message' => $e->getMessage()]);
                return response()->json(['error' => 'An error occurred'], 500);
            }
        });
    }


    private function filterOddsData($results, $filterNames)
    {
        $filteredResults = [];
        $currentRange = [];
        $includeRange = false;

        foreach ($results as $item) {
            if ($item['type'] == 'MG') {
                if ($includeRange) {
                    $filteredResults = array_merge($filteredResults, $currentRange);
                }
                // Start a new MG range
                $currentRange = [$item];
                $includeRange = in_array($item['NA'], $filterNames);
            } elseif (count($currentRange) > 0) {
                $currentRange[] = $item;
            }
        }

        if ($includeRange) {
            $filteredResults = array_merge($filteredResults, $currentRange);
        }

        return response()->json($filteredResults);
    }







}