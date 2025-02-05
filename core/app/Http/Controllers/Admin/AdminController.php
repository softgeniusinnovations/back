<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Lib\CurlRequest;
use App\Models\Admin;
use App\Models\AdminNotification;
use App\Models\Bet;
use App\Models\Deposit;
use App\Models\SupportTicket;
use App\Models\Transaction;
use App\Models\TransectionProviders;
use App\Models\User;
use App\Models\UserLogin;
use App\Models\Withdrawal;
use App\Rules\FileTypeValidate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Telegram\Bot\Laravel\Facades\Telegram;
use Illuminate\Support\Facades\Storage;
use App\Models\Currency;
use App\Models\Threshold;
use Spatie\Activitylog\Facades\Activity;


class AdminController extends Controller
{

    // Agent create
    public function agentCreate()
    {
        $pageTitle = 'Agent Create';
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $providers = TransectionProviders::where('status', 1)->get();
        return view('admin.agent.create', compact('pageTitle', 'countries', 'mobileCode', 'providers'));
    }

    // Agent view
    public function agentsList()
    {
        $pageTitle = 'All Agents';
        $searchTerm = request()->query('search');
        $agents = Admin::whereIn('type', [1, 2, 3])
            ->where(function ($query) use ($searchTerm) {
                $query->orWhere('name', 'like', '%' . $searchTerm . '%')
                    ->orWhere('email', 'like', '%' . $searchTerm . '%')
                    ->orWhere('identity', 'like', '%' . $searchTerm . '%')
                    ->orWhere('username', 'like', '%' . $searchTerm . '%')
                    ->orWhere('phone', 'like', '%' . $searchTerm . '%');
            })
            ->with('transectionProviders')
            ->orderBy('status', 'desc')
            ->orderBy('id', 'desc')->paginate(10);
        // dd($agents->toSql());
        return view('admin.agent.index', compact('pageTitle', 'agents'));
    }

    //Agent status change
    public function agentStatusChange($id)
    {
        $agent = Admin::findOrFail($id);
        $agent->update([
            "status" => !$agent->status
        ]);
        $notify[] = ['success', 'Status Updated'];
        return back()->withNotify($notify);
    }

    //Agent Transection provider status change
    public function agentTransectionProviderStatusChange(Request $request)
    {
        $provider = DB::table('admin_transection_providers')->find($request->id);
        if ($provider) {
            DB::table('admin_transection_providers')
                ->where('id', $request->id)
                ->update([
                    'status' => !$provider->status,
                ]);
            return response()->json(['status' => 'success', 'message' => 'Status Updated']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Record not found']);
        }
    }
    
    
    // Bettors List
    public function bettors(Request $r){
        $bettors = User::select('firstname', 'lastname', 'balance', 'username', 'user_id', 'country_code', 'currency', 'bonus_account')
        ->where('user_id', $r->bettor_id)->get()->makeVisible(['balance']);
        return response()->json($bettors);
        
    }
    
    // Make Bettor Deposit view
    public function makeBettorDepositPage(){
        $pageTitle = 'Make Bettor Deposit';
        return view('admin.bettor.deposit', compact('pageTitle'));
    }
    
    // Make Bettor Deposit 
    public function makeBettorDeposit(Request $request){
        $validate = $request->validate([
            'depositor_name'   => 'nullable',
            'amount'   => 'required|numeric|gt:299|lt:20001',
            'bettor' => 'required|numeric',
        ]);
        
            
        $userExists = User::where('user_id', $request->bettor)->first();
        if($userExists){
            $data = new Deposit();
            $data->method_trx_number = getTrx();
            $data->user_id = $userExists->id;
            $data->agent_id = Auth::user()->id;
            $data->gateway = 'cash';
            $data->amount = $request->amount;
            $data->depositor_name = $request->depositor_name;
            $data->method_currency = $userExists->currency;
            $data->btc_amo         = 0;
            $data->btc_wallet      = "";
            $data->final_amo = $request->amount;
            $data->trx = getTrx();
            $data->status = 2;
            if($data->save()){
                
                
                activity()
                    ->performedOn($userExists) 
                    ->causedBy(Auth::user()) 
                    ->inLog('Deposit')
                    ->withProperties([
                        // 'type' => $request->type,
                        'amount' => $request->amount,
                        // 'old_balance' => $oldBalance,
                        // 'new_balance' => $newBalance,
                        'trx' => $data->trx,
                    ])
                    ->event('Deposit Balance Update')
                    ->log('Deposit Balance has been ' . 'increased' . ' by ' . $request->amount . '.');

                
                
                
                
                
                $telegramText = '';
                if ($data->status == 2) {
                    $amount = $data->amount . $data->method_currency;
                    $telegramText .= 'DEPOSIT REQUEST
                    Deposit Request No: ' . $data->trx . '
                    Agent: ' . @$data->agent->name . '
                    Payment number: ' . $data->payment_number . '
                    Amount: ' . $amount . '
                    Customer: ' . @$data->user->username . '
                    ext_trn_id: ' . $data->method_trx_number . '
                    ';
                    $telegramText .= 'আমরা শুধু ক্যাশ গ্রহণ করে থাকি';
                    try {
                        telegramNotification($telegramText);
                    } catch (\Exception $e) {
                        $notify[] = ['error', 'Telegram issues'];
                        return back()->withNotify($notify);
                    }
                    if ($data->agent_id != 1 && $data->agent->telegram_link) {
                        if ($data->agent->bot_token && $data->agent->channel_id) {
                            try {
                                $client = new Client();
                                $response = $client->get("https://api.telegram.org/bot{$data->agent->bot_token}/sendMessage", [
                                    'query' => [
                                        'chat_id' => $data->agent->channel_id,
                                        'text' => $telegramText,
                                    ],
                                ]);

                                $responseData = json_decode($response->getBody(), true);
                                if ($responseData['ok']) {
                                    if($data->gateway !='local' ){
                                        return redirect('https://' . $data->agent->telegram_link);
                                    }
                                } else {
                                    $notify[] = ['error', 'No direction'];
                                    return back()->withNotify($notify);
                                }
                            } catch (\Exception $e) {
                                $notify[] = ['error', 'Telegram issue'];
                                return back()->withNotify($notify);
                            }
                        }
                    }
                }
                $notify[] = ['success', 'Deposit has been under processing. Please wait for confirmation'];
                return back()->withNotify($notify);
            }
        }
        else{
            $notify[] = ['error', 'No user found'];
            return back()->withNotify($notify);
        }
        
        
        $notify[] = ['error', 'Something went wrong'];
        return back()->withNotify($notify);
        
    }
    
    
    // Make Bettor Withdraw view
    public function makeBettorWithdrawPage(){
         $pageTitle = 'Make Bettor Withdraw';
        return view('admin.bettor.withdraw', compact('pageTitle'));
    }
    
    // Make Bettor Withdraw view
    public function makeBettorWithdraw(Request $request){
        $validate = $request->validate([
            'amount'   => 'required|numeric|gt:299|lt:20001',
            'bettor' => 'required|numeric',
        ]);
        $userExists = User::where('user_id', $request->bettor)->first();
        if($userExists){
            if ($request->amount > $userExists->balance) {
                $notify[] = ['error', 'Your request amount is larger then your current balance.'];
                return back()->withNotify($notify);
            }else{
               try{
                    DB::beginTransaction();
                    $withdraw = new Withdrawal();
                    $withdraw->user_id = $userExists->id;
                    $withdraw->agent_id = Auth::user()->id;
                    $withdraw->gateway = 'local';
                    $withdraw->amount = $request->amount;
                    $withdraw->currency = $userExists->currency;
                    $withdraw->final_amount = $request->amount;
                    $withdraw->trx = getTrx();
                    $withdraw->status = 2;
                    $withdraw->charge = 0;
                    $withdraw->withdraw_information = [];
                    $withdraw->save();
                    
                      activity()
                    ->performedOn($userExists) 
                    ->causedBy(Auth::user()) 
                    ->inLog('Withdraw')
                    ->withProperties([
                        // 'type' => $request->type,
                        'amount' => $request->amount,
                        // 'old_balance' => $oldBalance,
                        // 'new_balance' => $newBalance,
                        'trx' => $withdraw->trx,
                    ])
                    ->event('Withdraw Balance Update')
                    ->log('Withdraw Balance has been ' . 'updated' . ' by ' . $request->amount . '.');

                    
                    $userExists->balance  -=  $withdraw->amount;
                    $userExists->save();
                    
                    
                    $transaction = new Transaction();
                    $transaction->user_id = $withdraw->user_id;
                    $transaction->amount = $withdraw->amount;
                    $transaction->post_balance = $userExists->balance;
                    $transaction->charge = $withdraw->charge;
                    $transaction->trx_type = '-';
                    $transaction->details = showAmount(@$withdraw->final_amount) . ' ' . $withdraw->currency . ' Withdraw Via Cash agent';
                    $transaction->trx = $withdraw->trx;
                    $transaction->remark = 'withdraw';
                    $transaction->save();
                    DB::commit();
               } catch(\Exception $e){
                   DB::rollback();
                   $notify[] = ['error', 'Something went wrong'.$e->getMessage()];
                   return back()->withNotify($notify);
               }
                
                try {
                    $telegramText = '';
                    $amount = $withdraw->amount;
                    $telegramText .= 'WITHDRAW REQUEST
                            Withdraw Request No: ' . $withdraw->trx . '
                            Agent: ' . @$withdraw->agent->name . '
                            Amount: ' . $amount . $withdraw->currency . '
                            Customer: ' . $withdraw->user->username . '
                            ';
                    telegramNotification($telegramText);
        
                    if ($withdraw->agent_id != 1) {
                        if ($withdraw->agent->telegram_link) {
                            if ($withdraw->agent->bot_token && $withdraw->agent->channel_id) {
                                try {
                                    $client = new Client();
                                    $response = $client->get("https://api.telegram.org/bot{$withdraw->agent->bot_token}/sendMessage", [
                                        'query' => [
                                            'chat_id' => $withdraw->agent->channel_id,
                                            'text' => $telegramText,
                                        ],
                                    ]);
        
                                    $responseData = json_decode($response->getBody(), true);
                                    if ($responseData['ok']) {
                                        return redirect('https://' . $withdraw->agent->telegram_link);
                                    } else {
                                        $notify[] = ['error', 'No direction'];
                                        return back()->withNotify($notify);
                                    }
                                } catch (\Exception $e) {
                                    $notify[] = ['error', 'Telegram issue'];
                                    return back()->withNotify($notify);
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $notify[] = ['error', 'Telegram issues'];
                    return back()->withNotify($notify);
                }
            }
        }else{
            $notify[] = ['error', 'No user found'];
            return back()->withNotify($notify);
        }
        
    }

    //Agent details
    public function agentDetails($id)
    {
       $pageTitle = 'Agent Details';
       $agent = Admin::with(['transectionProviders', 'deposit', 'withdraw', 'agentDeposit'])->findOrFail($id);
       return view('admin.agent.details', compact('pageTitle','agent'));
    }

    // Agent register
    public function adminRegister(Request $request)
    {


        $roles = ['', 'agent', 'cash-agent', 'mob-agent', 'super-admin', 'affiliator', 'support', 'report', 'admin', 'sub-admin'];
        $syncProviders = [];
        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));
        $validate     = Validator::make($request->all(), [
            'name'         => 'required',
            'email'        => 'required|string|email|unique:admins',
            'mobile'       => 'required|regex:/^([0-9]*)$/|unique:admins,phone',
            'username'     => 'required|unique:admins|min:4',
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'type'         => 'required',
            'deposit_commision' => 'required',
            'withdraw_commision' => 'required',
            'providers' => 'required|array',
            'wallet_name' => 'required|array',
            'wallet_number' => 'required|array',
            'balance'      => 'required|numeric',
            'password' => 'required|min:8',
            'telegram_link' => 'required',
            'bot_token' => 'required',
            'chat_id' => 'required',
            'bot_name' => 'required',
            'file' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        } else {

            DB::beginTransaction();
            try {
                $maxIdentity = Admin::max('identity');
                
                if($request->file){
                    $file = $request->file('file');
                    $fileName = time() . '.' . $file->extension();
                }
                $admin = Admin::create(
                    [
                        'name' => $request->name,
                        'identity' => $maxIdentity + 1,
                        'file' => $fileName,
                        'email' => $request->email,
                        'phone' => $request->mobile_code . $request->mobile,
                        'username' => $request->username,
                        'country_code' => $request->country_code,
                        'type' => $request->type,
                        'password' => Hash::make($request->password),
                        'ver_code' => Str::random(6),
                        'address' => $request->type > 1 ? $request->address : '',
                        'telegram_link' => $request->telegram_link,
                        'bot_username' => $request->bot_name,
                        'bot_token' => $request->bot_token,
                        'channel_id' => $request->chat_id,
                        'balance' => $request->balance,
                        'deposit_commission' => $request->deposit_commision,
                        'withdraw_commission' => $request->withdraw_commision
                    ]
                );
                if($admin){
                    Storage::put('/public/agent/photo/' . $fileName, file_get_contents($file));
                }
                $admin->assignRole($roles[$request->type]);

                if ($request->type == 1) {
                    if (count($request->providers) > 0) {
                        foreach ($request->providers as $key => $provider) {
                            DB::table('admin_transection_providers')->insert(
                                [
                                    'admin_id' => $admin->id,
                                    'transection_provider_id' => (int) $provider,
                                    'wallet_name' => $request->wallet_name[$key],
                                    'mobile' => $request->wallet_number[$key],
                                    'comment' => $request->comments[$key],
                                    'status' => 1
                                ]
                            );
                        }
                    }

                    $admin->sendEmailVerificationNotification();
                }
                DB::commit();

                $notify[] = ['success', "New Admin successfully added"];
                return to_route('admin.agent.list')->withNotify($notify);
            } catch (\Exception $e) {
                DB::rollback();
                $notify[] = ['error', $e->getMessage()];
                return back()->withNotify($notify);
            }
        }
    }


    // Agent edit
    public function agentEdit($id)
    {
        $pageTitle = 'Agent edit';
        $agent = Admin::with('transectionProviders')->findOrFail($id);
        $info       = json_decode(json_encode(getIpInfo()), true);
        $mobileCode = @implode(',', $info['code']);
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $providers = TransectionProviders::where('status', 1)->get();
        return view('admin.agent.edit', compact('pageTitle', 'agent', 'countries', 'mobileCode', 'providers'));
    }

    // Agent Update
    public function agentUpdate(Request $request, $id)
    {
        $roles = ['', 'agent', 'cash-agent', 'mob-agent', 'super-admin', 'affiliator', 'support', 'report', 'admin', 'sub-admin'];
        $syncProviders = [];
        $countryData  = (array) json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryCodes = implode(',', array_keys($countryData));
        $mobileCodes  = implode(',', array_column($countryData, 'dial_code'));
        $countries    = implode(',', array_column($countryData, 'country'));
        $validate     = Validator::make($request->all(), [
            'name'         => 'required',
            'email'        => ['required', 'email', 'string', Rule::unique('admins')->ignore($id)],
            'mobile'       => 'required|regex:/^([0-9]*)$/|unique:admins,phone',
            'mobile_code'  => 'required|in:' . $mobileCodes,
            'country_code' => 'required|in:' . $countryCodes,
            'country'      => 'required|in:' . $countries,
            'type'         => 'required',
            'deposit_commision' => 'required',
            'withdraw_commision' => 'required',
            'providers' => 'required|array',
            'wallet_name' => 'required|array',
            'wallet_number' => 'required|array',
            'telegram_link' => 'required',
            'bot_token' => 'required',
            'chat_id' => 'required',
            'bot_name' => 'required',
            'balance'=> 'required|numeric',
            'file' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);
        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        } else {

            DB::beginTransaction();
            try {
                $admin = Admin::findOrFail($id);
                if ($request->file) {
                    $file = $request->file('file');
                    $fileName = time() . '.' . $file->extension();
                    $previousFile = $admin->file;
                    $admin->file = $fileName;
                }
                $admin->name = $request->name;
                $admin->email = $request->email;
                $admin->phone = $request->mobile_code . $request->mobile;
                $admin->country_code = $request->country_code;
                $admin->type = $request->type;
                $admin->address =  $request->address;
                $admin->deposit_commission = $request->deposit_commision;
                $admin->withdraw_commission = $request->withdraw_commision;
                $admin->telegram_link = $request->telegram_link;
                $admin->bot_token = $request->bot_token;
                $admin->bot_username = $request->bot_name;
                $admin->channel_id = $request->chat_id;
                $admin->balance = $request->balance;
                if($admin->save()){
                    if ($request->file && Storage::exists('/public/agent/photo/' . $previousFile)) {
                        Storage::delete('/public/agent/photo/' . $previousFile);
                        Storage::put('/public/agent/photo/' . $fileName, file_get_contents($file));
                    }
                }

                $admin->syncRoles($roles[$request->type]);

                if ($request->type == 2 || $request->type == 3) {
                    DB::table('admin_transection_providers')->where('admin_id', $id)->update(['status' => 0]);
                }

                if ($request->type == 1) {
                    if (count($request->providers) > 0) {
                        DB::table('admin_transection_providers')->where('admin_id', $id)->delete();
                        foreach ($request->providers as $key => $provider) {
                            DB::table('admin_transection_providers')->insert(
                                [
                                    'admin_id' => $id,
                                    'transection_provider_id' => (int) $provider,
                                    'wallet_name' => $request->wallet_name[$key],
                                    'mobile' => $request->wallet_number[$key],
                                    'comment' => $request->comments[$key],
                                    'status' => 1
                                ]
                            );
                        }
                    }

                    $admin->sendEmailVerificationNotification();
                }
                DB::commit();

                $notify[] = ['success', "Successfully updated"];
                return to_route('admin.agent.list')->withNotify($notify);
            } catch (\Exception $e) {
                DB::rollback();
                $notify[] = ['error', $e];
                return back()->withNotify($notify);
            }
        }
    }


    public function dashboard()
    {
        $pageTitle = 'Dashboard for ' . auth()->user()->getRoleNames()->first();

        // User Info
        $widget['totalUsers']            = User::count();
        $widget['verifiedUsers']         = User::active()->count();
        $widget['emailUnverifiedUsers']  = User::emailUnverified()->count();
        $widget['mobileUnverifiedUsers'] = User::mobileUnverified()->count();
        $widget['pendingBet']            = Bet::pending()->count();
        $widget['wonBet']                = Bet::won()->count();
        $widget['loseBet']               = Bet::lose()->count();
        $widget['refundedBet']           = Bet::refunded()->count();
        $widget['pendingTicket']         = SupportTicket::where('status', Status::TICKET_OPEN)->count();

        // user Browsing, Country, Operating Log
        $userLoginData = UserLogin::where('created_at', '>=', Carbon::now()->subDay(30))->get(['browser', 'os', 'country']);

        $chart['user_browser_counter'] = $userLoginData->groupBy('browser')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_os_counter'] = $userLoginData->groupBy('os')->map(function ($item, $key) {
            return collect($item)->count();
        });
        $chart['user_country_counter'] = $userLoginData->groupBy('country')->map(function ($item, $key) {
            return collect($item)->count();
        })->sort()->reverse()->take(5);

        $deposit['total_deposit_amount']   = Deposit::when(auth()->user()->hasRole(['agent', 'cash-agent', 'mob-agent']), function ($q) {
            return $q->where('agent_id', auth()->user()->id);
        })->successful()->sum('amount');

        $deposit['total_deposit_pending']  = Deposit::when(auth()->user()->hasRole(['agent', 'cash-agent', 'mob-agent']), function ($q) {
            return $q->where('agent_id', auth()->user()->id);
        })->pending()->count();
        $deposit['total_deposit_charge']   = Deposit::successful()->sum('charge');

        $withdrawals['total_withdraw_amount']   = Withdrawal::when(auth()->user()->hasRole(['agent', 'cash-agent', 'mob-agent']), function ($q) {
            return $q->where('agent_id', auth()->user()->id);
        })->approved()->sum('amount');

        $withdrawals['total_withdraw_pending']  = Withdrawal::when(auth()->user()->hasRole(['agent', 'cash-agent', 'mob-agent']), function ($q) {
            return $q->where('agent_id', auth()->user()->id);
        })->pending()->count();
        $withdrawals['total_withdraw_charge']   = Withdrawal::approved()->sum('charge');

        $trxReport['date'] = collect([]);
        $plusTrx           = Transaction::where('trx_type', '+')->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw("SUM(amount) as amount, DATE_FORMAT(created_at,'%Y-%m-%d') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();

        $plusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $minusTrx = Transaction::where('trx_type', '-')->where('created_at', '>=', Carbon::now()->subDays(30))
            ->selectRaw("SUM(amount) as amount, DATE_FORMAT(created_at,'%Y-%m-%d') as date")
            ->orderBy('created_at')
            ->groupBy('date')
            ->get();

        $minusTrx->map(function ($trxData) use ($trxReport) {
            $trxReport['date']->push($trxData->date);
        });

        $trxReport['date'] = dateSorting($trxReport['date']->unique()->toArray());

        // Monthly Deposit & Withdraw Report Graph
        $report['months']                = collect([]);
        $report['deposit_month_amount']  = collect([]);
        $report['withdraw_month_amount'] = collect([]);

        $depositsMonth = Deposit::when(auth()->user()->hasRole(['agent', 'cash-agent', 'mob-agent']), function ($q) {
            return $q->where('agent_id', auth()->user()->id);
        })->where('created_at', '>=', Carbon::now()->subYear())
            ->where('status', Status::PAYMENT_SUCCESS)
            ->selectRaw("SUM( CASE WHEN status = " . Status::PAYMENT_SUCCESS . " THEN amount END) as depositAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();

        $depositsMonth->map(function ($depositData) use ($report) {
            $report['months']->push($depositData->months);
            $report['deposit_month_amount']->push(getAmount($depositData->depositAmount));
        });
        $withdrawalMonth = Withdrawal::when(auth()->user()->hasRole(['agent', 'cash-agent', 'mob-agent']), function ($q) {
            return $q->where('agent_id', auth()->user()->id);
        })->where('created_at', '>=', Carbon::now()->subYear())->where('status', Status::PAYMENT_SUCCESS)
            ->selectRaw("SUM( CASE WHEN status = " . Status::PAYMENT_SUCCESS . " THEN amount END) as withdrawAmount")
            ->selectRaw("DATE_FORMAT(created_at,'%M-%Y') as months")
            ->orderBy('created_at')
            ->groupBy('months')->get();
        $withdrawalMonth->map(function ($withdrawData) use ($report) {
            if (!in_array($withdrawData->months, $report['months']->toArray())) {
                $report['months']->push($withdrawData->months);
            }
            $report['withdraw_month_amount']->push(getAmount($withdrawData->withdrawAmount));
        });

        $months = $report['months'];

        for ($i = 0; $i < $months->count(); ++$i) {
            $monthVal = Carbon::parse($months[$i]);
            if (isset($months[$i + 1])) {
                $monthValNext = Carbon::parse($months[$i + 1]);
                if ($monthValNext < $monthVal) {
                    $temp           = $months[$i];
                    $months[$i]     = Carbon::parse($months[$i + 1])->format('F-Y');
                    $months[$i + 1] = Carbon::parse($temp)->format('F-Y');
                } else {
                    $months[$i] = Carbon::parse($months[$i])->format('F-Y');
                }
            }
        }
        
        $threshold = Threshold::where('currency', auth()->user()->currency)->first();

        return view('admin.dashboard', compact('pageTitle', 'widget', 'chart', 'deposit', 'withdrawals', 'depositsMonth', 'withdrawalMonth', 'months', 'trxReport', 'plusTrx', 'minusTrx','threshold'));
    }

    public function profile()
    {
        $pageTitle = 'Profile';
        $admin     = auth('admin')->user();
        return view('admin.profile', compact('pageTitle', 'admin'));
    }

    public function profileUpdate(Request $request)
    {
        $this->validate($request, [
            'name'  => 'required',
            'email' => 'required|email',
            'image' => ['nullable', 'image', new FileTypeValidate(['jpg', 'jpeg', 'png'])],
        ]);
        $user = auth('admin')->user();

        if ($request->hasFile('image')) {
            try {
                $old         = $user->image;
                $user->image = fileUploader($request->image, getFilePath('adminProfile'), getFileSize('adminProfile'), $old);
            } catch (\Exception $exp) {
                $notify[] = ['error', 'Couldn\'t upload your image'];
                return back()->withNotify($notify);
            }
        }

        $user->name  = $request->name;
        $user->email = $request->email;
        $user->save();
        $notify[] = ['success', 'Profile updated successfully'];
        return to_route('admin.profile')->withNotify($notify);
    }

    public function password()
    {
        $pageTitle = 'Password Setting';
        $admin     = auth('admin')->user();
        return view('admin.password', compact('pageTitle', 'admin'));
    }

    public function passwordUpdate(Request $request)
    {
        $this->validate($request, [
            'old_password' => 'required',
            'password'     => 'required|min:5|confirmed',
        ]);

        $user = auth('admin')->user();
        if (!Hash::check($request->old_password, $user->password)) {
            $notify[] = ['error', 'Password doesn\'t match!!'];
            return back()->withNotify($notify);
        }
        $user->password = bcrypt($request->password);
        $user->save();
        $notify[] = ['success', 'Password changed successfully.'];
        return to_route('admin.password')->withNotify($notify);
    }

    public function notifications()
    {
        if(auth()->user()->hasRole('super-admin')){
            $notifications = AdminNotification::orderBy('id', 'desc')->with('user')->paginate(getPaginate());
        }else{
            $notifications = AdminNotification::orderBy('id', 'desc')->with('user')->where('user_id', auth()->user()->id)->paginate(getPaginate());
        }
        
        $pageTitle     = 'Notifications';
        return view('admin.notifications', compact('pageTitle', 'notifications'));
    }

    public function notificationRead($id)
    {
        $notification          = AdminNotification::findOrFail($id);
        $notification->is_read = Status::YES;
        $notification->save();
        $url = $notification->click_url;
        if ($url == '#') {
            $url = url()->previous();
        }
        return redirect($url);
    }

    public function requestReport()
    {
        $pageTitle            = 'Your Listed Report & Request';
        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $url                  = "https://license.viserlab.com/issue/get?" . http_build_query($arr);
        $response             = CurlRequest::curlContent($url);
        $response             = json_decode($response);
        if ($response->status == 'error') {
            return to_route('admin.dashboard')->withErrors($response->message);
        }
        $reports = $response->message[0];
        return view('admin.reports', compact('reports', 'pageTitle'));
    }

    public function reportSubmit(Request $request)
    {
        $request->validate([
            'type'    => 'required|in:bug,feature',
            'message' => 'required',
        ]);
        $url = 'https://license.viserlab.com/issue/add';

        $arr['app_name']      = systemDetails()['name'];
        $arr['app_url']       = env('APP_URL');
        $arr['purchase_code'] = env('PURCHASECODE');
        $arr['req_type']      = $request->type;
        $arr['message']       = $request->message;
        $response             = CurlRequest::curlPostContent($url, $arr);
        $response             = json_decode($response);
        if ($response->status == 'error') {
            return back()->withErrors($response->message);
        }
        $notify[] = ['success', $response->message];
        return back()->withNotify($notify);
    }

    public function readAll()
    {
        AdminNotification::where('is_read', Status::NO)->update([
            'is_read' => Status::YES,
        ]);
        $notify[] = ['success', 'Notifications read successfully'];
        return back()->withNotify($notify);
    }

    public function downloadAttachment($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general   = gs();
        $title     = slug(gs('site_name')) . '- attachments.' . $extension;
        $mimetype  = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }


    // Admin list
    public function adminLists()
    {
        $pageTitle            = 'Admin';
        if (Auth::user()->hasRole('super-admin')) {
            $admins = Admin::with('roles')->whereNotIn('type', [1, 2, 3])->paginate(10);
        } else {
            $admins = Admin::with('roles')->whereNotIn('type', [1, 2, 3])->whereNotIn('id', [Auth::id()])->paginate(10);
        }
        $roles = Role::whereNotIn('id', [1, 2, 3, 4])->get();

        return view('admin.admin.index', compact('pageTitle', 'admins', 'roles'));
    }

    // Admin register
    public function adminCreate(Request $r)
    {
        $validation = Validator::make($r->all(), [
            'name'         => 'required',
            'username'     => 'required|unique:admins|min:4',
            'password' => 'required|min:8',
            'role' => 'required'
        ]);
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        } else {

            DB::beginTransaction();
            try {
                $admin = Admin::create(
                    [
                        'name' => $r->name,
                        'username' => $r->username,
                        'email' => $r->username . '@domain.com',
                        'type' => $r->role,
                        'password' => Hash::make($r->password),
                        'ver_code' => Str::random(6),
                        'balance' => 0,
                        'country_code' => 'BD'
                    ]
                );
                $role = Role::findOrFail($r->role);
                $admin->assignRole($role->name);

                DB::commit();

                $notify[] = ['success', "New Admin successfully added"];
                return to_route('admin.admin.list')->withNotify($notify);
            } catch (\Exception $e) {
                DB::rollback();
                $notify[] = ['error', $e];
                return back()->withNotify($notify);
            }
        }
    }

    // Admin edit
    public function adminEdit($id)
    {
        $admin = Admin::findOrFail($id);
        $pageTitle = "Admin edit";
        $roles = Role::whereNotIn('id', [2, 3, 4, 5])->get();
        return view('admin.admin.edit', compact('pageTitle', 'admin', 'roles'));
    }
    // Admin update
    public function adminUpdate(Request $r, $id)
    {
        $validation = Validator::make($r->all(), [
            'name'         => 'required',
            'username'     =>  ['required', 'min:4', Rule::unique('admins')->ignore($id)],
            'password' => 'nullable|min:8',
            'role' => 'required',
            'status' => 'required'
        ]);
        if ($validation->fails()) {
            return back()->withErrors($validation)->withInput();
        } else {

            DB::beginTransaction();
            try {
                $admin = Admin::findOrFail($id);
                $admin->name = $r->name;
                $admin->username = $r->username;
                if ($r->password) {
                    $admin->password =  Hash::make($r->password);
                }
                $admin->type = $r->role;
                $admin->status = $r->status;
                $admin->save();

                $role = Role::findOrFail($r->role);
                $admin->syncRoles($role->name);

                DB::commit();

                $notify[] = ['success', "Successfully updated"];
                return to_route('admin.admin.list')->withNotify($notify);
            } catch (\Exception $e) {
                DB::rollback();
                $notify[] = ['error', $e];
                return back()->withNotify($notify);
            }
        }
    }
    
    
    // Agent threshold value
    public function thresholdValue(){
        $pageTitle = 'Threshold value';
        $currency = Currency::get();
        $threshold = Threshold::latest()->first();
        return view('admin.agent.threshold', compact('pageTitle', 'currency', 'threshold'));
    }
    public function agentThreshold(Request $request, $id){
         $request->validate([
             'name' => 'required',
             'amount' => 'required|min:0|numeric',
             'currency' => 'required'
        ]);
        
        $threshold = Threshold::findOrFail($id);
        $threshold->name = $request->name;
        $threshold->amount = $request->amount;
        $threshold->currency = $request->currency;
        $threshold->save();
        $notify[] = ['success', 'Threshold value updated'];
        return back()->withNotify($notify);
    }
    
    
    // Agent password change 
    public function agentPasswordChange($id){
        $pageTitle = 'Password change';
        $agent = Admin::find($id);
        return view('admin.agent.password', compact('pageTitle', 'agent'));
    }
    
    public function agentPasswordChanged(Request $request, $id){
        $user = Admin::findOrFail($id);
        $request->validate([
            'password' => 'required|min:6|confirmed', // Add confirmation validation
        ]);
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password successfully changed'];
        return back()->withNotify($notify);
    }
    
    // Login agent dashboard by using id
    public function loginAgentDashboard($id){
       Auth::guard('admin')->loginUsingId($id);
       return to_route('admin.dashboard');
    }
    
    // Amount change
    public function changeAgentAmount($id){
        $agent = Admin::findOrFail($id);
        $pageTitle = "Change amount";
        return view('admin.agent.change-amount', compact('pageTitle', 'agent'));
    } 
//    public function changedAgentAmount(Request $request, $id){
//        $request->validate([
//            'amount' => 'required|numeric|min:0',
//            'type' => 'required',
//            'remark' => 'nullable'
//        ]);
//
//        try{
//            $agent = Admin::findOrFail($id);
//            if($request->type == '+'){
//                $agent->balance += $request->amount;
//            }
//            else if($request->type == '-'){
//                $agent->balance -= $request->amount;
//            }
//            else{
//                $agent->balance = $agent->balance;
//            }
//            if($agent->save()){
//                $adminNotification            = new AdminNotification();
//                $adminNotification->user_id   = $agent ? $agent->id : 1;
//                $adminNotification->title = $request->amount . ' ' . $agent->currency . ' Amount ' . ($request->type == '+' ? 'has been credited to '.$agent->identity.' account' : ' has been deducted from '.$agent->identity.' account') . ($request->remark ? ' for ' . $request->remark : '');
//                $adminNotification->click_url = route('admin.notifications');
//                $adminNotification->save();
//
//                $notify[] = ['success', 'Successfully amount changed'];
//                return back()->withNotify($notify);
//            }else{
//                $notify[] = ['error', 'Something went wrong!'];
//                return back()->withNotify($notify);
//            }
//        } catch(\Exception $e){
//            $notify[] = ['error', 'Something went wrong!'];
//            return back()->withNotify($notify);
//        }
//
//    }





    public function changedAgentAmount(Request $request, $id)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0',
            'type' => 'required',
            'remark' => 'nullable',
        ]);

        try {
            $agent = Admin::findOrFail($id);

            // Calculate the new balance
            $oldBalance = $agent->balance;
            $newBalance = $request->type == '+' ? $agent->balance + $request->amount : $agent->balance - $request->amount;

            $agent->balance = $newBalance;

            if ($agent->save()) {
                // Log the activity
                activity()
                    ->performedOn($agent) // Log the model
                    ->causedBy(Auth::user()) // Log the user performing the action
                    ->withProperties([
                        'type' => $request->type,
                        'amount' => $request->amount,
                        'old_balance' => $oldBalance,
                        'new_balance' => $newBalance,
                        'remark' => $request->remark,
                    ])
                    ->event('Balance Update')
                    ->log('Balance has been ' . ($request->type == '+' ? 'increased' : 'decreased') . ' by ' . $request->amount . '.');

                // Send the notification
                $adminNotification = new AdminNotification();
                $adminNotification->user_id = $agent ? $agent->id : 1;
                $adminNotification->title = $request->amount . ' ' . $agent->currency . ' Amount ' .
                    ($request->type == '+' ? 'has been credited to ' . $agent->identity . ' account' :
                        ' has been deducted from ' . $agent->identity . ' account') .
                    ($request->remark ? ' for ' . $request->remark : '');
                $adminNotification->click_url = route('admin.notifications');
                $adminNotification->save();

                $notify[] = ['success', 'Successfully amount changed'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'Something went wrong!'];
                return back()->withNotify($notify);
            }
        } catch (\Exception $e) {
            $notify[] = ['error', 'Something went wrong!'];
            return back()->withNotify($notify);
        }
    }


    /**
     * Retrieve the role name based on the role ID.
     */
    protected function getRoleName(int $roleId): string
    {
        return DB::table('roles')->where('id', $roleId)->value('name') ?? 'Unknown Role';
    }

}























