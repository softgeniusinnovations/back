<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AgentDeposit;
use App\Models\Admin;
use App\Models\CriptoWallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AgentDepositController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'Agent Deposit';
        $deposits = AgentDeposit::with(['agent', 'wallet'])->latest();
        if (Auth::user()->hasRole('super-admin')) {
            $deposits = $deposits->paginate(10);
        } else {
            $deposits = $deposits->where('agent_id', Auth::id())->paginate(10);
        }

        return view('admin.agent.deposit.index', compact('pageTitle', 'deposits'));
    }
    
    public function depositStatus($status){
        $value = ['Rejected'=>0, 'Pending'=>1, 'Approved'=>2, 'Back'=>3];
        $pageTitle = $status . ' Deposits';
        $deposits = AgentDeposit::with(['agent', 'wallet'])->where('status', $value[$status])->latest();
        if (Auth::user()->hasRole('super-admin')) {
            $deposits = $deposits->paginate(10);
        } else {
            $deposits = $deposits->where('agent_id', Auth::id())->paginate(10);
        }
        return view('admin.agent.deposit.index', compact('pageTitle', 'deposits'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Make deposit';
        $wallet = Cache::get('walletData');
        if ($wallet) {
            $wallet = $wallet;
        } else {
            $wallet = CriptoWallet::inRandomOrder()->limit(1)->where('status', 1)->first();
            Cache::put('walletData', $wallet, 60);
            $wallet = $wallet;
        }
        // dd($wallet);
        return view('admin.agent.deposit.create', compact('pageTitle', 'wallet'));
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
            'depositor_wallet_number'     => 'required',
            'deposit_trx'     => 'required|unique:agent_deposits',
            'amount' => 'required|min:0|numeric',
            'deposit_currency' => 'required|in:USDT,XRP,BTC,TRX,BNB',
            'wallet_id' => 'required',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:1024',
        ]);
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        } else {
            $file = $request->file('file');
            $fileName = time() . '.' . $file->extension();

            $deposit = new AgentDeposit;
            $deposit->agent_id = Auth::id();
            $deposit->wallet_id = $request->wallet_id;
            $deposit->currency = userCurrency();
            $deposit->trx = getTrx();
            $deposit->file = $fileName;
            $deposit->amount = $request->amount;
            $deposit->deposit_trx = $request->deposit_trx;
            $deposit->depositor_account = $request->depositor_wallet_number;
            $deposit->deposit_currency = $request->deposit_currency;
            $deposit->save();
            Storage::put('/public/agent/transactions/' . $fileName, file_get_contents($file));
            Cache::forget('walletData');
            $notify[] = ['success', "Deposit waiting for admin approvement"];
            return to_route('admin.agent.deposit.list')->withNotify($notify);
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
        $pageTitle = 'Deposit Details';
        $deposit = AgentDeposit::with(['agent', 'wallet'])->findOrFail($id);
        return view('admin.agent.deposit.details', compact('pageTitle', 'deposit'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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
        //
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

    public function approve(Request $request, $id)
    {
        $deposit = AgentDeposit::findOrFail($id);
        DB::beginTransaction();
        try {
            $deposit->rate = $request->rate;
            $deposit->status = 2;
            $deposit->feedback = 'Admin has beed approved this deposit';
            $deposit->save();

            $agent = Admin::findOrFail($deposit->agent_id);
            $agent->increment('balance', $deposit->amount * $request->rate);
            DB::commit();

            $notify[] = ['success', "Approved"];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollback();
            $notify[] = ['error', $e];
            return back()->withNotify($notify);
        }
    }

    public function reject(Request $request)
    {
        $deposit = AgentDeposit::findOrFail($request->id);
        DB::beginTransaction();
        try {
            $deposit->status = 0;
            $deposit->feedback = $request->message;
            $deposit->save();

            DB::commit();

            $notify[] = ['success', "Deposit Rejected"];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollback();
            $notify[] = ['error', $e];
            return back()->withNotify($notify);
        }
    }


    public function back(Request $request)
    {
        $deposit = AgentDeposit::findOrFail($request->id);
        DB::beginTransaction();
        try {
            $agent = Admin::findOrFail($deposit->agent_id);
            $agent->decrement('balance', $deposit->amount * $deposit->rate);

            $deposit->rate = 1;
            $deposit->status = 3;
            $deposit->feedback = $request->message;
            $deposit->save();

            DB::commit();

            $notify[] = ['success', "Deposit Backed"];
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollback();
            $notify[] = ['error', $e];
            return back()->withNotify($notify);
        }
    }
}
