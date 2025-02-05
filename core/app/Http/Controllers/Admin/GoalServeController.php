<?php

namespace App\Http\Controllers\Admin;

use App\{
    Models\GoalCategory,
    Models\GoalSubCategory,
    Models\GoalImage
};
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

use App\Http\Controllers\Controller;

class GoalServeController extends Controller
{
    public $host = 'https://www.goalserve.com/getfeed/89b86665dc8348f5605008dc3da97a57/';
    public $session = '2024';
    
    
    public function categoryList(){
        $pageTitle = 'Categories';
        $categories = GoalCategory::where('status', 1)->withCount('leagues')->paginate(25);
       
        return view('admin.goalserve.categories', compact('pageTitle', 'categories'));
    }
    public function subCategoryList($id){
        $pageTitle = 'Sub Categories';
        $categories = GoalSubCategory::where('status', 1)->where('category_id', $id)->paginate(30);
       
        return view('admin.goalserve.sub-categories', compact('pageTitle', 'categories'));
    }
    
    // Data Response Function 
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
    
    public function daa(){
        // $url = "http://inplay.goalserve.com/dictionaries/odds-markets/soccer";
        $url = "http://inplay.goalserve.com/inplay-soccer.gz";
        $data =  $this->fetchData($url);
        
        foreach ($data['events'] as $eventId => &$eventData) {
            unset($eventData['stats']);
            unset($eventData['extra']);
            
                 $filteredOdds = [
                    'over_under' => null,
                    'home_draw_away' => null,
                    'home_away'=>null
                ];

            
              if(isset($eventData['odds'])){
                  foreach ($eventData['odds'] as $oddsId => $oddsData) {
                      if (isset($oddsData['participants']) && is_array($oddsData['participants'])) {
                            $participantNames = array_column($oddsData['participants'], 'name');
                        }
        
                    if (in_array('Over', $participantNames) && in_array('Under', $participantNames)) {
                        $overUnderParticipants = [];
                        foreach ($oddsData['participants'] as $participant) {
                            if ($participant['name'] === 'Over' || $participant['name'] === 'Under') {
                                $overUnderParticipants[] = $participant;
                                if (count($overUnderParticipants) >= 2) {
                                    break;
                                }
                            }
                        }
                        $oddsData['participants'] = $overUnderParticipants;
                        $filteredOdds['over_under'] = $oddsData;
                    }
                     if (in_array('Home', $participantNames) && in_array('Draw', $participantNames) && in_array('Away', $participantNames)) {
                        $filteredOdds['home_draw_away'] = $oddsData;
                        $filteredOdds['home_away'] = null;
                    } elseif (in_array('Home', $participantNames) && in_array('Away', $participantNames)) {
                        if (!$filteredOdds['home_draw_away']) {
                            $filteredOdds['home_away'] = $oddsData;
                        }
                    }
              }
            }
            unset($eventData['odds']);
            $eventData['filtered_odds'] = $filteredOdds;
        }
        
    
        return response()->json($data);
    }
    
    // Replace key

    // Sub category import 
    public function subCategoryImport(){
        $category = GoalCategory::where('is_static', 0)->where('status', 1)->get();
        if($category->count() > 0){
            foreach($category as $cat){
                $response = $this->leagueAdd($cat->league, $cat->id);
                if($response == 'COMPLETED'){
                    echo $cat->name .' Data import completed';
                }else{
                    echo $cat->name .' Data import incompleted';
                }
            }
            $notify[] = ['success', 'Completed'];
            return back()->withNotify($notify);
        }
        $notify[] = ['error', 'No data found'];
        return back()->withNotify($notify);
    }
    
    // Check sub category exist or not
     public function subCategoryExistOrNot($category, $cat, $name, $id, $session, $country, $object){
        $sub = GoalSubCategory::where('category_id', $cat)->where('sub_cat_id', $id)->where('name', $name)->first();
        
        if(!$sub){
            $subCat = new GoalSubCategory;
            $subCat->category = $category;
            $subCat->name = $name;
            $subCat->category_id = $cat;
            $subCat->sub_cat_id = $id;
            $subCat->session = $session;
            $subCat->country = $country;
            $subCat->object = json_encode($object);
            $subCat->save();
        }
    }
    
    // League / Sub category add by category name
    public function leagueAdd($category, $cat){
        if ($category) {
            if($category == 'cricket'){
                $url = $this->host ."/cricketfixtures/tours/tours?json=1"."&$this->session";
                $responseData = $this->fetchData($url);
                if (isset($responseData->fixtures->category) && is_array($responseData->fixtures->category)) {
                    foreach ($responseData->fixtures->category as $league) {
                        $name = isset($league->{'@name'}) ? $league->{'@name'} : (isset($league->name) ? $league->name : 'N/A');
                        $id = isset($league->{'@id'}) ? $league->{'@id'} : (isset($league->id) ? $league->id : 'N/A');
                        $session = isset($league->{'@session'}) ? $league->{'@session'} : (isset($league->session) ? $league->session : 'N/A');
                        $country = isset($league->{'@country'}) ? $league->{'@country'} : (isset($league->country) ? $league->country : 'N/A');
                        $this->subCategoryExistOrNot($category, $cat, $name, $id, $session, $country, $league);
                    }
                }
                
                return 'COMPLETED';
            }else{
                $url = $this->host . $category . "/leagues?json=1"."&$this->session";
                $responseData = $this->fetchData($url);
                if (isset($responseData->leagues->league) && is_array($responseData->leagues->league)) {
                    foreach ($responseData->leagues->league as $league) {
                        $name = isset($league->{'@name'}) ? $league->{'@name'} : (isset($league->name) ? $league->name : 'N/A');
                        $id = isset($league->{'@id'}) ? $league->{'@id'} : (isset($league->id) ? $league->id : 'N/A');
                        $session = isset($league->{'@session'}) ? $league->{'@session'} : (isset($league->session) ? $league->session : 'N/A');
                        $country = isset($league->{'@country'}) ? $league->{'@country'} : (isset($league->country) ? $league->country : 'N/A');
                        $this->subCategoryExistOrNot($category, $cat, $name, $id, $session, $country, $league);
                    }
                }
                if(isset($responseData->categories->category) && is_array($responseData->categories->category)) {
                    foreach ($responseData->categories->category as $league) {
                        $name = isset($league->{'@name'}) ? $league->{'@name'} : (isset($league->name) ? $league->name : 'N/A');
                        $id = isset($league->{'@id'}) ? $league->{'@id'} : (isset($league->id) ? $league->id : 'N/A');
                        $session = isset($league->{'@session'}) ? $league->{'@session'} : (isset($league->session) ? $league->session : 'N/A');
                        $country = isset($league->{'@country'}) ? $league->{'@country'} : (isset($league->country) ? $league->country : 'N/A');
                        $this->subCategoryExistOrNot($category, $cat, $name, $id, $session, $country, $league);
                    }
                }
                return 'COMPLETED';
            }
            
        } else {
            return response()->json(['error' => 'No category and league found'], 400);
        }
    }
    
    // Game Fetch Instance 
    public function gameFetchData($url) {
        $client = new Client();
        try {
            $response = $client->get($url);
            $responseBody = $response->getBody()->getContents();
            $responseBody = preg_replace('/^\x{FEFF}/u', '', $responseBody);
            return json_decode($responseBody);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);
        }
    }
    
    // Game soccer_10 fetch Instance
    public function gameSoccerFetchData($url){
         $client = new Client();
         try {
             $client = new \GuzzleHttp\Client([
                'headers' => [
                    'Accept-Encoding' => 'gzip'
                ]
             ]);
             $response = $client->request('GET', $url);
    
             $data = $response->getBody()->getContents();
             $data = preg_replace('/^\x{FEFF}/u', '', $data);
             $data = json_decode($data); 
             return $data;
                 
         } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);
        }
    }
    
    // Image fetch Instance
    public function imageFetchInstance($url){
        $client = new Client();
        try {
             $response = $client->request('GET', $url);
             $data = $response->getBody()->getContents();
             $data = json_decode($data);
             return $data;
            
        } catch (\Exception $e) {
                return response()->json(['error' => 'Failed to fetch data from API', 'message' => $e->getMessage()], 500);
            }
        }
    
    // Game Ipmort
    public function gameImport($id){
        $category = GoalCategory::where('status', 1)->where('id', $id)->first();
        if($category){
            $cacheKey = 'odds_cache_' . md5($category->game);
            $url = $this->host . "getodds/soccer?cat=".$category->game."&json=1";
            
            if($category->game == 'baseball_10' || $category->game == 'basket_10'  || $category->game == 'cricket_10'|| $category->game == 'tennis_10' || $category->game == 'hockey_10' || $category->game == 'handball_10' || $category->game == 'volleyball_10' || $category->game == 'football_10' 
            || $category->game == 'rugby_10' || $category->game == 'rugbyleague_10' || $category->game == 'darts_10'){
                $data = $this->gameFetchData($url);
                $this->matchedActiveCategoryData($category->id);
                 if (!empty($data) && isset($data->scores)) {
                    Cache::put($cacheKey, $data, 60*60*24);
                    
                    if( $category->game == 'baseball_10' || $category->game == 'basket_10' || $category->game == 'tennis_10'  || $category->game == 'hockey_10' || $category->game == 'handball_10'
                    || $category->game == 'volleyball_10' || $category->game == 'football_10' || $category->game == 'rugby_10' || $category->game == 'rugbyleague_10'  || $category->game == 'darts_10' ){
                        $categories = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category]);
                        $this->matchedActiveCategoryData($category->id, $categories);
                    }
                }
            }
            
             if($category->game == 'cricket_10'){  // This called for old sport card page data 
                $data = $this->gameFetchData($url);
                if (!empty($data) && isset($data->scores)) {
                    Cache::put($cacheKey, $data, 60*60*24);
                }
                
                // Data cache for schedule
                $cacheKey = 'odds_cache_cricket' . md5($category->game);
                $url = $this->host . "/cricket/schedule1?json=1";
                $data = $this->gameFetchData($url);
                if (!empty($data) && isset($data->fixtures)) {
                    Cache::put($cacheKey, $data, 60*60*24);
                     $categories = collect(is_array($data->fixtures->category) ? $data->fixtures->category : [$data->fixtures->category]);
                     $this->matchedActiveCategoryData($category->id, $categories);
                }
            }
            
            if( $category->game == 'soccer_10'){
                $currentDate = \Carbon\Carbon::now()->format('d.m.Y');
                $url = $url."&date_start=".$currentDate."&date_end=".$currentDate;
                $data = $this->gameSoccerFetchData($url);
                if(!empty($data) && isset($data->scores)){
                    Cache::put($cacheKey, $data, 60*60*24);
                    
                    $categories = collect(is_array($data->scores->categories) ? $data->scores->categories : [$data->scores->categories]);
                    $this->matchedActiveCategoryData($category->id, $categories);
                }
            }
            if( $category->game == 'esports_10'){
                $data = $this->gameFetchData($url);
                if(!empty($data) && isset($data->scores))
                {
                    Cache::put($cacheKey, $data, 60*60*24);
                    GoalSubCategory::where('category_id', $category->id)->update(['status' => 0]);
                    $categories = collect(is_array($data->scores->match) ? $data->scores->match : [$data->scores->match]);
                    $this->matchedActiveCategoryData($category->id, $categories);
                    $output = $categories->map(function($item) use($category) {
                        $exist = GoalSubCategory::where('category_id', $category->id)->where('sub_cat_id',  $item->{'@league_id'})->first();
                        if($exist){
                            $exist->name = $item->{'@league'};
                            $exist->status = 1;
                            $exist->save();
                        }else{
                            $sub = new GoalSubCategory;
                            $sub->category = 'esports';
                            $sub->category_id = $category->id;
                            $sub->sub_cat_id = $item->{'@league_id'};
                            $sub->name = $item->{'@league'};
                            $sub->save();
                        }
                    });
                }
            }
            
            if($category->game == 'mma_10'){
                $data = $this->gameFetchData($url);
                if(!empty($data) && isset($data->scores))
                {
                    Cache::put($cacheKey, $data, 60*60*24);
                    GoalSubCategory::where('category_id', $category->id)->update(['status' => 0]);
                    $categories = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category]);
                    $this->matchedActiveCategoryData($category->id, $categories);
                    $output = $categories->map(function($item) use($category) {
                        $exist = GoalSubCategory::where('category_id', $category->id)->where('sub_cat_id',  $item->{'@gid'})->first();
                        if($exist){
                            $exist->name = $item->{'@name'};
                            $exist->status = 1;
                            $exist->save();
                        }else{
                            $sub = new GoalSubCategory;
                            $sub->category = 'mma';
                            $sub->category_id = $category->id;
                            $sub->sub_cat_id = $item->{'@gid'};
                            $sub->name = $item->{'@name'};
                            $sub->save();
                        }
                    });
                }
                
            }
            
            if( $category->game == 'golf_10'){
                $data = $this->gameFetchData($url);
                if(!empty($data) && isset($data->scores))
                {
                    Cache::put($cacheKey, $data, 60*60*24);
                    GoalSubCategory::where('category_id', $category->id)->update(['status' => 0]);
                    $categories = collect(is_array($data->scores->categories) ? $data->scores->categories : [$data->scores->categories]);
                    $output = $categories->map(function($item) use($category) {
                        $exist = GoalSubCategory::where('category_id', $category->id)->where('sub_cat_id',  $item->{'id'})->first();
                        if($exist){
                            $exist->name = $item->{'name'};
                            $exist->status = 1;
                            $exist->save();
                        }else{
                            $sub = new GoalSubCategory;
                            $sub->category = 'golf';
                            $sub->category_id = $category->id;
                            $sub->sub_cat_id = $item->{'id'};
                            $sub->name = $item->{'name'};
                            $sub->save();
                        }
                    });
                }
            }
            
        }else{
            $notify[] = ['error', 'Inactive category'];
            return back()->withNotify($notify);
        }
        $category->last_cron = \Carbon\Carbon::now();
        $category->save();
        $notify[] = ['success', 'Completed'];
        return back()->withNotify($notify);
    }
    
    
    // Fetch data and not matched category inactive
    public function matchedActiveCategoryData($category, $categories =null){
        GoalSubCategory::where('category_id', $category)->update(['status' => 0]);
        if($categories){
            $output = $categories->map(function($item) use($category) {
                $exist = GoalSubCategory::where('category_id', $category)->where('sub_cat_id',  $item->id ?? $item->{'@id'} ?? $item->{'@league_id'} ?? $item->{'@gid'} ??  null)->first();
                if($exist){
                    $exist->status = 1;
                    $exist->save();
                }
            });
        }
    }
    
    
    
    
    
    // Logo Import
    public function logoUpdate($id){
        $leagueIds = '';
        $category = GoalCategory::with('leagues')->find($id);
        if($category->image != ''){
            if ($category && $category->leagues) {
                $leagueIds = $category->leagues->pluck('sub_cat_id')->implode(',');
                  if($leagueIds){
                        $url = "https://app.trambetbd.com/image-response.php?league=".$category->image."&url=".$leagueIds."&type=leagues";
                            $data = $this->imageFetchInstance($url);
                            if(!empty($data) && count($data) > 0){
                                foreach($data as $item){
                                    $sub = GoalSubCategory::where('category_id', $category->id)->where('sub_cat_id', $item->id)->first();
                                    if($sub){
                                        $sub->image = $item->base64;
                                        $sub->save();
                                    }
                                }
                            }
                  }
            }
        }else{
            $notify[] = ['error', 'Server not response for this category'];
            return back()->withNotify($notify);
        }
        
    }
    
    // team Image Import
    public function teamImageImport($id, $onlyTean = false){
        $category = GoalCategory::find($id);
        $leagueIds = '';
        $cacheKey = 'odds_cache_' . md5($category->game);
        $teams = [];
        
        if(!Cache::has($cacheKey)){
            $notify[] = ['error', 'No games found. Please import game first'];
            return back()->withNotify($notify);
        }
        
        $data = Cache::get($cacheKey);
  
        if($id == 13){
        	$result = [];
            $cacheData = collect(is_array($data->scores->match) ? $data->scores->match : [$data->scores->match]);
            $filteredData = $cacheData->map(function($item) use($id) {
            	unset($item->odds);
            	return $item;
            })->values()->all();
            
            $leagueData = collect($filteredData)->mapWithKeys(function ($item) use ($id) {
				    $result = [];

				    // Extract away team information
				    $away = $item->awayteam->id ?? $item->awayteam->{'@id'} ;
				    $awayTeamName = $item->awayteam->name ?? $item->awayteam->{'@name'} ;

				    if ($away) {
				        $result[$away] = $awayTeamName;
				    }

				    // Extract local team information
				    $local = $item->localteam->id ?? $item->localteam->{'@id'} ;
				    $localTeamName = $item->localteam->name ?? $item->localteam->{'@name'} ;

				    if ($local) {
				        $result[$local] = $localTeamName;
				    }

				    return $result;
				});

	            $output = [$leagueData];
	            foreach ($output as $subArray) {
	                    foreach ($subArray as $key => $value) {
	                        $teams[$key] = $value;
	                    }
	                }
	                $teamIds = array_keys($teams);
	                $leagueIds = implode(',', $teamIds);

        }
        else if($id == 17){
            $notify[] = ['error', 'Image not response for this category'];
            return back()->withNotify($notify);
        }
        else if($id == 4) { // soccer_10
            $cacheData = collect(is_array($data->scores->categories) ? $data->scores->categories : [$data->scores->categories]);
            $filteredData = $cacheData->map(function($item) use($id) {
                $modifiedItem = (array) $item;
                $matchData = collect(is_array($item->matches) ? $item->matches : [$item->matches]);
                $modifiedItem['matches'] = $matchData->map(function($match){
                    unset($match->odds);
                    return $match;
                })->values()->all();
                return (object) $modifiedItem;
            })->values()->all();
            $leagueData =  collect($filteredData)->map(function ($item) use($id) {
                                return collect($item->matches)->mapWithKeys(function ($match) use($id) {
                                    $away = $match->visitorteam->id ?  $match->visitorteam->id : $match->visitorteam->{'@id'} ;
                                    $local = $match->localteam->id ?? $match->localteam->{'@id'} ;
                                    $awayteamId = isset($away) ? $away : null;
                                    $localteamId = isset($local) ? $local : null;

                                    $localTeamName = $match->localteam->name ?? $match->localteam->{'@name'};
                        			$awayTeam = $match->visitorteam->name ?? $match->visitorteam->{'@name'};
                        
                                    // return array_filter([$awayteamId, $localteamId]); // Remove null values

                                    $result = [];
			                        if (isset($away)) {
			                            $result[$away] = $awayTeam;
			                        }
			                        if (isset($local)) {
			                            $result[$local] = $localTeamName;
			                        }
			                    
			                        return $result;

                                });
                            })->toArray();
            	foreach ($leagueData as $subArray) {
                    foreach ($subArray as $key => $value) {
                        $teams[$key] = $value;
                    }
                }

                $teamIds = array_keys($teams);
                $leagueIds = implode(',', $teamIds);
                            
        }
        else if($id == 5){

        	$notify[] = ['error', 'Image not found for this category'];
            return back()->withNotify($notify);

        	$cacheData = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category]);
            $filteredData = $cacheData->map(function($item) use($id) {
                $modifiedItem = (array) $item;
                $matchData = collect(is_array($item->matches) ? $item->matches : [$item->matches]);
                $modifiedItem['matches'] = $matchData->map(function($match){
                	$matchMap = collect(is_array($match->match) ? $match->match : [$match->match]);
                    $ma['match'] = $matchMap->map(function($m){
                    	unset($m->odds);
                    	return $m;
                    })->values()->all();
                    return (object) $ma;
                })->values()->all();
                return (object) $modifiedItem;
            })->values()->all();

         

            $matches = collect($filteredData)
			    ->flatMap(function ($item) {
			        return collect($item->matches);
			    });

			$players = $matches
			    ->map(function ($match) {
			        return collect($match);
			    })->flatten(3)->all();
			 // dd(collect($players));
			$playerData = collect($players)
			    ->map(function ($game) {
			        return collect($game->player)
			            ->mapWithKeys(function ($player) {
			                $id = $player->id ?? null;
			                $name = $player->name ?? '';

			                if ($id) {
			                    return [$id => $name];
			                }

			                return [];
			            });
			    })->toArray();
			foreach ($playerData as $subArray) {
                    foreach ($subArray as $key => $value) {
                        $teams[$key] = $value;
                    }
                }
            $teamIds = array_keys($teams);
            $leagueIds = implode(',', array_unique($teamIds));
        }
        else{
             $cacheData = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category]);
             $filteredData = $cacheData->map(function ($item) use($id) {
                 $modifiedItem = (array) $item;
                 $matchData = collect(is_array($item->matches->match) ? $item->matches->match : [$item->matches->match] );
                 
                 $modifiedItem['matches']->match = $matchData->map(function ($match) {
                
                 $filteredOdds = collect(is_array($match->odds->type) ? $match->odds->type : [$match->odds->type])->filter(function ($type) {
                        return in_array($type->value ?? $type->{'@value'}, ['3Way Result', 'Home/Away', 'Over/Under']);
                    })->map(function ($type) {
                        $type->bookmaker = is_array($type->bookmaker) ? [$type->bookmaker[0]] : [$type->bookmaker];
                        return $type;
                    });
        
                    $match->odds->type = $filteredOdds->values()->all();
                    unset($match->odds);
                    return $match;
                 
                 })->values()->all();
                            
                        return (object) $modifiedItem;
                })->values()->all();
                
                $leagueData = collect($filteredData)->map(function ($item) use ($id) {
                    $singleItem = collect($item->matches->match)->mapWithKeys(function ($match) use ($id) {
                        $away = $id == 3 ? $match->visitorteam->id : $match->awayteam->id ?? $match->awayteam->{'@id'};
                        $local = $match->localteam->id ?? $match->localteam->{'@id'};
                        $localTeamName = $match->localteam->name ?? $match->localteam->{'@name'};
                        $awayTeam = $id == 3 ? $match->visitorteam->name : $match->awayteam->name ?? $match->awayteam->{'@name'};
                
                        $result = [];
                        if (isset($away)) {
                            $result[$away] = $awayTeam;
                        }
                        if (isset($local)) {
                            $result[$local] = $localTeamName;
                        }
                    
                        return $result;
                    });
                    return $singleItem;
                })->toArray();
                foreach ($leagueData as $subArray) {
                    foreach ($subArray as $key => $value) {
                        $teams[$key] = $value;
                    }
                }
                $teamIds = array_keys($teams);
                $leagueIds = implode(',', $teamIds);
                
        }
        
        // return $leagueIds;
      if($category->image != ''){ 
          if($leagueIds){
          	$imageType = $id == 5 ? 'players' : 'teams';

          	$leagueIdsArray = explode(',', $leagueIds);
          	$chunks = array_chunk($leagueIdsArray, 40);

          		foreach ($chunks as $chunk) {
          			$chunkIds = implode(',', $chunk);
	                $url = "https://app.trambetbd.com/image-response.php?league=".$category->image."&url=".$chunkIds."&type=".$imageType."";
	                    $data = $this->imageFetchInstance($url);
                        // return $data;
	                    if(!empty($data) && count($data) > 0){
	                        foreach($data as $key => $item){
	                            
	                            $image = str_replace('data:image/jpeg;base64,', '', $item->base64);
	                            $image = str_replace(' ', '+', $image);
	                            $imageName = $item->id.'.'.'jpeg';
	                            $imageNameByTeamName = $this->transformString($teams[$item->id]).'.'.'jpeg';
	                            \File::put(storage_path(). '/app/public/teams/' . $imageName, base64_decode($image));
	                            \File::put(storage_path(). '/app/public/teams/' . $imageNameByTeamName, base64_decode($image));
	                        }
	                        
	                    }
                }
                $notify[] = ['success', 'Image Imported'];
                return back()->withNotify($notify);
                    
          }else{
                $notify[] = ['error', 'No Team found'];
                return back()->withNotify($notify);
            }
      }
        else{
            $notify[] = ['error', 'Image not found for this category'];
            return back()->withNotify($notify);
        }
    }
    
    // String transform
    public function transformString($input) {
        $lowercase = strtolower($input);
        $replaceSpaces = str_replace(' ', '-', $lowercase);
        $result = str_replace('/', '-', $replaceSpaces);
        return $result;
        
    }
    
    
    
    
    
    // Game Response
    public function gameResponse($id, $league, $page = 'sports'){
        $category = GoalCategory::where('status', 1)->where('id', $id)->first();
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
                            ->values();
                }
                else{
                    $response = collect(is_array($data->scores->category) ? $data->scores->category : [$data->scores->category])
                            ->filter(function ($item) use ($league) {
                                $id = $item->id ?? $item->{'@id'} ?? null;
                                return $id == $league;
                            })
                            ->values();
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
    
    // Category Response Response
    public function categoryResponse(){
        $cacheKey = 'odds_category_' . md5('category');
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
    
    
    
    // WebSocket connection
    public function getToken()
    {
        $apiKey = '89b86665dc8348f5605008dc3da97a57'; 
        $url = 'http://85.217.222.218:8765/api/v1/auth/gettoken';

        $client = new Client();
        $response = $client->post($url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'apiKey' => $apiKey,
            ]),
        ]);

        $data = json_decode($response->getBody(), true);

        return response()->json($data);
    }
    
    // Category wise teams
    public function teamsList($id){
        return $id;
    }
    
    // Team Image Upload Page 
    public function teamImageUploadPage(){
    	$pageTitle = 'Team Image Upload';
    	return view('admin.goalserve.teams', compact('pageTitle'));
    }

    public function teamImageUpload(Request $request){
    	$request->validate([
            'image' => 'required|file|mimes:jpeg|max:100',
            'team' => 'required'
        ]);


        if($request->hasFile('image')){
        	 $file = $request->file('image');
        	 $filename = $this->transformString($request->team). '.' . 'jpeg';
        	 \File::put(storage_path(). '/app/public/teams/' . $filename, file_get_contents($file));

        	 $notify[] = ['success', 'Successfully uploded'];
    		 return back()->withNotify($notify);
        }

        $notify[] = ['error', 'Someting went wrong. try again later'];
    	return back()->withNotify($notify);
    }
    
}
