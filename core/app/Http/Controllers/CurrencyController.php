<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;

class CurrencyController extends Controller
{
    public function index()
    {
        //add data from json
        $json = file_get_contents(public_path('assets/currency.json'));
        $json = json_decode($json, true);
        foreach ($json as $value) {
            $currency = new Currency();
            $currency->currency_code = $value['code'];
            $currency->currency_name = $value['name'];
            $currency->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Currency added successfully'
        ]);

    }
}
