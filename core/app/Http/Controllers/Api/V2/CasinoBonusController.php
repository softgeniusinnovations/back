<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\KycFormSubmit;
use App\Http\Resources\KycFormResource;
use App\Http\Resources\NewsCollection;
use App\Http\Resources\NewsDetailsCollection;
use App\Http\Resources\PolicyCollection;
use App\Http\Resources\RefundPolicyCollection;
use App\Http\Resources\TermsOfServiceCollection;
use App\Models\CasinoGameSession;
use App\Models\Form;
use App\Models\Frontend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CasinoBonusController extends Controller
{
    public $host = 'http://tbs2api.aslot.net/API/';
    // public $hall = '3201995';
//    public $hall;
//    public $hall = '3205382';
    public $hall = '3205824';
    public $key = 'testhall';
    public $cdnUrl = 'https://static.cdns-stat.com/resources/';
    public $domain = 'https://team7.p333r1m2287.com/';
//    public function __construct(Request $request)
//    {
//
//        parent::__construct();
//        $this->hall = $request->get('hall', 3205382);
////        Log::info("api".$this->hall);
//
//
//        if (!is_numeric($this->hall)) {
//            abort(400, 'Invalid hall parameter.');
//        }
//    }
    public function getLiveCasinobonus()
    {

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
        $responseData = json_decode($responseData,true);
        $payload = [
            'status'         => true,
            'data' => $responseData,
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function openCasinoGame(Request $request){

        try{
            Log::info($this->hall);
            $url = $this->host.'openGame/';
            $raw = [
                "cmd"=>"openGame",
                "hall" => $this->hall,
                "key" => $this->key,
                "cdnUrl" => $this->cdnUrl,
                "domain"=>"https://team7.p333r1m2287.com/",
                "exitUrl"=>"https://team7.p333r1m2287.com/user/casino/close-game",
                "language"=>"en",
                "login"=> auth()->user()->user_id,
                "gameId"=>$request->id,
                "cdnUrl"=>"https://static.cdns-stat.com/resources",
                "demo"=>$request->demo,
                "continent"=>"bdt"
            ];
            Log::info(print_r($raw, true));
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url,[
                'body' => json_encode($raw),
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseData = $response->getBody()->getContents();


            $data = json_decode($responseData, true);

            if($data['status'] == true){

                if($data['content']['game']['sessionId']){
                    $sessionData = new CasinoGameSession;
                    $sessionData->user_id= auth()->user()->user_id;
                    $sessionData->session_id= $data['content']['game']['sessionId'];
                    $sessionData->game_name= $request->name;
                    $sessionData->save();
                }

                $payload = [
                    'status'         => true,
                    'data' => $data['content'],
                    'app_message'  => 'Successfully Retrieve Data',
                    'user_message' => 'Successfully Retrieve Data'
                ];
                return response()->json($payload, 200);
            }else{
                $payload = [
                    'status' => false,
                    'app_message' => 'Please try again.',
                    'user_message' => 'Please try again.'
                ];
                return response()->json($payload, 200);
            }

        }
        catch(\Exception $e){
            $payload = [
                'status' => false,
                'app_message' => 'Please try again.',
                'user_message' => 'Please try again.'
            ];
            return response()->json($payload, 200);
        }
    }

    public function casinoHistory($pageNo = null, $perPage = null){
        $perPage = $perPage ?? 10;
        //$pageNo = $request->query('page', 1);
        $user = auth()->user();
        $currentDate = Carbon::now();
        $nineDaysAgo = $currentDate->copy()->subDays(9);
        $gameSessions = CasinoGameSession::where('user_id', $user->user_id)
            ->whereBetween('created_at', [$nineDaysAgo->startOfDay(), $currentDate->endOfDay()])
            ->latest()->limit(100);
        $totalItems = $gameSessions->count();
        if($pageNo){
            $skip = $pageNo == 1 ? 0 : $perPage * ($pageNo - 1);
            $gameSessions = $gameSessions->skip($skip)->take($perPage)->get();
            $paginationData = [
                'currentPage' => $pageNo,
                'nextPage' => $pageNo + 1,
                'totalPages' => ceil($totalItems / $perPage),
                'totalItems' => $totalItems,
                'itemsPerPage' => $perPage,
            ];
        } else {
            $gameSessions = $gameSessions->get();
        }
        $payload = [
            'status'        => true,
            'paginationData' => $paginationData,
            'data'              => $gameSessions,
            'app_message'   => 'Successfully Retrieve Data',
            'user_message'  => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }

    //casino session
    public function casinoSession($session){
        try{
            $url = $this->host;
            $raw = [
                "cmd"=>"gameSessionsLog",
                "hall" => $this->hall,
                "key" => $this->key,
                "sessionsId"=>$session,
                "count"=>40,
                "page"=> 1
            ];
            $client = new \GuzzleHttp\Client();
            $response = $client->request('POST', $url,[
                'body' => json_encode($raw),
                'headers' => [
                    'Content-Type' => 'application/json',
                ]
            ]);

            $responseData = $response->getBody()->getContents();

            $data = json_decode($responseData, true);
            $payload = [
                'status'        => true,
                'data'          => $data,
                'app_message'   => 'Successfully Retrieve Data',
                'user_message'  => 'Successfully Retrieve Data'
            ];
            return response()->json($payload, 200);
        } catch(\Exception $e){
            $payload = [
                'status'        => false,
                'app_message'   => 'Please try again.',
                'user_message'  => 'Please try again.'
            ];
            return response()->json($payload, 400);
        }
    }
}
