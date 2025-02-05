<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use App\Models\User;
use App\Models\Admin;
use App\Models\Bonuse;
use App\Models\Deposit;
use App\Models\Gateway;
use App\Models\TransectionProviders;
use App\Constants\Status;
use App\Models\Commision;
use App\Models\DepositBonusTracker;
use App\Models\UserBonusList;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Gateway\PaymentController;
use App\Notifications\TramcardSendNotification;
use Illuminate\Support\Facades\Log;

class DepositController extends Controller
{
    public function pending()
    {
        $pageTitle = 'Pending Deposits';
        $deposits  = $this->depositData('pending');
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function approved()
    {
        $pageTitle = 'Approved Deposits';
        $deposits  = $this->depositData('approved');
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function successful()
    {
        $pageTitle = 'Successful Deposits';
        $deposits  = $this->depositData('successful');
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function rejected()
    {
        $pageTitle = 'Rejected Deposits';
        // dd($this->depositData('rejected'));
        $deposits  = $this->depositData('rejected');
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function initiated()
    {
        $pageTitle = 'Initiated Deposits';
        $deposits  = $this->depositData('initiated');
        return view('admin.deposit.log', compact('pageTitle', 'deposits'));
    }

    public function deposit()
    {
        $pageTitle   = 'Deposit History';
        $depositData = $this->depositData($scope = null, $summery = true);
        $deposits    = $depositData['data'];
        $summery     = $depositData['summery'];
        $successful  = $summery['successful'];
        $pending     = $summery['pending'];
        $rejected    = $summery['rejected'];
        $initiated   = $summery['initiated'];
        return view('admin.deposit.log', compact('pageTitle', 'deposits', 'successful', 'pending', 'rejected', 'initiated'));
    }

    protected function depositData($scope = null, $summery = false)
    {
        if ($scope) {
            $deposits = Deposit::$scope()->with(['user', 'gateway', 'transectionProviders', 'agent']);
        } else {
            $deposits = Deposit::with(['user', 'gateway', 'transectionProviders', 'agent']);
        }

        $roles = [1, 2, 3];

        if (in_array(Auth::user()->type, $roles)) {
            $deposits = $deposits->where('agent_id', Auth::user()->id)->searchable(['trx','method_trx_number', 'user:username'])->dateFilter();
        } else {
            $deposits = $deposits->searchable(['trx','method_trx_number', 'user:username'])->dateFilter();
        }

        $request = request();
        //vai method
        if ($request->method) {
            $method   = Gateway::where('alias', $request->method)->firstOrFail();
            $deposits = $deposits->where('method_code', $method->code);
        }
        if (!$summery) {
            return $deposits->orderBy('id', 'desc')->paginate(getPaginate());
        } else {
            $successful = clone $deposits;
            $pending    = clone $deposits;
            $rejected   = clone $deposits;
            $initiated  = clone $deposits;

            $successfulSummery = $successful->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
            $pendingSummery    = $pending->where('status', Status::PAYMENT_PENDING)->sum('amount');
            $rejectedSummery   = $rejected->where('status', Status::PAYMENT_REJECT)->sum('amount');
            $initiatedSummery  = $initiated->where('status', Status::PAYMENT_INITIATE)->sum('amount');



            return [
                'data'    => $deposits->orderBy('id', 'desc')->paginate(getPaginate()),
                'summery' => [
                    'successful' => $successfulSummery,
                    'pending'    => $pendingSummery,
                    'rejected'   => $rejectedSummery,
                    'initiated'  => $initiatedSummery,
                ],
            ];
        }
    }

    public function details($id)
    {
        $agents = [];
        $general   = gs();
        $deposit   = Deposit::where('id', $id)->with(['user', 'gateway', 'transectionProviders', 'agent'])->firstOrFail();
        $pageTitle = $deposit->user->username . ' requested ' . showAmount($deposit->amount) . ' ' . userCurrency();
        $details   = ($deposit->detail != null) ? json_encode($deposit->detail) : null;
        
        if($deposit->gateway == 'cash'){
            $agents = Admin::whereIn('type', [2,3])->where('country_code', $deposit->user->country_code)->where('currency', $deposit->user->currency)->where('admins.status',1)->get();
        }
        if($deposit->gateway == 'local'){
            $agents = TransectionProviders::where('id',  $deposit->transectionProviders->id)->with('agentFounds', function($q) use($deposit){
                $q->where('country_code', $deposit->user->country_code)->where('currency', $deposit->user->currency)->where('admins.status',1)->distinct();
            })->get();
            if(count($agents) > 0){
                $agents = $agents[0]->agentFounds;
            }
            
        }
        
        
        return view('admin.deposit.detail', compact('pageTitle', 'deposit', 'details', 'agents'));
    }

    public function approve($id)
    {
        DB::beginTransaction();
        try {
            $deposit = Deposit::with(['agent', 'user'])->where('id', $id)->where(function ($query) {
                $query->where('status', Status::PAYMENT_PENDING)
                    ->orWhere('status', Status::PAYMENT_REJECT);
            })->firstOrFail();
            
            
           
            $deposit->status = Status::PAYMENT_PENDING; // these 2 lines for reject to again approved 
            $deposit->save(); // these 2 lines for reject to again approved 
            
            $agent = Admin::find($deposit->agent->id);
            if ((float) $agent->balance < (float) $deposit->amount) {
                $notify[] = ['error', 'Insufficient balance, Please topup your balance.'];
                return back()->withNotify($notify);
            }

            $agent->decrement('balance', $deposit->amount);

            $this->depositBonusGoesUserBonusList($deposit->id);
            $this->bonusAmountUpdate($deposit->user_id, $deposit->id, 1);

            $commission = new Commision();
            $commission->user_id = $deposit->user_id;
            $commission->agent_id = $deposit->agent_id;
            $commission->deposit_id = $deposit->id;
            $commission->type = 'Deposit';
            $commission->commision = $agent->deposit_commission;
            $commission->amount = $deposit->amount;
            $commission->final_amount = ($deposit->amount * ($agent->deposit_commission / 100));
            $commission->save();
            $agent->increment('balance', ($deposit->amount * ($agent->deposit_commission / 100)));

            PaymentController::userDataUpdate($deposit, true);
             // Send Notification
             if($deposit->user->ref_by != 0 && $deposit->user->is_ref_claim == 0 && $deposit->user->is_ref_deposit_notify ==0 && $deposit->amount >= 300){
                 $userNotify = new UserNotification;
                 $userNotify->user_id = $deposit->user->ref_by;
                 $userNotify->title = "Congratulations! Your referral bonus 300". $deposit->user->currency." waiting for claim.";
                 $userNotify->url = "/user/bonus";
                 $userNotify->save();
                
                 $userNotify->notify(new TramcardSendNotification($userNotify));
                 
                 $notifyUpdate = User::where('id', $deposit->user->id)->first();
                 $notifyUpdate->is_ref_deposit_notify = 1;
                 $notifyUpdate->save();
             }
            
            
            DB::commit();
            
            try {
                $telegramText = '';
                $telegramText .= 'APPROVED
            Deposit Request No: ' . $deposit->trx . '
            Agent: ' . @$deposit->agent->name . '
            Payment number: ' . $deposit->payment_number . '
            Amount: ' . $deposit->amount . $deposit->method_currency . '
            Customer: ' . @$deposit->user->username . '
            ext_trn_id: ' . $deposit->method_trx_number . '
            আমরা শুধু ক্যাশ গ্রহণ করে থাকি';
                telegramNotification($telegramText);
            } catch (\Exception $r) {
                $notify[] = ['error', 'Telegram Issue'];
            }

            $notify[] = ['success', 'Deposit request approved successfully'];
            // Notify to user
            $userNotify = new UserNotification();
            $user = User::find($deposit->user_id);
            $userNotify->user_id = $user->id;
            $userNotify->title = "Deposit Approved";
            $userNotify->url = "/user/deposit/history";
            $userNotify->save();


            return to_route('admin.deposit.pending')->withNotify($notify);
        } catch (\Exception $e) {
            DB::rollBack();
            $notify[] = ['error', "Something went wrong"];
            return back()->withNotify($notify);
        }
    }
    
    
    public function depositRefund(Request $r, $id){
         DB::beginTransaction();
            try {
                $deposit = Deposit::where('id', $id)->with(['agent', 'user'])->where('status', Status::PAYMENT_SUCCESS)->firstOrFail();
                $deposit->admin_feedback = $r->message;
                $deposit->status = Status::PAYMENT_REJECT;
                $deposit->save();
        
                $agent = Admin::find($deposit->agent->id);
    
                $agent->increment('balance', $deposit->amount);
    
                $this->bonusAmountRefund($deposit->user_id, $deposit->id, 1);
    
                $commission = Commision::where('deposit_id', $deposit->id)->where('agent_id', $deposit->agent->id)->first();
                $commission->comment = 'Deposit Refunded';
                $commission->save();
                $agent->decrement('balance', ($deposit->amount * ($agent->deposit_commission / 100)));
                
                $user = User::find($deposit->user_id);
                $user->balance -= $deposit->amount;
                $user->save();
                DB::commit();
                $notify[] = ['success', 'Deposit successfully refunded'];

                // Notify to user
                $userNotify = new UserNotification();
                $userNotify->user_id = $user->id;
                $userNotify->title = "Deposit refunded please check your balance.";
                $userNotify->url = "/";
                $userNotify->save();


                return back()->withNotify($notify);
            }catch(\Exception $e){
                DB::rollBack();
                $notify[] = ['error', 'Something went wrong'];
                return back()->withNotify($notify);
            }
    }

    public function reject(Request $request)
    {
        $request->validate([
            'id'      => 'required|integer',
            'message' => 'required|string|max:255',
        ]);
        $deposit = Deposit::where('id', $request->id)->where('status', Status::PAYMENT_PENDING)->firstOrFail();
    
        $this->bonusAmountUpdate($deposit->user_id, $deposit->id, 3);

        $deposit->admin_feedback = $request->message;
        $deposit->status         = Status::PAYMENT_REJECT;
        $deposit->save();
        notify($deposit->user, 'DEPOSIT_REJECT', [
            'method_name'       => $deposit->transectionProviders->name ?? 'Cash-agent',
            'method_currency'   => $deposit->method_currency,
            'method_amount'     => showAmount($deposit->final_amo),
            'amount'            => showAmount($deposit->amount),
            'charge'            => showAmount($deposit->charge),
            'rate'              => showAmount($deposit->rate),
            'trx'               => $deposit->trx,
            'rejection_message' => $request->message,
        ]);

        $notify[] = ['success', 'Deposit request rejected successfully'];
        // Notify to user
        $user = User::find($deposit->user_id);
        $userNotify = new UserNotification();
        $userNotify->user_id = $user->id;
        $userNotify->title = "Deposit Rejected.";
        $userNotify->url = "/user/deposit/history";
        $userNotify->save();
        return to_route('admin.deposit.pending')->withNotify($notify);
    }

    // Agent deposit commission
    public function commission()
    {
        $pageTitle = 'Deposit Commissions';
        $comissions = Commision::with(['agent', 'user', 'deposit'])->whereNotNull('deposit_id')->latest();
        if (Auth::user()->hasRole('super-admin')) {
            $comissions = $comissions->paginate(10);
        } else {
            $comissions = $comissions->where('agent_id', Auth::id())->paginate(10);
        }
        return view('admin.deposit.commission', compact('pageTitle', 'comissions'));
    }



    // Deposit edit request
    public function requestDeposit(Request $r)
    {
        try {
            $r->validate([
                'id'      => 'required|integer',
                'amount' => 'required',
            ]);
            $deposit = Deposit::where('id', $r->id)->where('status', Status::PAYMENT_PENDING)->firstOrFail();
            $deposit->amount = $r->amount;
            $deposit->final_amo = $r->amount;
            $deposit->admin_feedback = $r->remark;
            $deposit->save();
            $notify[] = ['success', 'Change/Request Deposit Amount Successfully Complete.'];

            $telegramText = '';
            $telegramText .= 'DEPOSIT REQUEST UPDATE FROM CASH
            Deposit Request No: ' . $deposit->trx . '
            Agent: ' . @$deposit->agent->name . '
            Payment number: ' . $deposit->payment_number . '
            Amount: ' . $r->amount . $deposit->method_currency . '
            Customer: ' . @$deposit->user->username . '
            ext_trn_id: ' . $deposit->method_trx_number . '
            আমরা শুধু ক্যাশ গ্রহণ করে থাকি';
            telegramNotification($telegramText);
            return back()->withNotify($notify);
        } catch (\Exception $e) {
            $notify[] = ['error', 'Something went wrong.'];
            return back()->withNotify($notify);
        }
    }

    private function bonusAmountUpdate($UserId, $depositId, $status)
    {
        $bonus = Bonuse::where('deposit_id', $depositId)->first();
        if ($bonus != null) {
            $bonus->status = $status;
            $bonus->save();
            if ($status == 1) {
                $user = User::find($UserId);
                $user->bonus_account += $bonus->bonus_amount;
                $user->save();
            }
        }
    }
    
    private function depositBonusGoesUserBonusList($deposit){
        
        $findDeposit = DepositBonusTracker::where('deposit_id', $deposit)->where('status', 0)->first();
        if($findDeposit){
            $user = User::find($findDeposit->user_id);
            $existBonus = UserBonusList::where('user_id', $findDeposit->user_id)->first();
            if ($existBonus) {
                Log::info("has active bonus");
                // Notify to user
                $userNotify = new UserNotification();
                $userNotify->user_id = $findDeposit->user_id;
                $userNotify->title = "Deposit approved, but only one active bonus is allowed now.";
                $userNotify->url = "/";
                $userNotify->save();
            } else {
                Log::info("bonus given");
                $bonus = new UserBonusList();
                $bonus->user_id = $findDeposit->user_id;
                $bonus->type = 'deposit';
                $bonus->initial_amount = $findDeposit->initial_amount;
                $bonus->wager = 0;
                $bonus->wager_limit = $findDeposit->wager_limit;
                $bonus->rollover_limit = $findDeposit->rollover_limit;
                $bonus->min_bet_multi = $findDeposit->min_bet_multi;
                $bonus->minimum_odd = $findDeposit->minimum_odd;
                $bonus->game_type = $findDeposit->game_type;
                $bonus->currency = $findDeposit->currency;
                $bonus->valid_time = $findDeposit->valid_time;
                $bonus->duration = $findDeposit->duration;
                $bonus->duration_text = $findDeposit->duration_text;
                $bonus->save();
                
                // Update statur
                $findDeposit->status = 1;
                $findDeposit->save();
                
                if($bonus){
                    if($bonus->game_type==2){
                        $user->increment('casino_bonus_account', $findDeposit->initial_amount);
                    }
                    else{
                        $user->increment('bonus_account', $findDeposit->initial_amount);
                    }



                    $transaction               = new Transaction();
                    $transaction->user_id      = $user->id;
                    $transaction->amount       = $bonus->initial_amount;
//                    $transaction->post_balance = $user->balance + $bonus->initial_amount;
                    $transaction->trx_type     = '+';
                    $transaction->trx          = $findDeposit->deposit_id;
                    $transaction->transection_type          = 2;
                    $transaction->remark       = 'get bonus';
                    $transaction->details      = 'For get bonus';
                    $transaction->save();
                    // Notify to user
                    $userNotify = new UserNotification();
                    $userNotify->user_id = $findDeposit->user_id;
                    $userNotify->title = "Congratulations! You have to got " . $findDeposit->initial_amount . $findDeposit->currency . " deposit bonus for " . $findDeposit->duration_text;
                    $userNotify->url = "/user/bonus";
                    $userNotify->save();
                }
            }
        }

    }
    
     private function bonusAmountRefund($UserId, $depositId, $status)
    {
        $bonus = Bonuse::where('deposit_id', $depositId)->first();
        if ($bonus != null) {
            $bonus->status = $status;
            $bonus->save();
            if ($status == 1) {
                $user = User::find($UserId);
                $user->bonus_account -= $bonus->bonus_amount;
                $user->save();
            }
        }
    }
    
    public function agentChangeForDeposit(Request $request){
        try{
            DB::beginTransaction();
            $deposit = Deposit::find($request->deposit_id);
            if($deposit->status == 2 || $deposit->status ==3){
                $deposit->agent_id = $request->agent_id;
                $deposit->save();
            }
            if($deposit->status == 1){
                
                // Deposit approved process changed agent amount when change the agent
                $agent = Admin::find($deposit->agent_id);
                $agent->increment('balance', $deposit->amount);
                $agent->decrement('balance', ($deposit->amount * ($agent->deposit_commission / 100)));
                
                $commission = Commision::where('agent_id', $deposit->agent_id)->where('deposit_id', $deposit->id)->first();
               if ($commission) {
                    $commission->update(['amount' => 0, 'final_amount' => 0]);
                }
                
                $deposit->agent_id = $request->agent_id;
                $deposit->status = 2;
                $deposit->admin_feedback = 'Waiting for reapprove';
                $deposit->save();
            }
            DB::commit();
            
            $notify[] = ['success', 'Agent successfully changed'];
            return back()->withNotify($notify);
        } catch(\Exception $e){
            DB::rollBack();
            $notify[] = ['error', 'Something went wrong'];
            return back()->withNotify($notify);
        }
        // dd($deposit);
    }
    
    public function depositAdjustment(Request $request, $id){
        $request->validate([
            'adjustment_amount'      => 'required|numeric|min:0'
        ]);
         try{
            DB::beginTransaction();
            $deposit = Deposit::find($id);
            $deposit->amount = $request->adjustment_amount;
            $deposit->final_amo = $request->adjustment_amount;
            $deposit->save();
            
            $this->approve($deposit->id);
            DB::commit();
            
            $notify[] = ['success', 'Successfully changed'];
            return back()->withNotify($notify);
        } catch(\Exception $e){
            DB::rollBack();
            $notify[] = ['error', 'Something went wrong'];
            return back()->withNotify($notify);
        }
    }
}
