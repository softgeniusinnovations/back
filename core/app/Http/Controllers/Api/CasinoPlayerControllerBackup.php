<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
class CasinoPlayerControllerBackup extends Controller
{
    public function casinoPlayer(Request $request)
    {
        Log::info("getbalance");
        if($request->isMethod('post')){
            Log::info("getbalance");
            if($request->cmd === 'getBalance'){
                Log::info("getbalance");
                Log::info($request);
                $validator = Validator::make($request->all(), [
                    'cmd' => 'required|in:getBalance',
                    'hall' => 'required',
                    'key' => 'required',
                    'login' => 'required',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'fail',
                        'error' => $validator->errors()->first(),
                    ]);
                }

                $user = $this->fetchBalanceFromDatabase($request->login);
                $hall = $request->input('hall');
                if($hall){
                    Log::info("hall in getbalance");
                    Log::info($hall);
                }


                if ($user === null) {
                    return response()->json([
                        'status' => 'fail',
                        'error' => 'user_not_found',
                    ]);
                }
//                if ($user) {
//                    $balance = $user->balance + $user->withdrawal;
//                } else {
//
//                    $balance = 0;
//                }

                return response()->json([
                    'status' => 'success',
                    'error' => '',
                    'login' => $request->login,
                    // 'balance' => str_replace(',', '', number_format((float) convertCurrency($user->balance, $user->currency, 'EUR'), 2)),
                    // 'currency' => "EUR",
                    'balance' => $hall==3205824?str_replace(',', '', number_format((float) $user->casino_bonus_account, 2)):str_replace(',', '', number_format((float) $user->balance, 2)),
                    'currency' => "BDT",

                ]);
            }

            if($request->cmd === 'writeBet'){
                $validator = Validator::make($request->all(), [
                    'cmd' => 'required|in:writeBet',
                    'login' => 'required|string',
                    'hall' => 'required|integer',
                    'key' => 'required|string',
                    'bet' => 'required|numeric|min:0',
                    'win' => 'required|numeric|min:0',
                    'tradeId' => 'required|string',
                    'betInfo' => 'nullable|string',
                    'gameId' => 'required|integer',
                    'matrix' => 'nullable|string',
                    'sessionId' => 'required|integer',
                    'date' => 'nullable|date_format:Y-m-d H:i:s',
                    'WinLines' => 'nullable|string',
                ]);

                if ($validator->fails()) {
                    return response()->json([
                        'status' => 'fail',
                        'error' => $validator->errors()->first(),
                    ], 400);
                }


                $login = $request->input('login');
                $hall = $request->input('hall');
                $key = $request->input('key');
                $bet = (float) $request->input('bet'); // Convert to float
                $win = (float) $request->input('win'); // Convert to float
                $tradeId = $request->input('tradeId');
                $betInfo = $request->input('betInfo');
                $gameId = $request->input('gameId');
                $matrix = $request->input('matrix');
                $sessionId = $request->input('sessionId');

//                check hall for bonus

                $user = $this->fetchBalanceFromDatabase($login);

                if ($user === null) {
                    return response()->json([
                        'status' => 'fail',
                        'error' => 'user_not_found',
                    ],200);
                }
                if($hall==3205824){
                    Log::info('casino bonus');
                }


                // $currentBalance = str_replace(',', '', number_format((float) convertCurrency($user->balance, $user->currency, 'EUR'), 2));
                $currentBalance = str_replace(',', '', number_format((float) $user->balance, 2));
                $withdrawalBalance = str_replace(',', '', number_format((float) $user->withdrawal, 2));

                // Create bet session
                if($bet > 0){
                    Cache::put('bet_'.$sessionId.'_'.$gameId, $bet, 120 );
                }


                if ($betInfo === 'refund') {
                    // $convertBalance = convertCurrency($bet, 'EUR', $user->currency);
                    $convertBalance = $bet;

                    $refunded = User::where('user_id', $login)->first();
                    $refunded->balance +=  $convertBalance;
                    $refunded->save();

                    $user->increment('balance', $convertBalance);
                    return response()->json([
                        'status' => 'success',
                        'error' => '',
                        'login' => $login,
                        // 'balance' => str_replace(',', '', number_format((float) convertCurrency($refunded->balance, $refunded->currency, 'EUR'), 2)),
                        'balance' => str_replace(',', '', number_format((float) $refunded->balance, 2)),
                        'currency' => 'BDT',
                        'operationId' => rand(100, 9999999999),
                    ], 200);
                }

                if ($bet > $currentBalance || $currentBalance < 0) {
                    return response()->json([
                        'status' => 'fail',
                        'error' => 'fail_balance',
                    ], 200);
                }else{
                    $stable = $currentBalance - $bet;
                    // $adjustment = $stable  + $win;
                    // User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) convertCurrency($adjustment,'EUR',$user->currency), 2))]);


                    // Session and gameId dhore kaj korte hobe, karon last response a bet er data dai na tai bet er data dhore rakhte hobe
                    if($bet == 0){
                        $playBet = Cache::get('bet_'.$sessionId.'_'.$gameId);
                        if($win >= $playBet){
                            $adjustment = $stable  + $playBet;
                            $winBalance = $withdrawalBalance + ($win + $playBet);
                            User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) $adjustment, 2)), 'withdrawal' => str_replace(',', '', number_format((float) $winBalance, 2))]);
                            Cache::forget('bet_'.$sessionId.'_'.$gameId);
                        }

                    }else{
                        $adjustment = $stable  + $win;
                        User::where('user_id',$login)->update(['balance'=> str_replace(',', '', number_format((float) $adjustment, 2))]);
                    }


                    // else{
                    //   $adjustment = $stable;
                    //   $winBalance = $withdrawalBalance;
                    // }



                    $updatedData = User::where('user_id', $login)->first();

                    return response()->json([
                        'status' => 'success',
                        'error' => '',
                        'login' => $login,
                        // 'balance' => str_replace(',', '', number_format((float) convertCurrency($updatedData->balance, $updatedData->currency, 'EUR'), 2)),
                        'balance' => str_replace(',', '', number_format((float) $updatedData->balance, 2)),
                        'currency' => 'BDT',
                        'operationId' => rand(100, 9999999999),
                    ], 200);
                }



            }
        }else{
            return response()->json([
                'status' => 'fail',
                'error' => 'Method Not Allowed',
            ], 405);
        }

    }
    // Your method to fetch balance from the real database
    private function fetchBalanceFromDatabase($login)
    {
        $user = User::select('user_id', 'balance','casino_bonus_account', 'currency', 'withdrawal')->where('user_id', $login)->first();
        if($user){
            Log::info("user in fetchBalanceFromDatabase");
            Log::info($user);
            return $user;
        }
        return null;
    }

}
