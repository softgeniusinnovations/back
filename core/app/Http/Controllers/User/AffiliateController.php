<?php

namespace App\Http\Controllers\user;

use App\Models\User;
use App\Models\Bonuse;
use App\Models\Deposit;
use App\Models\Website;
use App\Models\Currency;
use App\Models\Promotion;
use App\Models\ClickLog;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\AffiliatePromos;
use App\Models\AffiliateWebsite;
use App\Http\Controllers\Controller;
use App\Models\AffiliateCommissionTransaction;

class AffiliateController extends Controller{

    public function transactionDetails(Request $request){
        $pageTitle = 'Transaction Details';

        $dates = explode(' - ', $request->input('dates'));
        $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
        $to = isset($dates[1]) ? date('Y-m-d H:i:s', strtotime($dates[1])) : null;

        $transaction = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->latest()->paginate(getPaginate(10));
        return view($this->activeTemplate . 'user.affiliate.transaction', compact('pageTitle', 'transaction'));
    }

    public function affiliatepromo()
    {
        $pageTitle = 'Affiliate Promotions';
        $affiliates = AffiliatePromos::where('affliate_user_id', auth()->id())->latest()->paginate(getPaginate(10));
        return view($this->activeTemplate . 'user.affiliate.promotion.affiliatepromotions', compact('affiliates', 'pageTitle'));
    }

    public function summery(Request $request)
    {
        $pageTitle = 'Affiliate Summery';
        $userdata = User::where('id', auth()->id())->first();

        $dates = explode(' - ', $request->input('dates'));
        $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
        $to = isset($dates[1]) ? date('Y-m-d 23:59:59', strtotime($dates[1])) : null;

        $registrationCount = AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->get();

        $profit = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
            ->where('result', '1')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('amount');

        $loss = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
            ->where('result', '2')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('amount');


        $activepalyer = AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->count();

        $newBetter = AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->pluck("better_user_id");

        $newDepositor = Deposit::whereIn('user_id', $newBetter)
            ->where('status', 1)
            ->whereBetween('created_at', [$from ?? Carbon::now()->subDays(30), $to ?? Carbon::now()]);

        $allDepositAmount = Deposit::whereIn('user_id', $newBetter)
            ->where('status', 1)
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('final_amo');

        $bonus = Bonuse::where('user_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('bonus_amount');

        $currency = Currency::orderBy('currency_code', 'asc')->get();
        $website = AffiliateWebsite::where('affiliate_id', auth()->id())->get();
        $webCount = Website::where('user_id', auth()->id())
                    ->when($from && $to, function ($query) use ($from, $to) {
                        return $query->whereBetween('created_at', [$from, $to]);
                    })
                    ->get();

        $promotion = Promotion::where('user_id', auth()->id())->pluck("promo_code");
        
        $click = ClickLog::whereIn('promoCode', $promotion)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->get()->count();
        $data = [
            'registration' => $registrationCount->count(),
            'profit' => $profit,
            'loss' => $loss,
            'revenue' => $profit - $loss,
            'activepalyer' => $activepalyer,
            'bonus' => $bonus,
            'webs' =>  Website::where('user_id', auth()->id())->get(),
            'webCount' =>  $webCount->count(),
            'view' => $click,
            'newDepositor' => $newDepositor->groupBy('user_id')->get()->unique('user_id')->count(),
            'newDepositorAmount' => $newDepositor->sum('final_amo'),
            'allDepositAmount' => $allDepositAmount,
        ];
        return view($this->activeTemplate . 'user.affiliate.report.summery', compact('pageTitle', 'currency', 'website'))->with($data);
    }


    public function playerreport(Request $request)
    {
        $pageTitle = 'Player Report';
        $userdata = User::where('id', auth()->id())->first();
            $dates = explode(' - ', $request->input('dates'));
            $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
            $to = isset($dates[1]) ? date('Y-m-d 23:59:59', strtotime($dates[1])) : null;
            $currency = $request->input('currency');
            $country = $request->input('country');
            $userId = $request->input('playerId');
            $playerType = $request->input('playerType');

            $affiliatepromos = AffiliatePromos::where('affliate_user_id', auth()->id())
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->when($playerType == "new", function ($query) {
                    return $query->where('created_at', '>=', Carbon::now()->subDays(30));
                })
                ->when($playerType == "old", function ($query) {
                    return $query->where('created_at', '<=', Carbon::now()->subDays(30));
                })
                ->when($currency || $country || $userId, function ($query) use ($currency, $country, $userId) {
                    $query->whereHas('betterUser', function ($query) use ($currency, $country, $userId) {
                        if ($currency) {
                            $query->where('currency', $currency);
                        }
                        if ($country) {
                            $query->where('country_code', $country);
                        }
                        if ($userId) {
                            $query->where('user_id', $userId);
                        }
                    });
                });

        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $currency = Currency::orderBy('currency_code', 'asc')->get();
        $website = AffiliateWebsite::where('affiliate_id', auth()->id())->get();
        $data = [
            'affiliatepromos' => $affiliatepromos->get(),
        ];
        return view($this->activeTemplate . 'user.affiliate.report.playerreport', compact('pageTitle', 'currency', 'website', 'countries'))->with($data);
    }

    public function fullreport(Request $request)
    {
        $pageTitle = 'Full Report';
        $userdata = User::where('id', auth()->id())->first();
        $website = AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($request->website, function ($query, $website) {
                return $query->where('website', $website);
            });

        $websiteData = $website->get();
        // dd($websiteData->pluck('better_user_id')->toArray());
        // dd($website->groupBy('website')->get()->pluck('website'));

        if (!empty($request->all())) {
            $interval = $request->input('interval');
            $dateRange = $request->dates;
            $dates = explode(" - ", $dateRange);
            $from = date('Y-m-d H:i:s', strtotime($dates[0]));
            $to = date('Y-m-d H:i:s', strtotime($dates[1]));
            $currency = $request->input('currency');
        }
       // dd($website->groupBy('website')->get()->pluck('website', 'websiteId'));
        $data = [
            'website' => $website->groupBy('website')->get(),
            'website1' => $website->groupBy('website')->get()->pluck('website', 'websiteId'),
            'regCount' => $websiteData->count(),
        ];
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));
        $currency = Currency::orderBy('currency_code', 'asc')->get();
        return view($this->activeTemplate . 'user.affiliate.report.fullreport', compact('pageTitle', 'currency', 'countries'))->with($data);
    }

    private function getAffiliateData($website)
    {
        $data = [];

        foreach ($website as $key => $value) {
            $userIds = $value->affiliatePromos->pluck('betterUser.id')->toArray();

            $deposits = Deposit::whereIn('user_id', $userIds)
                ->where('status', 1)
                ->get();

            $newDepositors = $deposits->groupBy('user_id')->count();
            $totalAmount = $deposits->sum('final_amo');

            $totalBonus = User::whereIn('id', $userIds)->sum('bonus_account');

            $transactionSums = AffiliateCommissionTransaction::whereIn('user_id', $userIds)
                ->whereIn('result', [1, 2])
                ->groupBy('result')
                ->selectRaw('result, sum(amount) as sum')
                ->pluck('sum', 'result');

            $earn = $transactionSums[1] ?? 0;
            $loss = $transactionSums[2] ?? 0;

            $commission = $earn - $loss;

            $data[] = [
                'newDepositors' => $newDepositors,
                'totalAmount' => $totalAmount,
                'totalBonus' => $totalBonus,
                'commission' => $commission,
            ];
        }

        return $data;
    }

    public function affiliatelink(Request $request)
    {
        $pageTitle = 'Affiliate Link List';
        $currency = Currency::orderBy('currency_code', 'asc')->get();
        $website = AffiliateWebsite::where('affiliate_id', auth()->id())->get();
        $websiteList = Website::where('user_id', auth()->id())->get();
        $promo = Promotion::where('user_id', auth()->id())->first();
        return view($this->activeTemplate . 'user.affiliate.report.affiliatelink', compact('pageTitle', 'currency', 'website', 'websiteList','promo'));
    }

    public function affiliatelinkgenarate(Request $request)
    {

        $validatedData = $request->validate([
            'website' => 'required',
            'currency' => 'required',
            'campaign' => 'nullable',
            'landingpage' => 'nullable',
        ]);
        $str = $request->website;
        $parts = explode(" | ", $str);
        // If failed to validate then return error notification
        if (!$validatedData) {
            return back()->with('error', 'Validation failed');
        }
        $randId = rand(1000, 9999);
        $web                = new Website();
        $web->user_id       = auth()->user()->id;
        $web->website_link  = $parts[1];
        $web->aff_website   = $parts[0];
        $web->currency      = $request->currency;
        $web->campaign      = $request->campaign;
        $web->landing_page  = $request->landingpage;
        $web->subid         = $request->subid;
        $web->webId         = $randId;
        $web->status        =  1;
        $web->promo_id      =  Promotion::where('status', 1)->where("user_id", auth()->user()->id)->value('id');
        $web->linkgenarate  = url('/') . '?affi_link=' . Promotion::where('status', 1)->where("user_id", auth()->user()->id)->value('promo_code')
            . '&id=' . auth()->user()->user_id . '&site=' . AffiliateWebsite::where('id', $request->website)->value('websiteId') . '&ad=' . $randId;
        $web->save();

        return back()->with('success', 'Affiliate link generated successfully');
    }

    public function websiteList()
    {
        $pageTitle = 'Website List';
        $websiteList = AffiliateWebsite::where('affiliate_id', auth()->id())->get();
        return view($this->activeTemplate . 'user.affiliate.website.index', compact('pageTitle', 'websiteList'));
    }

    public function websiteAdd(Request $request)
    {
        $data = $request->validate([
            'website' => 'required',
            'type' => 'required'
        ]);
        if (!$data) {
            return back()->with('error', 'Validation failed');
        }

        $website = new AffiliateWebsite();
        $website->affiliate_id = auth()->id();
        $website->website = $request->website;
        $website->webtype = $request->type;
        $website->status = 1;
        $website->websiteId = AffiliateWebsite::latest()->first() ? AffiliateWebsite::latest()->first()->websiteId + 1 : 100001;
        $website->save();
        return response()->json(['success' => 'Application form submitted successfully.']);
    }

    public function websiteEdit($id)
    {
        $pageTitle = 'Edit Website';
        $website = AffiliateWebsite::find($id);
        return response()->json($website);
    }

    public function websiteUpdate(Request $request)
    {
        $data = $request->validate([
            'website_edit' => 'required',
            'type_edit' => 'required',
            'id' => 'required'
        ]);
        if (!$data) {
            return back()->with('error', 'Validation failed');
        }

        $website = AffiliateWebsite::find($request->id);
        $website->website = $request->website_edit;
        $website->webtype = $request->type_edit;
        $website->save();
        return response()->json(['success' => 'Application form submitted successfully.']);
    }
    public function websiteDelete(Request $request)
    {
        $data = $request->validate([
            'id' => 'required'
        ]);
        if (!$data) {
            return back()->with('error', 'Validation failed');
        }

        $website = AffiliateWebsite::find($request->id);
        $website->delete();
        return response()->json(['success' => 'Application form submitted successfully.']);
    }
}
