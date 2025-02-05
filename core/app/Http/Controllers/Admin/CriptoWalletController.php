<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CriptoWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CriptoWalletController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'Wallets';
        $wallets = CriptoWallet::latest()->paginate(10);
        $currency = [
            ['name' => 'USDT', 'value' => 'USDT'],
            ['name' => 'XRP', 'value' => 'XRP'],
            ['name' => 'BTC', 'value' => 'BTC'],
            ['name' => 'TRX', 'value' => 'TRX'],
            ['name' => 'BNB', 'value' => 'BNB'],
        ];
        return view('admin.agent.wallet', compact('pageTitle', 'wallets', 'currency'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validation = Validator::make($request->all(), [
            'name'         => 'required',
            'wallet_number'     => 'required|unique:cripto_wallets',
            'currency' => 'required|array'
        ]);
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        } else {
            $currency = json_decode(json_encode($request->currency), true);
            $currencyes = [];
            foreach ($currency as $key => $c) {
                $currencyes[$key]['name'] = $c;
                $currencyes[$key]['value'] = $c;
            }
            CriptoWallet::create(
                [
                    'name' => $request->name,
                    'wallet_number' => $request->wallet_number,
                    'currency' => json_encode($currencyes)
                ]
            );
            $notify[] = ['success', "New wallet successfully added"];
            return back()->withNotify($notify);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = 'Wallet edit';
        $wallet = CriptoWallet::findOrFail($id);
        $currency = [
            ['name' => 'USDT', 'value' => 'USDT'],
            ['name' => 'XRP', 'value' => 'XRP'],
            ['name' => 'BTC', 'value' => 'BTC'],
            ['name' => 'TRX', 'value' => 'TRX'],
            ['name' => 'BNB', 'value' => 'BNB'],
        ];
        return view('admin.agent.wallet-edit', compact('pageTitle', 'wallet', 'currency'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validation = Validator::make($request->all(), [
            'name'         => 'required',
            'wallet_number'     =>  ['required', Rule::unique('cripto_wallets')->ignore($id)],
            'status' => 'required',
            'currency' => 'array|required'
        ]);
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        } else {
            $currency = json_decode(json_encode($request->currency), true);
            $currencyes = [];
            foreach ($currency as $key => $c) {
                $currencyes[$key]['name'] = $c;
                $currencyes[$key]['value'] = $c;
            }
            $wallet = CriptoWallet::findOrFail($id);
            $wallet->name = $request->name;
            $wallet->wallet_number = $request->wallet_number;
            $wallet->currency = $currencyes;
            $wallet->status = $request->status;
            $wallet->save();
            $notify[] = ['success', "Successfully updated"];
            return to_route('admin.agent.diposit.wallet.list')->withNotify($notify);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
