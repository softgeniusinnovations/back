<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Carbon\Carbon;
class CricketController extends Controller
{
    public $apiKey;
    public $baseUrl;
    public $baseUrlV3;
    public $client;
    public $categoryName;
    public $categoryId;
    
    public $scoreapiurl;
    
    /*
        Cricket = 3
        Handball = 78
        Rugby union = 8
        Rugby League = 19
        Boxing = 9 Upcoming not show
        MMA = 162
        Darts = 15
        
        
        Golf = Upcoming not show
    
    
    */
    
    
    
    public function __construct(Client $client)
    {
        // https://api.b365api.com/v1/bet365/upcoming?sport_id=3&token=184366-38ewbBi00F7thQ
        $this->apiKey = "184366-38ewbBi00F7thQ";
        $this->baseUrl = "https://api.b365api.com/v1/bet365/";
        $this->baseUrlV3 = "https://api.b365api.com/v3/bet365/";
        $this->scoreapiurl = "https://api.betsapi.com/v1/bet365/event/";
        $this->client = $client;
        $this->categoryName = "Clicket";
        $this->categoryId = 3;

    }
    
    // Valida sport ID
    private $validSportIds = [
        3 => 'Cricket',
        78 => 'Handball',
        8 => 'Rugby Union',
        19 => 'Rugby League',
        9 => 'Boxing',
        162 => 'MMA',
        15 => 'Darts'
    ];
    
    // Games
    public function games(Request $request){
        $validator = Validator::make($request->all(), [
            'sport_id' => [
                'nullable',
                'integer',
                'in:' . implode(',', array_keys($this->validSportIds)),
                'required_if:type,upcoming,live'
            ],
            'type' => ['required', 'string', 'in:live,upcoming,prematch,result,live-details,score,scorerun'],
            'event' => ['nullable', 'required_if:type,prematch,result']
        ]);



        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Something went wrong!',
                'errors' => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }
        
        if($request->type == "upcoming"){
            return $this->upcomingGamesData($request);
        }else if(($request->type == "live")){
            return $this->liveGamesData($request);
        }else if(($request->type == "prematch")){
            return $this->preMatchGamesOdds($request);
        }else if(($request->type == "live-details")){
            return $this->LiveGamesDetails($request);
        }
        else if(($request->type == "score")){
            return $this->scoreBoard($request);
        }
        else if(($request->type == "scorerun")){
            return $this->scoreBoardrun($request);
        }
        else{
            return $this->gameResults($request);
        }
    }
    // Upcoming data method
    public function upcomingGamesData(Request $request)
    {
        
        $sportId = $request->get('sport_id');
        $params = [
            "sport_id" => $sportId,
            "token" => $this->apiKey
        ];

        $url = $this->baseUrl . "upcoming";

        $result = $this->makeApiCall($url, $params);
        $result['data']['type'] = "upcoming";
        $result['data']['category_name'] = $this->validSportIds[$sportId];

        return response()->json($result);
    }
    
     // Live data method
    public function LiveGamesData(Request $request)
    {
        $sportId = $request->get('sport_id');
        $params = [
            "sport_id" => $sportId,
            "token" => $this->apiKey
        ];

        $url = $this->baseUrl . "inplay_filter";

        $result = $this->makeApiCall($url, $params);
        $result['data']['type'] = "live";
        $result['data']['category_name'] = $this->validSportIds[$sportId];

        return response()->json($result);
    }
    
        
     // Live data method
    public function LiveGamesDetails(Request $request)
    {
        $sportId = $request->get('FI');
        $params = [
            "FI" => $sportId,
            "token" => $this->apiKey
        ];

        $url = $this->baseUrl . "event";

        $result = $this->makeApiCall($url, $params);
        $result['data']['type'] = "live details";

        return response()->json($result);
    }
    
    public function scoreBoard(Request $request)
    {


        $sportId = $request->get('FI');
        $params = [
            "FI" => $sportId,
            "token" => $this->apiKey,
            "lineup"=>1
        ];

        $url = $this->scoreapiurl;

        $result = $this->makeApiCall($url, $params);
        // $result['data']['type'] = "live details";

        return response()->json($result);
    }

    public function scoreBoardrun(Request $request)
    {
        $sportId = $request->get('FI');
        $params = [
            "FI" => $sportId,
            "token" => $this->apiKey,
            "stats"=>1
        ];

        $url = $this->scoreapiurl;

        $result = $this->makeApiCall($url, $params);
        // $result['data']['type'] = "live details";

        return response()->json($result);
    }
    
    
    
    
    // Pre Match Games Odds
    public function preMatchGamesOdds(Request $request)
    {
        $fi = $request->get('event'); // Upcoming game id
        $params = [
            "FI" => $fi,
            "token" => $this->apiKey
        ];
        $url = $this->baseUrlV3 . "prematch";

        $result = $this->makeApiCall($url, $params);
        $result['data']['type'] = "prematch odd";
        return response()->json($result);
    }
    
     // Pre Match Games Odds
    public function gameResults(Request $request)
    {
        $event = $request->get('event'); // Upcoming game id
        $params = [
            "event_id" => $event,
            "token" => $this->apiKey
        ];
        $url = $this->baseUrl . "result";

        $result = $this->makeApiCall($url, $params);
        $result['data']['type'] = "result";
        return response()->json($result);
    }
    
    
    
    // Helper function to make the API call
    private function makeApiCall($url, $params = [], $headers = [])
    {
        try {
            $response = $this->client->get($url, [
                'query' => $params,
                'headers' => $headers,
            ]);

            $data = json_decode($response->getBody()->getContents(), true);

            
            // If the response contains a success flag, you can also check it here
            return [
                'success' => true,
                'data' => $data
            ];

        } catch (\Exception $e) {
//            \Log::error('Error fetching data: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to fetch data. Please try again later.'
            ];
        }
    }
}