<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\{CommissionLog, NotificationLog, Transaction, UserLogin, };

class ReportController extends Controller
{
    public function transaction(Request $request)
    {
        $pageTitle    = 'Transaction Logs';
        $remarks      = Transaction::distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::searchable(['trx','user:username'])->filter(['trx_type','transection_type','remark'])->dateFilter()->orderBy('id','desc')->with('user')->paginate(getPaginate());

        return view('admin.reports.transactions', compact('pageTitle', 'transactions','remarks'));
    }

    public function loginHistory(Request $request)
    {
        $loginLogs = UserLogin::orderBy('id','desc')->with('user');
        $pageTitle = 'User Login History';
        $loginLogs = UserLogin::orderBy('id','desc')->searchable(['user:username'])->with('user')->paginate(getPaginate());

        return view('admin.reports.logins', compact('pageTitle', 'loginLogs'));
    }

    public function loginIpHistory($ip)
    {
        $pageTitle = 'Login by - ' . $ip;
        $loginLogs = UserLogin::where('user_ip',$ip)->orderBy('id','desc')->with('user')->paginate(getPaginate());
        return view('admin.reports.logins', compact('pageTitle', 'loginLogs','ip'));
    }

    public function notificationHistory(Request $request){
        $pageTitle = 'Notification History';
        $logs = NotificationLog::orderBy('id','desc')->searchable(['user:username'])->with('user')->paginate(getPaginate());

        return view('admin.reports.notification_history', compact('pageTitle','logs'));
    }

    public function emailDetails($id){
        $pageTitle = 'Email Details';
        $email = NotificationLog::findOrFail($id);
        return view('admin.reports.email_details', compact('pageTitle','email'));
    }

    public function referralCommissions()
    {
        $pageTitle = 'Referral Commissions';
        $logs      = CommissionLog::query();

        if (request()->search) {
            $search = request()->search;
            $logs = $logs->where(function ($q) use ($search) {
                $q->where('trx', 'like', "%$search%")->orWhereHas('byWho', function ($byWho) use ($search) {
                    $byWho->where('username', 'like', "%$search%");
                })->orWhereHas('toUser', function ($toUser) use ($search) {
                    $toUser->where('username', 'like', "%$search%");
                });
            });
        }

        if (request()->type) {
            $type = request()->type;
            $logs = $logs->where('type', $type);
        }

        $logs = $logs->with(['byWho', 'toUser'])->orderBy('id','desc')->dateFilter()->paginate(getPaginate());

        return view('admin.reports.referral_commissions', compact('pageTitle','logs'));
    }
}
