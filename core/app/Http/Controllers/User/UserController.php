<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Bet;
use App\Models\Form;
use App\Models\News;
use App\Models\User;
use App\Models\Deposit;
use App\Constants\Status;
use App\Models\Promotion;
use App\Lib\FormProcessor;
use App\Models\Withdrawal;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\CommissionLog;
use App\Models\SupportTicket;
use App\Models\AffiliatePromos;
use App\Models\ReferralSetting;
use App\Lib\GoogleAuthenticator;
use App\Models\AffiliateWebsite;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AffiliateCommissionTransaction;
use App\Models\TramcardUser;
class UserController extends Controller
{

    public function home(Request $request)
    {
        
        $user = auth()->user();
        if ($user->is_affiliate != 1 && $user->profile_mode == 'better') {
            $pageTitle = 'Dashboard';
            $widget = $this->getWidgetData($user);
            $bets = $this->getBets($user);
            $transactions = Transaction::where('user_id', $user->id)->orderBy('id', 'desc')->limit(5)->get();
            $report = $this->getReport($user, $request);

            return redirect()->route('home');
        } else if ($user->is_affiliate == 1) {
            $yestrdayEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->whereDate('created_at', Carbon::yesterday())->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->whereDate('created_at', Carbon::yesterday())->sum('amount');
            $currentMonthEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->whereMonth('created_at', Carbon::now()->month)->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->whereMonth('created_at', Carbon::now()->month)->sum('amount');
            $thirtyDaysEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->whereDate('created_at', '>=', Carbon::now()->subDays(30))->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->whereDate('created_at', '>=', Carbon::now()->subDays(30))->sum('amount');
            $totalEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->sum('amount');

            $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

            $promoCodeUserDataMonthWise = AffiliatePromos::where('affliate_user_id', $user->id)
                ->whereYear('created_at', Carbon::now()->year)
                ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
                ->groupBy('month')
                ->pluck('count', 'month');

            $promoCodeUserData = array_fill_keys($months, 0);

            foreach ($promoCodeUserDataMonthWise as $month => $count) {
                $promoCodeUserData[$months[$month - 1]] = $count;
            }


            $data = [
                'user' => $user,
                'pageTitle' => 'Affiliate Dashboard',
                'promocodeUser' => AffiliatePromos::where('affliate_user_id', $user->id)->get(),
                'promoCode' => Promotion::where('user_id', $user->id)->get(),
                'yestrdayEarn' => $yestrdayEarn,
                'currentMonthEarn' => $currentMonthEarn,
                'thirtyDaysEarn' => $thirtyDaysEarn,
                'totalEarn' => $totalEarn,
                'promoCodeUserData' => $promoCodeUserData,
                'affiliateCommisionData' => $this->getCommitionData($user->id) ,
            ];
            return view($this->activeTemplate . 'user.affiliate.dashboard')->with($data);
        }
    }

    private function getCommitionData($userId)
    {
        $affiliateCommisionData = AffiliateCommissionTransaction::where('affiliate_id', $userId)
        ->selectRaw('DATE_FORMAT(created_at, "%Y-%m-%d") as day,
                     SUM(CASE WHEN result = 1 THEN amount WHEN result = 2 THEN -amount ELSE 0 END) as total_amount')
        ->groupBy('day')
        ->orderBy('day')
        ->pluck('total_amount', 'day')
        ->all();

    // Ensure every day has a value
    $days = range(1, Carbon::now()->daysInMonth);
    $days = array_map(function ($day) {
        return Carbon::now()->startOfMonth()->addDays($day - 1)->format('Y-m-d');
    }, $days);
    $affiliateCommisionData = array_replace(array_fill_keys($days, 0), $affiliateCommisionData);
            return $affiliateCommisionData;
    }

    private function getWidgetData($user)
    {
        return [
            'totalTransaction' => Transaction::where('user_id', $user->id)->count(),
            'totalTicket' => SupportTicket::where('user_id', $user->id)->count(),
            'totalDeposit' => Deposit::where('user_id', $user->id)->successful()->sum('amount'),
            'totalWithdraw' => Withdrawal::where('user_id', $user->id)->approved()->sum('amount'),
            'totalBet' => Bet::where('user_id', $user->id)->count(),
            'pendingBet' => Bet::where('user_id', $user->id)->pending()->count(),
            'wonBet' => Bet::where('user_id', $user->id)->won()->count(),
            'loseBet' => Bet::where('user_id', $user->id)->lose()->count(),
            'refundedBet' => Bet::where('user_id', $user->id)->refunded()->count(),
        ];
    }

    private function getBets($user)
    {
        return Bet::where('user_id', $user->id)->pending()->with(['bets' => function ($query) {
            $query->relationalData();
        }])->limit(5)->get();
    }

    private function getReport($user, $request)
    {
        $report['bet_return_amount'] = collect([]);
        $report['bet_stake_amount'] = collect([]);
        $report['bet_dates'] = collect([]);

        $startDate = now()->startOfDay();
        $endDate = now()->endOfDay();

        if ($request->date) {
            $date = explode('-', $request->date);
            $startDate = Carbon::parse($date[0])->startOfDay();
            $endDate = Carbon::parse($date[1])->endOfDay();
        }

        $totalBets = Bet::where('user_id', $user->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("SUM(CASE WHEN status = " . Status::BET_WIN . " AND amount_returned = " . Status::NO . " THEN return_amount ELSE 0 END) as return_amount")
            ->selectRaw("SUM(stake_amount) as stake_amount")
            ->selectRaw("DATE_FORMAT(created_at,'%Y-%m-%d') as dates")
            ->orderBy('created_at')
            ->groupBy('dates')
            ->get();

        $totalBets->map(function ($betData) use (&$report) {
            $report['bet_dates']->push($betData->dates);
            $report['bet_return_amount']->push(getAmount($betData->return_amount));
            $report['bet_stake_amount']->push(getAmount($betData->stake_amount));
        });

        return $report;
    }

    public function depositHistory(Request $request)
    {
        $pageTitle = 'Deposit History';
        $deposits  = auth()->user()->deposits()->searchable(['trx'])->with(['gateway', 'transectionProviders'])->orderBy('id', 'desc')->paginate(getPaginate());
        return view($this->activeTemplate . 'user.deposit_history', compact('pageTitle', 'deposits'));
    }

    public function show2faForm()
    {
        $ga        = new GoogleAuthenticator();
        $user      = auth()->user();
        $secret    = $ga->createSecret();
        $qrCodeUrl = $ga->getQRCodeGoogleUrl($user->username . '@' . gs('site_name'), $secret);
        $pageTitle = '2FA Setting';
        return view($this->activeTemplate . 'user.twofactor', compact('pageTitle', 'secret', 'qrCodeUrl'));
    }

    public function create2fa(Request $request)
    {
        $user = auth()->user();
        $this->validate($request, [
            'key'  => 'required',
            'code' => 'required',
        ]);
        $response = verifyG2fa($user, $request->code, $request->key);
        if ($response) {
            $user->tsc = $request->key;
            $user->ts  = 1;
            $user->save();

            $notify[] = ['success', 'Google authenticator activated successfully'];
            return back()->withNotify($notify);
        } else {
            $notify[] = ['error', 'Wrong verification code'];
            return back()->withNotify($notify);
        }
    }

    public function disable2fa(Request $request)
    {
        $this->validate($request, [
            'code' => 'required',
        ]);

        $user     = auth()->user();
        $response = verifyG2fa($user, $request->code);
        if ($response) {
            $user->tsc = null;
            $user->ts  = 0;
            $user->save();
            $notify[] = ['success', 'Two factor authenticator deactivated successfully'];
        } else {
            $notify[] = ['error', 'Wrong verification code'];
        }
        return back()->withNotify($notify);
    }

    public function transactions()
    {
        $pageTitle    = 'Transactions';
        $remarks      = Transaction::distinct('remark')->orderBy('remark')->get('remark');
        $transactions = Transaction::where('user_id', auth()->id())->searchable(['trx'])->filter(['trx_type', 'remark'])->orderBy('id', 'desc')->paginate(getPaginate());

        return view($this->activeTemplate . 'user.transactions', compact('pageTitle', 'transactions', 'remarks'));
    }

    public function kycForm()
    {
        if (auth()->user()->kv == 2) {
            $notify[] = ['error', 'Your KYC is under review'];
            return to_route('user.home')->withNotify($notify);
        }
        if (auth()->user()->kv == 1) {
            $notify[] = ['error', 'You are already KYC verified'];
            return to_route('user.home')->withNotify($notify);
        }

        $pageTitle = 'KYC Form';
        $form      = Form::where('act', 'kyc')->first();

        return view($this->activeTemplate . 'user.kyc.form', compact('pageTitle', 'form'));
    }

    public function kycData()
    {
        $user      = auth()->user();
        $pageTitle = 'KYC Data';
        return view($this->activeTemplate . 'user.kyc.info', compact('pageTitle', 'user'));
    }

    public function kycSubmit(Request $request)
    {
        $form           = Form::where('act', 'kyc')->first();
        $formData       = $form->form_data;
        $formProcessor  = new FormProcessor();
        $validationRule = $formProcessor->valueValidation($formData);
        $request->validate($validationRule);
        $userData       = $formProcessor->processFormData($request, $formData);
        $user           = auth()->user();
        $user->kyc_data = $userData;
        $user->kv       = 2;
        $user->save();

        $notify[] = ['success', 'KYC data submitted successfully'];
        return to_route('home')->withNotify($notify);
        // return back()->withNotify($notify);
    }

    public function attachmentDownload($fileHash)
    {
        $filePath  = decrypt($fileHash);
        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
        $general   = gs();
        $title     = slug($general->site_name) . '- attachments.' . $extension;
        $mimetype  = mime_content_type($filePath);
        header('Content-Disposition: attachment; filename="' . $title);
        header("Content-Type: " . $mimetype);
        return readfile($filePath);
    }

    public function userData()
    {
        $user = auth()->user();
        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }
        $pageTitle = 'User Data';
        return view($this->activeTemplate . 'user.user_data', compact('pageTitle', 'user'));
    }

    public function userDataSubmit(Request $request)
    {
        $user = auth()->user();
        if ($user->profile_complete == Status::YES) {
            return to_route('user.home');
        }
        $request->validate([
            'firstname' => 'required',
            'lastname'  => 'required',
        ]);
        $user->firstname = $request->firstname;
        $user->lastname  = $request->lastname;
        $user->address   = [
            'country' => @$user->address->country,
            'address' => $request->address,
            'state'   => $request->state,
            'zip'     => $request->zip,
            'city'    => $request->city,
        ];
        if (auth()->user()->is_affiliate == 1) {
            if($request->website != null){
                $website = new AffiliateWebsite();
                $website->affiliate_id = auth()->user()->id;
                $website->website = $request->website;
                $website->webtype = "website";
                $website->websiteId = AffiliateWebsite::latest()->first() ? AffiliateWebsite::latest()->first()->websiteId + 1 : 100001;
                $website->status = 1;
                $website->save();
            }
            if($request->youtube_link != null){
                $website = new AffiliateWebsite();
                $website->affiliate_id = auth()->user()->id;
                $website->website = $request->youtube_link;
                $website->webtype = "youtube";
                $website->websiteId = AffiliateWebsite::latest()->first() ? AffiliateWebsite::latest()->first()->websiteId + 1 : 100001;
                $website->status = 1;
                $website->save();
            }
        }

        $user->profile_complete = Status::YES;
        $user->save();

        $notify[] = ['success', 'Registration process completed successfully'];
        // return to_route('user.home')->withNotify($notify);
        return back()->withNotify($notify);
    }

    public function referralCommissions(Request $request)
    {
        $request->validate([
            'type' => 'nullable|in:deposit,bet,win',
        ]);

        $logs = CommissionLog::query();
        if ($request->type) {
            $type = $request->type;
            $logs = $logs->where('type', $request->type);
        } else {
            $type = 'deposit';
            $logs = $logs->where('type', 'deposit');
        }
        $logs      = $logs->where('to_id', auth()->id())->with('byWho')->orderBy('id', 'desc')->paginate(getPaginate());
        $pageTitle = 'Referral Commissions';
        return view($this->activeTemplate . 'user.referral.commission', compact('pageTitle', 'logs', 'type'));
    }

    public function myRef()
    {
        $pageTitle = 'My Referred Users';
        $maxLevel  = ReferralSetting::max('level');
        $relations = [];
        for ($label = 1; $label <= $maxLevel; $label++) {
            $relations[$label] = (@$relations[$label - 1] ? $relations[$label - 1] . '.allReferrals' : 'allReferrals');
        }
        $user = auth()->user()->load($relations);
        return view($this->activeTemplate . 'user.referral.users', compact('pageTitle', 'maxLevel', 'user'));
    }

    public function myRefLink(){
        $pageTitle = 'My Referral List';
        $user = User::where("id", auth()->user()->id)->first();
        return view($this->activeTemplate . 'user.referral.referral', compact('pageTitle', 'user'));
    }

    public function setProfileMode(Request $request)
    {
        $user = User::find($request->id);
        $user->profile_mode = $request->mode;
        $user->update();

        $data = User::find(auth()->user()->id);
        if ($data->profile_mode == 'better') {
            return redirect()->route('home');
        } elseif ($data->profile_mode == 'affiliate' && $data->is_affiliate == 1) {
            return redirect()->route('user.home');
        }
    }

    public function oneTimePassDismiss(Request $request)
    {

        $user = User::find($request->id);
        $user->one_time_pass = null;
        $user->update();
        return response()->json(['success' => 'Profile status changed successfully.']);
    }

    public function affiliateApplicationForm()
    {
        $pageTitle = "Application Form";
        $applicationForm = DB::table('applicationforms')->where('user_id', auth()->id())->get();
        return view($this->activeTemplate . 'user.ApplicationForm', compact('pageTitle', 'applicationForm'));
    }

    public function affiliateApplicationFormSubmit(Request $request)
    {
        $request->validate([
            'description' => 'required',
        ]);

        DB::table('applicationforms')->insert([
            'user_id' => auth()->id(),
            'description' => $request->description,
            'website' => $request->website,
            'status' => 0,
            'is_approved' => 0,
            'created_at' => Carbon::now(),
        ]);

        return response()->json(['success' => 'Application form submitted successfully.']);
    }
    
    
    // User tramcards
    public function allTramcards(){
        $pageTitle = 'Tramcards';
        $tramcard = TramcardUser::with('tramcard')->where('user_id', auth()->user()->id)->first();
        return view($this->activeTemplate . 'user.tramcard', compact('pageTitle', 'tramcard'));
    }
    
    
    //Claim trmacard
    public function tramCardClaim(){
        try{
            DB::beginTransaction();
            $tramcard = TramcardUser::where('user_id', auth()->user()->id)->where('is_win', 1)->first();
            if($tramcard){
                $user = User::where('id', auth()->user()->id)->first();
                $user->withdrawal += $tramcard->amount;
                $user->save();
                
                $tramcard->amount = 0;
                $tramcard->is_win = 0;
                $tramcard->save();
                DB::commit();
                $notify[] = ['success', 'Congratulations. You tramcard amount goes to withdrawal fund.'];
                return back()->withNotify($notify);
            }else{
                $notify[] = ['error', 'There are no active tramcard or You can not pass the all rules.'];
                return back()->withNotify($notify);
            }
        } catch(\Exception $e){
             DB::rollback(); 
             $notify[] = ['error', 'Something went wrong.'];
             return back()->withNotify($notify);
        }
    }
}
