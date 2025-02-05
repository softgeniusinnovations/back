<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use App\Models\GoalCategory;
use App\Models\GoalImage;
use Illuminate\Support\Arr;
use Carbon\Carbon;
use App\WebSocket\WebSocketClient;
use Illuminate\Support\Facades\Log;

class ExternalApiController extends Controller
{
    public $page = 'sport';
    public function getLeaugeImage($match, $id)
    {
        //make CURL call
        $url = "http://data2.goalserve.com:8084/api/v1/logotips/" . $match . "/teams?k=ef2762546f6a447cc37608dc6b5e7b62&ids=" . $id;
        $client = new Client();
        $response = $client->request('GET', $url);

        $body = $response->getBody();
        $data = json_decode($body, true);
        $paload = [
            "status" => true,
            'data' => $data
        ];
        return $paload;
    }

    // Logo response
    public function logoResponse($name){
        $image = GoalImage::where('team_id', $name)->orWhere('name', $name)->first();
        if($image){
            return $image->image;
        }else{
            return '';
        }
    }



    // Category Response Response
    public function categoryResponse(){
        $cacheKey = 'odds_category_' . md5('category');

        // $updated = GoalCategory::find(13);
        // $updated->image = 'soccer';
        // $updated->save();

        // Cache::forget($cacheKey);
        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);
            return response()->json([
                "status" => 200,
                "data" => $data
            ], 200);
        }else{
            $category = GoalCategory::where('status', 1)->with('leagues')->withCount('leagues')->get();
            if($category){
                Cache::put($cacheKey, $category, 60*60*72);
                return response()->json([
                    "status" => 200,
                    "data" => $category
                ], 200);
            }else{
                return response()->json([
                    "status" => 200,
                    "data" => [],
                    "message" => "No category found"
                ], 200);
            }

        }


    }


    public function categoryData($id, $page='sports'){
        $this->page = $page;
        $removeLiveGamesFromUpcoming = [];
        $category = GoalCategory::select('id', 'name','in_play', 'image', 'league', 'game')->withCount('leagues')->with('leagues', function($q){
            return $q->select("id","category","category_id","sub_cat_id","name");
        })->where('status', 1)->where('id', $id)->first();
        if($category){

            if($category->in_play){
                $removeLiveGamesFromUpcoming = $this->inPlayMatches($category->in_play);
            }else{
                $removeLiveGamesFromUpcoming = [];
            }

            $filteredData =[];

            // Check cached data
            $cacheKey = 'odds_cache_' . md5($category->game);
            if (Cache::has($cacheKey)) {
                $chechedData = Cache::get($cacheKey);

                if($id == 4 || $id == 17){
                    $category = collect(is_array($chechedData->scores->categories) ? $chechedData->scores->categories : [$chechedData->scores->categories]);
                }
                else if($id == 13){
                    $category = collect(is_array($chechedData->scores->match) ? $chechedData->scores->match : [$chechedData->scores->match])->filter(function ($itemData) use($removeLiveGamesFromUpcoming)  {
                        $id = $itemData->id ?? ($itemData->{'@id'} ?? null);
                        $isIdNotInRemoveList = !in_array($id, $removeLiveGamesFromUpcoming);
                        return $isIdNotInRemoveList;
                    });
                }
                else{
                    $category = collect(is_array($chechedData->scores->category) ? $chechedData->scores->category : [$chechedData->scores->category]);
                }

                $filteredData = $category->map(function ($item) use($id, $removeLiveGamesFromUpcoming) {
                    $modifiedItem = (array) $item; // preserve tha all data

                    if($this->page== 'sports'){
                        if($id == 4 || $id == 17 ){
                            $matchData = collect(is_array($item->matches) ? $item->matches : [$item->matches] );
                            $modifiedItem['matches'] = $matchData->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds) ? $match->odds : [$match->odds])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                                })->map(function ($type) {
                                    $type->bookmakers = is_array($type->bookmakers) ? [$type->bookmakers[0]] : [$type->bookmakers];
                                    return $type;
                                });

                                $match->odds = $filteredOdds->values()->all();
                                return $match;

                            })->values()->all();
                        }
                        else if($id == 13){
                            $matchData = collect(is_array($item) ? $item : [$item] );
                            $modifiedItem['odds'] = $matchData->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'} ?? $type->{'@name'} ?? null, ['3Way Result', 'Home/Away', 'Over/Under', 'Total Maps']);
                                })->map(function ($type) {
                                    $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                                    return $type;
                                });


                                return $filteredOdds->values()->all();

                            })->values()->all();
                        }
                        else{
                            $matchData = collect(is_array($item->matches->match) ? $item->matches->match : [$item->matches->match] );

                            $modifiedItem['matches']->match = $matchData->filter(function ($itemData) {
                                $status = $itemData->status ?? ($itemData->{'@status'} ?? null);
                                return $status != "Finished" && $status != "Ended" && $status != "Cancelled" && $status != "Retired" && $status != "Postponed" && $status != "Removed";
                            })->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                                })->map(function ($type) {
                                    $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                                    return $type;
                                });

                                $match->odds->type = $filteredOdds->values()->all();
                                return $match;

                            })->values()->all();

                        }
                    }

                    else if($this->page== 'live'){

                        $today = Carbon::today()->format('d.m.Y');

                        if($id == 4 ){
                            $matchData = collect(is_array($item->matches) ? $item->matches : [$item->matches] );
                            $modifiedItem['matches'] = $matchData->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds) ? $match->odds : [$match->odds])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                                })->map(function ($type) {
                                    $type->bookmakers = is_array($type->bookmakers) ? [$type->bookmakers[0]] : [$type->bookmakers];
                                    return $type;
                                });

                                $match->odds = $filteredOdds->values()->all();
                                return $match;

                            })->values()->all();
                        }
                        else if($id == 13){
                            $matchData = collect(is_array($item) ? $item : [$item] );
                            $modifiedItem['odds'] = $matchData->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'} ?? $type->{'@name'} ?? null, ['3Way Result', 'Home/Away', 'Over/Under', 'Total Maps']);
                                })->map(function ($type) {
                                    $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                                    return $type;
                                });


                                return $filteredOdds->values()->all();

                            })->values()->all();
                        }
                        else if($id == 3){
                            $today = Carbon::today()->format('d.m.Y');
                            $matchData = collect(is_array($item->matches->match) ? $item->matches->match : [$item->matches->match] );
                            $modifiedItem['matches']->match = $matchData->filter(function ($match) use($today) {
                                return $match->matchinfo->info[0]->value != "" && $match->date == $today;
                            })->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                                })->map(function ($type) {
                                    $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                                    return $type;
                                });

                                $match->odds->type = $filteredOdds->values()->all();
                                return $match;

                            })->values()->all();
                        }
                        else{
                            $matchData = collect(is_array($item->matches->match) ? $item->matches->match : [$item->matches->match] );
                            // ->filter(function ($match) {
                            //         $status = $match->status ?? $match->{'@status'};
                            //         return ($status == "started") ??  ($status == "In Progress") ??  ($status == "InPlay") ??  ($status == "In Play") ;
                            //     })
                            $modifiedItem['matches']->match = $matchData->filter(function ($itemData) use($today){
                                $status = $itemData->status ?? ($itemData->{'@status'} ?? null);
                                $date = $itemData->date ?? $itemData->{'@date'};
                                return $status != "Finished" && $status != "Ended" && $status != "Cancelled" && $status != "Retired" && $status != "Postponed" && $status != "Removed" && $date === $today;
                            })->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                                })->map(function ($type) {
                                    $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                                    return $type;
                                });

                                $match->odds->type = $filteredOdds->values()->all();
                                return $match;

                            })->values()->all();
                        }
                    }


                    else{
                        if($id == 4 || $id == 17 ){
                            $matchData = collect(is_array($item->matches) ? $item->matches : [$item->matches] );
                            $modifiedItem['matches'] = $matchData->filter(function ($itemData) use($removeLiveGamesFromUpcoming) {
                                $id = $itemData->id ?? ($itemData->{'@id'} ?? null);
                                $isIdNotInRemoveList = !in_array($id, $removeLiveGamesFromUpcoming);

                                return $isIdNotInRemoveList;
                            })->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds) ? $match->odds : [$match->odds])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                                })->map(function ($type) {
                                    $type->bookmakers = is_array($type->bookmakers) ? [$type->bookmakers[0]] : [$type->bookmakers];
                                    return $type;
                                });

                                $match->odds = $filteredOdds->values()->all();
                                return $match;

                            })->values()->all();
                        }
                        else if($id == 13){
                            $matchData = collect(is_array($item) ? $item : [$item] );


                            $modifiedItem['odds'] = $matchData->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'} ?? $type->{'@name'} ?? null, ['3Way Result', 'Home/Away', 'Over/Under', 'Total Maps']);
                                })->map(function ($type) {
                                    $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                                    return $type;
                                });


                                return $filteredOdds->values()->all();

                            })->values()->all();
                        }
                        else{
                            $matchData = collect(is_array($item->matches->match) ? $item->matches->match : [$item->matches->match] );
                            $modifiedItem['matches']->match = $matchData->filter(function ($itemData) use($removeLiveGamesFromUpcoming) {
                                $status = $itemData->status ?? ($itemData->{'@status'} ?? null);
                                $id = $itemData->id ?? ($itemData->{'@id'} ?? null);

                                $isStatusValid = !in_array($status, ["Finished", "Ended", "Cancelled", "Retired", "Postponed", "Removed"]);

                                $isIdNotInRemoveList = !in_array($id, $removeLiveGamesFromUpcoming);

                                return  $isStatusValid && $isIdNotInRemoveList;
                            })->map(function ($match) {

                                $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                                    return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                                })->map(function ($type) {
                                    $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                                    return $type;
                                });

                                $match->odds->type = $filteredOdds->values()->all();
                                return $match;

                            })->values()->all();
                        }
                    }



                    // Return the modified item with other data intact
                    return (object) $modifiedItem;
                })->values()->all();
                // $filteredData = $chechedData;
            }


            return response()->json([
                "status" => 200,
                // "data" => $response,
                "data" => $filteredData,
            ], 200);
        }else{
            return response()->json([
                "status" => 200,
                "data" => [],
            ], 200);
        }
    }


















    // Game response
    public function gameResponse($id, $league, $page="sports"){
//        Log::info("id");
//        Log::info($id);
//        Log::info("league");
//        Log::info($league);
//        Log::info("page");
//        Log::info($page);
        $category = GoalCategory::where('status', 1)->where('id', $id)->first();

        $valuesToIgnore = ["Home Team Total Goals (Including OT)", "Away Team Total Goals (Including OT)", "Asian Handicap", "Correct Score", "Correct Score 1st Half", "Correct Score 2nd Half", "Home Team Total Goals(1st Half)","Total Hits","Player Total Bases","Player Shots","Player Rebounds","Player Doubles","Player Home Runs","Player Triples","Player Stolen Bases" ];
        $search_priority = ["Bet365", "188Bet", "Betsson", "1xBet", "Bwin", "WilliamHill", "10Bet", "Betfair", "Betway", "Unibet", "Dafabet", "Pinnacle", "Betfred", "Ladbrokes", "Expect", "Marathon", "Sbobet", "Tipico", "Sportingbet", "Betano"];

        if($category){
            $cacheKey = 'odds_cache_' . md5($category->game);
            if (Cache::has($cacheKey)) {
                $data = Cache::get($cacheKey);
                if($id == 4 || $id == 17) { // soccer_10
                    $response = collect(is_array($data->scores->categories) ? $data->scores->categories : [$data->scores->categories])
                        ->filter(function ($item) use ($league) {
                            $id = $item->id ?? $item->{'@id'} ?? null;
                            return $id == $league;
                        })
                        ->map(function ($category) use ($search_priority) {

                            $category->matches = collect($category->matches)->map(function ($match) use ($search_priority) {

                                $match->odds = collect($match->odds)->map(function ($odds) use ($search_priority) {

                                    $filteredBookmaker = collect($odds->bookmakers)
                                        ->first(function ($bookmaker) use ($search_priority) {
                                            return in_array($bookmaker->name, $search_priority);
                                        });


                                    $odds->bookmakers = $filteredBookmaker ? [$filteredBookmaker] : $odds->bookmakers;

                                    return $odds;
                                })->values();

                                return $match;
                            })->values();

                            return $category;
                        })

                        ->values();
                }
                else if($id == 3 && $page == 'sports'){
                    $cacheKey = "odds_cache_cricket" . md5($category->game);
                    $data = Cache::get($cacheKey);
                    $response = collect(is_array($data->fixtures->category) ? $data->fixtures->category : [$data->fixtures->category])
                        ->filter(function ($item) use ($league) {
                            $id = $item->id ?? $item->{'@id'} ?? null;
                            return $id == $league;
                        })
                        ->values();
                }
                else if($id == 13){

                    $response = collect(is_array($data->scores->match) ? $data->scores->match : [$data->scores->match])
                        ->filter(function ($item) use ($league) {
                            $id = $item->league_id ?? $item->{'@league_id'} ?? null;
                            return $id == $league;
                        })
                        ->map(function ($item) use ($search_priority) {
                            if (!isset($item->odds->type) || !is_array($item->odds->type)) {
                                return $item; // Skip if no valid odds->type array
                            }

                            // Process each type in the odds->type array
                            $item->odds->type = collect($item->odds->type)->map(function ($type) use ($search_priority) {
                                if (!isset($type->bookmaker) || !is_array($type->bookmaker)) {
                                    return $type; // Skip if no valid bookmaker array
                                }

                                // Filter the bookmaker array based on search_priority
                                $filteredBookmaker = collect($type->bookmaker)->first(function ($bookmaker) use ($search_priority) {
                                    return in_array($bookmaker->{'@name'} ?? null, $search_priority); // Use -> instead of []
                                });

                                // Update the bookmaker field with the filtered result
                                $type->bookmaker = $filteredBookmaker ? [$filteredBookmaker] : [];
                                return $type;
                            })->toArray();

                            return $item;
                        })
                        ->values();
                }
                else{
                    if ($id == 1 ||$id==10){
                        $response = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category])
                            ->filter(function ($item) use ($league) {
                                $id = $item->id ?? $item->{'@id'} ?? null;
                                return $id == $league;
                            })

                            ->map(function ($item) use ($valuesToIgnore, $search_priority) {
                                if (isset($item->matches->match) && is_array($item->matches->match)) {
                                    $item->matches->match = collect($item->matches->match)
                                        ->map(function ($match) use ($valuesToIgnore, $search_priority) {
                                            if (isset($match->odds->type) && is_array($match->odds->type)) {
                                                // Filter out odds types based on valuesToIgnore
                                                $filteredOdds = collect($match->odds->type)
                                                    ->reject(function ($type) use ($valuesToIgnore) {

                                                        return isset($type->value) && in_array($type->value, $valuesToIgnore);
                                                    })
                                                    ->map(function ($type) use ($search_priority) {

                                                        $firstMatch = collect($type->bookmaker ?? [])
                                                            ->first(function ($bookmaker) use ($search_priority) {
                                                                return isset($bookmaker->name) && in_array($bookmaker->name, $search_priority);
                                                            });


                                                        $type->bookmaker = $firstMatch ? [$firstMatch] : [];
                                                        return $type;
                                                    })
                                                    ->filter(function ($type) {

                                                        return !empty($type->bookmaker);
                                                    });


                                                $match->odds->type = $filteredOdds->values()->all();
                                            }
                                            return $match;
                                        })
                                        ->values()
                                        ->all();
                                }
                                return $item;
                            })
                            ->values();

                    }
                    else{
                        // $response = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category])
                        //         ->filter(function ($item) use ($league) {
                        //             $id = $item->id ?? $item->{'@id'} ?? null;
                        //             return $id == $league;
                        //         })
                        //         ->values();

                        $response = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category])
                            ->filter(function ($item) use ($league) {
                                $id = $item->id ?? $item->{'@id'} ?? null;
                                return $id == $league;
                            })
                            ->map(function ($item) use ($valuesToIgnore, $search_priority) {
                                // Check if matches and match structure exists
                                if (isset($item->matches->match) && is_array($item->matches->match)) {
                                    // Process each match item
                                    $item->matches->match = collect($item->matches->match)
                                        ->map(function ($match) use ($valuesToIgnore, $search_priority) {
                                            if (isset($match->odds->type) && is_array($match->odds->type)) {
                                                // Process each type item
                                                $match->odds->type = collect($match->odds->type)
                                                    ->filter(function ($type) use ($valuesToIgnore) {
                                                        $typeValue = $type->value ?? $type->{'@value'} ?? null;
                                                        return isset($typeValue) && !in_array($typeValue, $valuesToIgnore);
                                                    })
                                                    ->map(function ($type) use ($search_priority) {
                                                        // Filter bookmakers within each type based on search_priority
                                                        if (isset($type->bookmaker) && is_array($type->bookmaker)) {
                                                            $filteredBookmaker = collect($search_priority)
                                                                ->map(function ($priorityName) use ($type) {
                                                                    return collect($type->bookmaker)
                                                                        ->firstWhere('@name', $priorityName);
                                                                })
                                                                ->filter()
                                                                ->first();

                                                            $type->bookmaker = $filteredBookmaker ? [$filteredBookmaker] : $type->bookmaker;
                                                        }
                                                        return $type;
                                                    })
                                                    ->values()
                                                    ->all();
                                            }
                                            return $match;
                                        })
                                        ->values()
                                        ->all();
                                }
                                return $item;
                            })
                            ->values();

                    }

                }

                return response()->json([
                    "status" => 200,
                    "data" => $response,
                ], 200);
            }else{
                return response()->json([
                    "status" => 200,
                    "data" => []
                ], 200);
            }
        }else{
            return response()->json([
                "status" => 200,
                "data" => [],
                "message" => "No data found"
            ], 200);
        }

    }



    // WS to data fetch
    public $apiAccessToken = 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1biI6InRyYW1iZXQiLCJuYmYiOjE3MjA2MzI2NzYsImV4cCI6MTcyMDYzNjI3NiwiaWF0IjoxNzIwNjMyNjc2fQ.ExFu3fcqLLMr3sdAvt1lgVSBxpCDbfHaJJo5YNagqPI';
    public $sportType = 'basket';

    public function getWSData($name){
        $this->sportType = $name;
        $cacheKey = 'websocket_data' . $this->sportType;
        $cachedData = Cache::get($cacheKey);
        if ($cachedData) {
            $jsonData = json_encode($cachedData);
            return response()->json([
                "status" => 200,
                "data" => json_decode(stripslashes($cachedData), true),
                "message" => "No data found"
            ], 200);
        }else{
            return response()->json(['error' => "no data found"], 404);
        }

    }


    // Live game | In-play data
    public function fetchData($url) {
        $client = new Client();
        try {
            $response = $client->get($url);
            $responseBody = $response->getBody()->getContents();
            return json_decode($responseBody,true);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);
        }
    }

    // Live Games response
    public function inPlayGames($in){

        switch($in){
            case 'baseball' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-baseball.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            case 'basket' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-basket.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            case 'soccer' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-soccer.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            case 'tennis' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-tennis.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            case 'hockey' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-hockey.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            case 'volleyball' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-volleyball.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            case 'amfootball' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-amfootball.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            case 'esports' :{
                $cacheKey = 'inplay_cache_' . md5($in);
                if(Cache::has($cacheKey)){
                    $data = Cache::get($cacheKey);
                    return response()->json([
                        "status" => 200,
                        "data" => $data,
                        "message" => "Response data"
                    ], 200);
                }else{
                    $url = "http://inplay.goalserve.com/inplay-esports.gz";
                    $data =  $this->fetchData($url);
                    $modifiedData = $this->inPlayCategoryData($data);
                    Cache::put($cacheKey, $modifiedData, 8 );
                    Cache::put($cacheKey.'_detail', $data, 8 );
                    return response()->json([
                        "status" => 200,
                        "data" => $modifiedData,
                        "message" => "Response data"
                    ], 200);
                }

            }
            default: {
                $cacheKey = 'inplay_cache_' . md5($in);
                $url = "http://inplay.goalserve.com/inplay-".$in.".gz";
                $data =  $this->fetchData($url);
                $modifiedData = $this->inPlayCategoryData($data);
                Cache::put($cacheKey, $modifiedData, 8 );
                Cache::put($cacheKey.'_detail', $data, 8 );
                return response()->json([
                    "status" => 200,
                    "data" => $modifiedData,
                    "message" => "Response data"
                ], 200);
            }
        }

    }

    // In play matches
    public function inPlayMatches($in){
        $response =  $this->inPlayGames($in);
        $responseArray = json_decode($response->getContent(), true);
        if (isset($responseArray['status']) && $responseArray['status'] == 200) {
            if (isset($responseArray['data']['events'])) {
                return array_keys($responseArray['data']['events']);

                // return response()->json([
                //     'status' => 200,
                //     'data' => array_keys($responseArray['data']['events']),
                //     'message'=> 'Response data'
                // ]);
            } else {
                return [];
                //  return response()->json([
                //     'status' => 200,
                //     'data' => [],
                //     'message'=> 'Response data'
                // ]);
            }
        } else {
            return [];
            // return response()->json([
            //        'status' => 200,
            //        'data' => [],
            //        'message'=> 'Response data'
            //    ]);
        }
    }

    // In Play game details
    public function inPlayGameDetails($cat, $match)
    {
        $cacheKey = 'inplay_cache_' . md5($cat) . '_detail';

        // Check if cached data exists
        if (Cache::has($cacheKey)) {
            $data = Cache::get($cacheKey);

            // Check if the match index exists in the cached data
            if (isset($data['events'][$match])) {
                $event = $data['events'][$match];
                return response()->json([
                    "status" => 200,
                    "data" => $event,
                    "message" => "Response data"
                ], 200);
            } else {
                return response()->json([
                    "status" => 200,
                    "data" => [],
                    "message" => "Match not found in cached data"
                ], 200);
            }
        }

        // Fetch new data if not cached
        $url = "http://inplay.goalserve.com/inplay-{$cat}.gz";
        $data = $this->fetchData($url);

        // Cache the fetched data
        Cache::put($cacheKey, $data, 7);

        // Check if the match index exists in the new data
        if (isset($data['events'][$match])) {
            $event = $data['events'][$match];
//            Log::info("event",$event);

            return response()->json([
                "status" => 200,
                "data" => $event,
                "message" => "Response data"
            ], 200);
        } else {
            return response()->json([
                "status" => 200,
                "data" => [],
                "message" => "Match not found in new data"
            ], 200);
        }
    }


    // Get order Index
    public function getOrderIndex($name, $order) {
        $index = array_search($name, $order);
        return $index !== false ? $index : PHP_INT_MAX;
    }

    // In play games by category
//    public function inPlayCategoryData($data){
//        $data = $data;
//        foreach ($data['events'] as $eventId => &$eventData) {
//            unset($eventData['stats']);
//            unset($eventData['extra']);
//
//            $filteredOdds = [
//                'over_under' => null,
//                'home_draw_away' => null,
//                'home_away'=>null
//            ];
//
//
//            if(isset($eventData['odds'])){
//                foreach ($eventData['odds'] as $oddsId => $oddsData) {
//                    if (isset($oddsData['participants']) && is_array($oddsData['participants'])) {
//                        $participantNames = array_column($oddsData['participants'], 'name');
//                    }
//
//                    if (in_array('Over', $participantNames) && in_array('Under', $participantNames)) {
//                        $overUnderParticipants = [];
//                        if(isset($oddsData['participants'])){
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
//                        usort($overUnderParticipants, function($a, $b) use ($order) {
//                            return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//                        });
//                        $oddsData['participants'] = $overUnderParticipants;
//                        $filteredOdds['over_under'] = $oddsData;
//                    }
//                    if (in_array('Home', $participantNames) && in_array('Draw', $participantNames) && in_array('Away', $participantNames)) {
//                        $homeAwayParticipants = [];
//                        if(isset($oddsData['participants'])){
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
//                        usort($homeAwayParticipants, function($a, $b) use ($order) {
//                            return $this->getOrderIndex($a['name'], $order) - $this->getOrderIndex($b['name'], $order);
//                        });
//                        $oddsData['participants'] = $homeAwayParticipants;
//
//                        $filteredOdds['home_draw_away'] = $oddsData;
//                        $filteredOdds['home_away'] = null;
//                    } elseif (in_array('Home', $participantNames) && in_array('Away', $participantNames)) {
//                        if (!$filteredOdds['home_draw_away']) {
//                            $homeAwayParticipants = [];
//                            if(isset($oddsData['participants'])){
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
//                            usort($homeAwayParticipants, function($a, $b) use ($order) {
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
























    public function checkResponse(Request $request)
    {
        // Extract query parameters from the request
        $category = $request->input('cat');
        $league = $request->input('league');
        $ts = $request->input('ts');

//        Log::info("category");
//        Log::info($category);
//        Log::info("league");
//        Log::info($league);

        if ($category) {
            // Generate a unique cache key based on the category and league
            $cacheKey = 'odds_cache_' . md5($category . '_' . $league);

            // Check if the response is cached
            if (Cache::has($cacheKey)) {
                // Return the cached response
                return response()->json(json_decode(Cache::get($cacheKey), true));
            }else{
                // sleep(10);

                // URL for the third-party API
                $url = "https://www.goalserve.com/getfeed/89b86665dc8348f5605008dc3da97a57/getodds/soccer?cat={$category}&league={$league}&json=1";
                // $url = "https://www.goalserve.com/getfeed/89b86665dc8348f5605008dc3da97a57/getodds/soccer?cat={$category}&json=1";

                // Create a Guzzle client
                $client = new Client();

                try {
                    // Make the request to the third-party API
                    $response = $client->get($url);
                    $responseBody = $response->getBody()->getContents();
//                    Log::info("responseBody");

//                    Log::info($responseBody);

                    // Cache the response content for 32 seconds
                    Cache::put($cacheKey, $responseBody, 120);

                    // Return the response as JSON
                    return response()->json(json_decode($responseBody, true));
                } catch (\Exception $e) {
                    // Handle potential errors
                    return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);
                }
            }

        } else {
            // Return an error response if category or league is not provided
            return response()->json(['error' => 'No category and league found'], 400);
        }
    }


    public function getResponse() {
        $url = "http://www.goalserve.com/getfeed/89b86665dc8348f5605008dc3da97a57/getodds/soccer?cat=soccer_10?json=1";

        $client = new Client();

        try {
            $response = $client->get($url);
            $responseBody = $response->getBody()->getContents();
//            Log::info("getResponse");
//            Log::info($responseBody);


            return response()->json(json_decode($responseBody, true));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);
        }
    }







}
