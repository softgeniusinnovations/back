<?php

namespace App\Http\Controllers\Admin;

use App\{
    Http\Controllers\Controller,
    Models\Extension,
    Models\Category,
    Models\League,
    Models\Team,
    Models\Game,
    Models\Question,
    Models\Option
};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
class ExtensionController extends Controller
{
    public function index()
    {   
        $pageTitle = 'Extensions';
        $extensions = Extension::orderBy('name')->get();
        return view('admin.extension.index', compact('pageTitle', 'extensions'));
    }

    public function update(Request $request, $id)
    {
        $extension = Extension::findOrFail($id);
        $validationRule = [];
        foreach ($extension->shortcode as $key => $val) {
            $validationRule = array_merge($validationRule,[$key => 'required']);
        }
        $request->validate($validationRule);

        $shortcode = json_decode(json_encode($extension->shortcode), true);
        foreach ($shortcode as $key => $value) {
            $shortcode[$key]['value'] = $request->$key;
        }

        $extension->shortcode = $shortcode;
        $extension->save();
        $notify[] = ['success', $extension->name . ' updated successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return Extension::changeStatus($id);
    }
    
    // Odds api call
    public function fetchOdds(){
        try{
            $extension = Extension::where('act', 'odds')->where('status', 1)->first();
            if($extension){
                 $shortcode = json_decode(json_encode($extension->shortcode), true);
                 $apiKey = $shortcode['app_key']['value'];
                 $categories = [];
                 if($apiKey){
                     $responseData = [];
                     $host = 'https://api.the-odds-api.com/v4/sports/';
                     $categoryUrl = $host.'?apiKey='.$apiKey.'&all=true';
                    //  $response = $this->getCaregories($categoryUrl);
                     
                     try{
                         DB::beginTransaction();
                         
                        //  if(count($response) > 0){
                        //      foreach($response as $item){
                        //          $isExist = Category::where('name', $item['name'])->where('source', 'api')->first();
                        //          if($isExist){
                        //             $newLeague = $this->leagueStore($isExist->id, $item['leagues']);
                        //          }else{
                        //              $newCategory = $this->categoryStore($item);
                        //              $newLeague = $this->leagueStore($newCategory->id, $item['leagues']);
                        //          }
                        //      }
                        //  }
                        
                        
                        // Get Active categories active leagues
                        $fetchLeaguesFromDb = $this->fetchLeagues();
                        
                        // League data run for team and game create
                        foreach($fetchLeaguesFromDb as $item){
                            if(count(@$item->leagues) > 0){
                                foreach(@$item->leagues as $league){
                                //   $teamData =  $this->fetchTeams($league, $apiKey);
                                  $teamData =  $this->fetchTeams($league, $apiKey);
                                  
                                  if(count($teamData) > 0){
                                      foreach($teamData as $team){
                                          
                                        // Team store
                                        // if($team['home_team']){
                                        //     $this->teamStore ($team['home_team'], $item);
                                        // }
                                        // if($team['away_team']){
                                        //     $this->teamStore($team['away_team'], $item);
                                        // }
                                        // else{
                                        //     if(count($team['bookmakers']) > 0){
                                        //         foreach($team['bookmakers'] as $bookmaker){
                                        //             if(count($bookmaker['markets']) > 0 ){
                                        //                 foreach($bookmaker['markets'] as $market){
                                        //                     if(count($market['outcomes']) > 0){
                                        //                         foreach($market['outcomes'] as $outcome){
                                        //                             $this->teamStore($outcome['name'], $item);
                                        //                         }
                                        //                     }
                                        //                 }
                                        //             }
                                        //         }
                                        //     }
                                        // }
                                        
                                        
                                        // Insert games
                                        $newGameStore = $this->gameStore($team, $league);
                                        
                                        
                                          
                                      }
                                  }
                                 
                                }
                            }
                        }
                         
                         DB::commit();
                     } catch(\Exception $e){
                         DB::rollback();
                         return $e->getMessage();
                     }
                     

                   
                 }
            }
        }catch(\Exception $e){
          return $e->getMessage();
        }
    }
    
    // Get categories
    public function getCaregories($url){
         try{
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $url);
            $categoriesResponse = $response->getBody();
            $data = json_decode($categoriesResponse, true);
                     
            foreach ($data as $item) {
                $name = $item["group"];
                $slug = strtolower(str_replace(' ', '_', $name));
                $status = $item["active"] ? 1 : 0;
            
                if (!isset($categories[$name])) {
                    $categories[$name] = [
                        "name" => $name,
                        "slug" => $slug,
                        "status" => $status,
                        "leagues" => [],
                    ];
                }
            
                $categories[$name]["leagues"][] = [
                    "slug" => $item["key"],
                    "short_name" => $item["title"],
                    "name" => $item["description"],
                    "status" => $item["active"] ? 1 : 0,
                    "has_outrights" => $item["has_outrights"],
                ];
            }
            
            $categories = array_values($categories);
            
            $output = array_map(function ($category) {
                return [
                    "name" => $category["name"],
                    "slug" =>  $category["slug"],
                    "status" =>  $category["status"],
                    "leagues" => $category["leagues"],
                ];
            }, $categories);
            
            return $output;
         }catch(\Exception $e){
              $notify[] = ['error', 'API Call issue'];
              return back()->withNotify($notify);
         }
    }
    
    // Get Teams
    public function getTeams($url){
        try{
            $client = new \GuzzleHttp\Client();
            $response = $client->request('GET', $url);
            $response = $response->getBody();
            $data = json_decode($response, true);
            return $data;
        } catch(\Exception $e){
            return $e->getMessage();
        }
    }
    
    // Category Store
    public function categoryStore($item){
        try{
            $itemObj = new Category;
             $itemObj->name = $item['name'];
             $itemObj->slug = $item['slug'];
             $itemObj->source= 'api';
             $itemObj->status= 1;
             $itemObj->save();
        } catch(\Exception $e){
            \Log::info('Category store problem');
        }
         
    }
    
    // League Store
    public function leagueStore($category_id, $categoryData){
        try{
            if(count($categoryData) > 0)
            {
                foreach($categoryData as $item){
                    $isExist = League::where('short_name', $item['short_name'])->where('category_id', $category_id)->where('slug', $item['slug'])->where('source', 'api')->first();
                    if(!$isExist){
                        $itemObj = new League;
                        $itemObj->category_id = $category_id;
                        $itemObj->name = $item['name'];
                        $itemObj->short_name = $item['short_name'];
                        $itemObj->slug = $item['slug'];
                        $itemObj->source = 'api';
                        $itemObj->status = $item['status'];
                        $itemObj->save();
                    }else{
                        
                        $isExist->name = $item['name'];
                        $isExist->source = 'api';
                        $isExist->status = $item['status'];
                        $isExist->save();
                    }
                }
            }
        } catch(\Exception $e){
            \Log::info('League store problem');
            echo $e->getMessage();
        }
        
    }
    
    // fetch all leagues 
    public function fetchLeagues(){
        $categories = Category::with(['leagues'=> function($q){
                    $q->where('status', 1)->where('source', 'api');
            }])->where('source', 'api')->where('status', 1)->orderBy('name', 'ASC')->get();
        return $categories;
    }
    
    // FetchGame
    public function fetchTeams($league, $apiKey){
         $responseData = [];
         $markets = '&markets=h2h,spreads,totals';
         $host = 'https://api.the-odds-api.com/v4/sports/';
         $url = $host.$league->slug.'/odds?apiKey='.$apiKey.'&oddsFormat=decimal&regions=us'.$markets;
        //  dd($url);
         
         try{
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $url);
                $responseData = $response->getBody();
                $data = json_decode($responseData, true);
         } catch(\Exception $e){
                 $url = $host.$league->slug.'/odds?apiKey='.$apiKey.'&oddsFormat=decimal&regions=us';
                $client = new \GuzzleHttp\Client();
                $response = $client->request('GET', $url);
                $responseData = $response->getBody();
                $data = json_decode($responseData, true);
         }
         
         return $data;
         
    }
    
    // Team Store function
    public function teamStore($checker, $item){
      try{
          $isExistTeam = Team::where("slug", Str::slug($checker))->first();
          if(!$isExistTeam){
              $newTeam = new Team;
              $newTeam->category_id = $item->id;
              $newTeam->slug = Str::slug($checker);
              $newTeam->name = $checker;
              $newTeam->short_name = $this->wfl($checker);
              $newTeam->source = 'api';
              $newTeam->save();
          }
      }catch(\Exception $e){
          echo $e->getMessage();
      }
    }
    
    // Word first letter
    public function wfl($data){
        $output = Str::of($data)
        ->explode(' ')
        ->map(function ($word) {
            return Str::upper(Str::substr($word, 0, 1));
        })
        ->implode('');
        return $output;
    }
    
    // Game store
    public function gameStore($team, $league){
        try{
            if($team['home_team'] != null && $team['away_team'] != null){
                $isExist = Game::where('game_id', $team['id'])->where('source', 'api')->orWhere('game_id', $team['id'])->first();
                $firstTeam = Team::where('name', $team['home_team'])->first();
                $secondTeam = Team::where('name', $team['away_team'])->first();
                if(!$isExist && $firstTeam && $secondTeam){
                    $newGame = new Game;
                    $newGame->game_id = $team['id'];
                    $newGame->source = 'api';
                    $newGame->team_one_id = $firstTeam->id;
                    $newGame->team_two_id = $secondTeam->id;
                    $newGame->league_id = $league->id;
                    $newGame->slug = Str::slug($team['sport_title'].@$firstTeam->name.@$secondTeam->name.$team['commence_time'].time());
                    $newGame->start_time = Carbon::parse($team['commence_time']);
                    $newGame->bet_start_time = Carbon::parse($team['commence_time'])->subMinutes(10);
                    $newGame->bet_end_time = Carbon::parse($team['commence_time'])->addYears(1);
                    $newGame->status = 1;
                    $newGame->save();
                    
                    // Insert Question/ Market 
                    $this->questionStore($team, $newGame);
                }else{
                    // Insert Question/ Market 
                    $this->questionStore($team, $isExist);
                }
            }
        } 
        catch(\Exception $e){
            echo $e->getMessage();
        }
    }
    
    // Question store
    public function questionStore($questions, $game){
        try{
            if(count($questions['bookmakers']) > 0){
                foreach($questions['bookmakers'] as $question){
                    if(count($question['markets']) > 0)
                        {
                            foreach($question['markets'] as $market)
                            {
                                $isExist = Question::where('title', $question['title'].' - '. $market['key'])->where('slug', $question['key'])->where('source', 'api')->where('game_id', $game->id)->first();
                                if(!$isExist){
                                    
                                    $newQ = new Question;
                                    $newQ->game_id = $game->id;
                                    $newQ->source = 'api';
                                    $newQ->slug = $question['key'];
                                    $newQ->title= $question['title'].' - '. $market['key'];
                                    $newQ->save();
                                    
                                    // Option Store
                                    $this->optionStore($market, $newQ);
                                    
                                }else{
                                    $isExist->game_id = $game->id;
                                    $isExist->source = 'api';
                                    $isExist->slug = $question['key'];
                                    $isExist->title= $question['title'].' - '. $market['key'];
                                    $isExist->save();
                                    
                                    // Option Store
                                    $this->optionStore($market, $isExist);
                                }
                            }
                        }
                }
            }
        } catch(\Exception $e){
            echo 'Question Store=>'.$e->getMessage();
        }
    }
    
    // Option Store
    public function optionStore($market, $question){
        try{
            // if(count($markets['markets']) > 0){
            //     foreach($markets['markets'] as $market){
                    if(count($market['outcomes'])>0){
                        foreach($market['outcomes'] as $option){
                            $name = $market['key'] == 'h2h' ? $option['name']:$option['name'].'-'.$option['point'];
                            $isExist = Option::where('name', $name)->where('source', 'api')->where('question_id', $question->id)->first();
                            if(!$isExist){
                                $newO = new Option;
                                $newO->question_id = $question->id;
                                $newO->name = $name;
                                $newO->source = 'api';
                                $newO->odds = $option['price'];
                                $newO->save();
                            }else{
                                $isExist->question_id = $question->id;
                                $isExist->name = $name;
                                $isExist->source = 'api';
                                $isExist->odds = $option['price'];
                                $isExist->save();
                            }
                        }
                    }
            //     }
            // }
        } catch(\Exception $e){
            echo 'Option Store => '.$e->getMessage();
        }
    }

    
    
    
    
}
