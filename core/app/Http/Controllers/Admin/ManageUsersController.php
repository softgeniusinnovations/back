<?php

namespace App\Http\Controllers\Admin;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Models\Bet;
use App\Models\CommissionLog;
use App\Models\Deposit;
use App\Models\NotificationLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\TramcardUser;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;

class ManageUsersController extends Controller {
    public function allUsers() {
        $pageTitle = 'All Bettors';
        $users     = $this->userData();
        return view('admin.users.list', compact('pageTitle', 'users'));
    }
    // Current weel active users
    public function currentWeekActiveUsers() {
        $pageTitle = 'Current Week Active Bettors';
        $users     = $this->currentWeekActionBettorsList();
        return view('admin.users.list', compact('pageTitle', 'users'));
    }
        
    protected function currentWeekActionBettorsList(){
        $currentDayOfWeek = Carbon::now()->dayOfWeek;
        $differenceToFriday = 5 - $currentDayOfWeek;
        $currentWeekStartDate = Carbon::now()->subDays($differenceToFriday)->startOfDay();
        $currentWeekEndDate = Carbon::now();
        
        $nowDate = Carbon::now()->subDays($differenceToFriday)->dayOfWeek;
        $users = User::with('loginLogs','loginLogsIp')->whereHas('bets', function($query) use ($currentWeekStartDate, $currentWeekEndDate) {
            $query->whereBetween('created_at', [$currentWeekStartDate, $currentWeekEndDate])
                  ->groupBy('user_id');
        })->searchable(['username', 'email','user_id','mobile','loginLogs:user_ip'])->orderBy('id', 'desc')->paginate(getPaginate());
        return $users;
    }
    
    // User password change
    public function changeUserPassword(Request $request, $user_id){
        $user = User::findOrFail($user_id);
        $request->validate([
            'password' => 'required|min:8|confirmed', // Add confirmation validation
        ]);
        $user->password = Hash::make($request->password);
        $user->save();
        $notify[] = ['success', 'Password successfully changed'];
        return back()->withNotify($notify);
    }

    public function activeUsers() {
        $pageTitle = 'Active Bettors';
        $users     = $this->userData('active');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function bannedUsers() {
        $pageTitle = 'Banned Bettors';
        $users     = $this->userData('banned');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailUnverifiedUsers() {
        $pageTitle = 'Email Unverified Bettors';
        $users     = $this->userData('emailUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycUnverifiedUsers() {
        $pageTitle = 'KYC Unverified Bettors';
        $users     = $this->userData('kycUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function kycPendingUsers() {
        $pageTitle = 'KYC Unverified Bettors';
        $users     = $this->userData('kycPending');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function emailVerifiedUsers() {
        $pageTitle = 'Email Verified Bettors';
        $users     = $this->userData('emailVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function mobileUnverifiedUsers() {
        $pageTitle = 'Mobile Unverified Bettors';
        $users     = $this->userData('mobileUnverified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function mobileVerifiedUsers() {
        $pageTitle = 'Mobile Verified Bettors';
        $users     = $this->userData('mobileVerified');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function usersWithBalance() {
        $pageTitle = 'Bettors with Balance';
        $users     = $this->userData('withBalance');
        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    protected function userData($scope = null) {
        if ($scope) {
            $users = User::with('loginLogs','loginLogsIp')->$scope();
        } else {
            $users = User::query()->with('loginLogs','loginLogsIp');
        }
        return $users->searchable(['username', 'email','user_id','mobile','loginLogs:user_ip'])->orderBy('id', 'desc')->paginate(getPaginate());
    }


    public function detail($id) {
        $user               = User::findOrFail($id);
        $pageTitle          = 'Bettor\'s Detail - ' . $user->username;
        $totalDeposit       = Deposit::where('user_id', $user->id)->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
        $totalWithdrawals   = Withdrawal::where('user_id', $user->id)->where('status', Status::PAYMENT_SUCCESS)->sum('amount');
        $totalTransaction   = Transaction::where('user_id', $user->id)->count();
        $totalBets          = Bet::where('user_id', $user->id)->count();
        $totalReferredUsers = User::where('ref_by', $user->id)->count();
        $totalReferralCom   = CommissionLog::where('to_id', $user->id)->sum('commission_amount');
        $countries          = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $betWinAmount       = Transaction::where('user_id', $user->id)->where('remark', 'bet_won')->sum('amount');
        $trumcard       = TramcardUser::where('user_id', $user->id)->sum('amount');

        return view('admin.users.detail', compact('pageTitle', 'user', 'totalDeposit', 'totalWithdrawals', 'totalTransaction', 'totalBets', 'totalReferredUsers', 'totalReferralCom', 'countries', 'betWinAmount','trumcard'));
    }

    public function bets(Request $request, $id) {
        $user      = User::findOrFail($id);
        $type      = 'single';
        $pageTitle = "$user->username - Bets";
        $bets      = Bet::where('user_id', $user->id);
        if ($request->search == 'win') {
            $bets->where('status', Status::BET_WIN);
        }
        $bets = $bets->with(['user', 'bets'])->paginate(getPaginate());
        return view('admin.bet.index', compact('pageTitle', 'type', 'bets'));
    }

    public function refereedUsers($id) {
        $user      = User::findOrFail($id);
        $pageTitle = "$user->username - Refereed Bettors";
        $users     = User::where('ref_by', $user->id)->latest()->paginate(getPaginate());

        return view('admin.users.list', compact('pageTitle', 'users'));
    }

    public function referralCommissions($id) {
        $user      = User::findOrFail($id);
        $pageTitle = "$user->username - Commission Logs";
        $logs      = CommissionLog::where('to_id', $user->id)->with(['byWho', 'toUser'])->latest()->paginate(getPaginate());

        return view('admin.reports.referral_commissions', compact('pageTitle', 'logs'));
    }

    public function kycDetails($id) {
        $pageTitle = 'KYC Details';
        $user      = User::findOrFail($id);
        return view('admin.users.kyc_detail', compact('pageTitle', 'user'));
    }

    public function kycApprove($id) {
        $user     = User::findOrFail($id);
        $user->kv = 1;
        $user->save();

        notify($user, 'KYC_APPROVE', []);

        $notify[] = ['success', 'KYC approved successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function kycReject($id) {
        $user = User::findOrFail($id);
        foreach ($user->kyc_data as $kycData) {
            if ($kycData->type == 'file') {
                fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
            }
        }
        $user->kv       = 0;
        $user->kyc_data = null;
        $user->save();

        notify($user, 'KYC_REJECT', []);

        $notify[] = ['success', 'KYC rejected successfully'];
        return to_route('admin.users.kyc.pending')->withNotify($notify);
    }

    public function update(Request $request, $id) {
        $user         = User::findOrFail($id);
        $countryData  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $countryArray = (array) $countryData;
        $countries    = implode(',', array_keys($countryArray));

        $countryCode = $request->country;
        $country     = $countryData->$countryCode->country;
        $dialCode    = $countryData->$countryCode->dial_code;

        $request->validate([
            'firstname' => 'required|string|max:40',
            'lastname'  => 'required|string|max:40',
            'email'     => 'required|email|string|max:40|unique:users,email,' . $user->id,
            'mobile'    => 'required|string|max:40|unique:users,mobile,' . $user->id,
            'country'   => 'required|in:' . $countries,
        ]);
        $user->mobile       = $dialCode . $request->mobile;
        $user->country_code = $countryCode;
        $user->firstname    = $request->firstname;
        $user->lastname     = $request->lastname;
        $user->email        = $request->email;
        $user->address      = [
            'address' => $request->address,
            'city'    => $request->city,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'country' => @$country,
        ];

        if($user->is_one_click_user){
            $user->oev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
            $user->omv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        }else{
            $user->ev = $request->ev ? Status::VERIFIED : Status::UNVERIFIED;
            $user->sv = $request->sv ? Status::VERIFIED : Status::UNVERIFIED;
        }
        $user->ts = $request->ts ? Status::ENABLE : Status::DISABLE;
        if (!$request->kv) {
            $user->kv = 0;
            if ($user->kyc_data) {
                foreach ($user->kyc_data as $kycData) {
                    if ($kycData->type == 'file') {
                        fileManager()->removeFile(getFilePath('verify') . '/' . $kycData->value);
                    }
                }
            }
            $user->kyc_data = null;
        } else {
            $user->kv = 1;
        }
        $user->save();

        $notify[] = ['success', 'User details updated successfully'];
        return back()->withNotify($notify);
    }

    public function addSubBalance(Request $request, $id) {
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'act'    => 'required|in:add,sub',
            'remark' => 'required|string|max:255',
        ]);

        $user   = User::findOrFail($id);
        $amount = $request->amount;
        $trx    = getTrx();

        $transaction = new Transaction();

        if ($request->act == 'add') {
            $user->balance += $amount;

            $transaction->trx_type = '+';
            $transaction->remark   = 'balance_add';

            $notifyTemplate = 'BAL_ADD';

            $notify[] = ['success', gs('cur_sym') . $amount . ' added successfully'];
        } else {
            if ($amount > $user->balance) {
                $notify[] = ['error', $user->username . ' doesn\'t have sufficient balance.'];
                return back()->withNotify($notify);
            }

            $user->balance -= $amount;

            $transaction->trx_type = '-';
            $transaction->remark   = 'balance_subtract';

            $notifyTemplate = 'BAL_SUB';
            $notify[]       = ['success', gs('cur_sym') . $amount . ' subtracted successfully'];
        }

        $user->save();

        $transaction->user_id      = $user->id;
        $transaction->amount       = $amount;
        $transaction->post_balance = $user->balance;
        $transaction->charge       = 0;
        $transaction->trx          = $trx;
        $transaction->details      = $request->remark;
        $transaction->save();

        notify($user, $notifyTemplate, [
            'trx'          => $trx,
            'amount'       => showAmount($amount),
            'remark'       => $request->remark,
            'post_balance' => showAmount($user->balance),
        ]);

        return back()->withNotify($notify);
    }

    public function login($id) {
        // Auth::loginUsingId($id); // previous code
          $user =  Auth::guard('web')->loginUsingId($id); // Changed code
          return to_route('home');
    }

    public function status(Request $request, $id) {
        $user = User::findOrFail($id);
        if ($user->status == Status::USER_ACTIVE) {
            $request->validate([
                'reason' => 'required|string|max:255',
            ]);
            $user->status     = Status::USER_BAN;
            $user->ban_reason = $request->reason;
            $notify[]         = ['success', 'Bettor banned successfully'];
        } else {
            $user->status     = Status::USER_ACTIVE;
            $user->ban_reason = null;
            $notify[]         = ['success', 'Bettor unbanned successfully'];
        }
        $user->save();
        return back()->withNotify($notify);
    }

    public function showNotificationSingleForm($id) {
        $user    = User::findOrFail($id);
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.users.detail', $user->id)->withNotify($notify);
        }
        $pageTitle = 'Send Notification to ' . $user->username;
        return view('admin.users.notification_single', compact('pageTitle', 'user'));
    }

    public function sendNotificationSingle(Request $request, $id) {
        $request->validate([
            'message' => 'required|string',
            'subject' => 'required|string',
        ]);

        $user = User::findOrFail($id);
        notify($user, 'DEFAULT', [
            'subject' => $request->subject,
            'message' => $request->message,
        ]);
        $notify[] = ['success', 'Notification sent successfully'];
        return back()->withNotify($notify);
    }

    public function showNotificationAllForm() {
        $general = gs();
        if (!$general->en && !$general->sn) {
            $notify[] = ['warning', 'Notification options are disabled currently'];
            return to_route('admin.dashboard')->withNotify($notify);
        }
        $notifyToUser = User::notifyToUser();
        $users        = User::active()->count();
        $pageTitle    = 'Notification to Verified Bettors';
        return view('admin.users.notification_all', compact('pageTitle', 'users', 'notifyToUser'));
    }

    public function sendNotificationAll(Request $request) {

        $validator = Validator::make($request->all(), [
            'message'                      => 'required',
            'subject'                      => 'required',
            'start'                        => 'required',
            'batch'                        => 'required',
            'being_sent_to'                => 'required',
            'user'                         => 'required_if:being_sent_to,selectedUsers',
            'number_of_top_deposited_user' => 'required_if:being_sent_to,topDepositedUsers|integer|gte:0',
            'number_of_days'               => 'required_if:being_sent_to,notLoginUsers|integer|gte:0',
        ], [
            'number_of_days.required_if'               => "Number of days field is required",
            'number_of_top_deposited_user.required_if' => "Number of top deposited user field is required",
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()->all()]);
        }

        $scope = $request->being_sent_to;
        $users = User::oldest()->active()->$scope()->skip($request->start)->limit($request->batch)->get();
        foreach ($users as $user) {
            notify($user, 'DEFAULT', [
                'subject' => $request->subject,
                'message' => $request->message,
            ]);
        }
        return response()->json([
            'total_sent' => $users->count(),
        ]);
    }

    public function list() {
        $query = User::active();

        if (request()->search) {
            $query->where(function ($q) {
                $q->where('email', 'like', '%' . request()->search . '%')->orWhere('username', 'like', '%' . request()->search . '%');
            });
        }
        $users = $query->orderBy('id', 'desc')->paginate(getPaginate());
        return response()->json([
            'success' => true,
            'users'   => $users,
            'more'    => $users->hasMorePages()
        ]);
    }

    public function notificationLog($id) {
        $user      = User::findOrFail($id);
        $pageTitle = 'Notifications Sent to ' . $user->username;
        $logs      = NotificationLog::where('user_id', $id)->with('user')->orderBy('id', 'desc')->paginate(getPaginate());
        return view('admin.reports.notification_history', compact('pageTitle', 'logs', 'user'));
    }
}
