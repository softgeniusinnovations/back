<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApiController extends Controller
{
    public function getOdds(){
        // http://www.goalserve.com/getfeed/89b86665dc8348f5605008dc3da97a57/getodds/soccer?cat=soccer_10

        $url = "http://www.goalserve.com/getfeed/89b86665dc8348f5605008dc3da97a57/soccernew/live?json=1";

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        $json = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }

        curl_close($ch);

        $data = json_decode($json, true);
        dd($data['scores']['category']);
        return response()->json($data);
    }
}


