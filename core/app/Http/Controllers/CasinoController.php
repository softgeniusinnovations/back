<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CasinoGameSession;
use Carbon\Carbon;
class CasinoController extends Controller {
    public $host = 'http://tbs2api.aslot.net/API/';
    public $hall = '3201995';
    public $key = 'testhall';
    public $cdnUrl = 'https://static.cdns-stat.com/resources/';
    public $domain = 'https://trambet.com/';

    
    // Live casino
    public function liveCasino(){
        $pageTitle = 'Live Casino';
        return view($this->activeTemplate . 'casino', compact('pageTitle'));
    }
    
    // Get casino data
    public function getCasinoData(){
        $url = $this->host;
        $raw = [
            "cmd" => "gamesList",
            "hall" => $this->hall,
            "key" => $this->key,
            "cdnUrl" => $this->cdnUrl,
        ];
        
        $client = new \GuzzleHttp\Client();
        $response = $client->request('POST', $url,[
            'body' => json_encode($raw),
            'headers' => [
                 'Content-Type' => 'application/json',
            ]
        ]);
        
        $responseData = $response->getBody()->getContents();
        return $responseData;
    }
    
    // Open Game
    public function openCasinoGame(Request $request){
       try{
            $url = $this->host.'openGame/';
            $raw = [
                "cmd"=>"openGame",
                "hall" => $this->hall,
                "key" => $this->key,
                "cdnUrl" => $this->cdnUrl,
                "domain"=>"https://trambet.com",
                "exitUrl"=>"https://p333r1m2287.com/user/casino/close-game",
                "language"=>"en",
                "login"=> auth()->user()->user_id,
                "gameId"=>$request->id,
                "cdnUrl"=>"https://static.cdns-stat.com/resources",
                "demo"=>$request->demo,
                "continent"=>"eur"
            ];
            // dd($raw);
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url,[
                'body' => json_encode($raw),
                'headers' => [
                     'Content-Type' => 'application/json',
                ]
            ]);
            
            $responseData = $response->getBody()->getContents();
            
            $data = json_decode($responseData, true);
            // dd($data);
            
            if($data['content']['game']['sessionId']){
               $sessionData = new CasinoGameSession;
               $sessionData->user_id= auth()->user()->user_id;
               $sessionData->session_id= $data['content']['game']['sessionId'];
               $sessionData->game_name= $request->name;
               $sessionData->save();
            }
            
            return $responseData;
       } 
       catch(\Exception $e){
           return $e->getMessage();
       }
    }
    
    // Close Game
    public function closeCasinoGame(){
        $pageTitle = 'Casino closed';
        return view($this->activeTemplate . 'casino_close', compact('pageTitle'));
    }
    
    // Iframe open game
    public function casinoGameOpen(Request $request){
         $pageTitle = 'Casino open';
         $src = $request->url;
        return view($this->activeTemplate . 'casino_open', compact('pageTitle', 'src'));
    }
    //casino history
    public function casinoHistory(){
        $pageTitle = 'Casino Bet Hisotry';
        $user = auth()->user()->user_id;
        $currentDate = Carbon::now();
        $nineDaysAgo = $currentDate->copy()->subDays(9);
        $data = CasinoGameSession::where('user_id', $user)
            ->whereBetween('created_at', [$nineDaysAgo->startOfDay(), $currentDate->endOfDay()])
            ->latest()->limit(100)->paginate(10);
       return view($this->activeTemplate . 'casino_history', compact('pageTitle', 'data'));
    }
     //casino session
    public function casinoSession($session){
        try{
            $pageTitle = "Casino $session Hisotry";
            $url = $this->host;
            $raw = [
                "cmd"=>"gameSessionsLog",
                "hall" => $this->hall,
                "key" => $this->key,
                "sessionsId"=>$session,
                "count"=>40,
                "page"=> 1
            ];
            // dd($raw);
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url,[
                'body' => json_encode($raw),
                'headers' => [
                     'Content-Type' => 'application/json',
                ]
            ]);
            
            $responseData = $response->getBody()->getContents();
            
            $data = json_decode($responseData, true);
            return view($this->activeTemplate . 'casino_session', compact('pageTitle','data', 'session'));
        } catch(\Exception $e){
            $notify = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
}