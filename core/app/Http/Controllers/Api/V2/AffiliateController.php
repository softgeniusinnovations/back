<?php

namespace App\Http\Controllers\Api\V2;

use App\Constants\Status;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\AffiliateLinkGenerateStore;
use App\Http\Requests\Api\CreateTwoFactore;
use App\Http\Requests\Api\DisableTwoFactore;
use App\Http\Requests\Api\KycFormSubmit;
use App\Http\Requests\Api\UserChangePassword;
use App\Http\Requests\Api\UserProfileUpdate;
use App\Http\Resources\PromotionsCollection;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserReferralsCollection;
use App\Lib\GoogleAuthenticator;
use App\Models\AffiliateCommissionTransaction;
use App\Models\AffiliatePromos;
use App\Models\AffiliateWebsite;
use App\Models\Bet;
use App\Models\Bonuse;
use App\Models\ClickLog;
use App\Models\Currency;
use App\Models\Deposit;
use App\Models\Promotion;
use App\Models\TramcardUser;
use App\Models\User;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
class AffiliateController extends Controller
{
    public function getDashboardData()
    {
        $user = auth()->user();
        $yestrdayEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->whereDate('created_at', Carbon::yesterday())->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->whereDate('created_at', Carbon::yesterday())->sum('amount');
        $currentMonthEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->whereMonth('created_at', Carbon::now()->month)->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->whereMonth('created_at', Carbon::now()->month)->sum('amount');
        $thirtyDaysEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->whereDate('created_at', '>=', Carbon::now()->subDays(30))->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->whereDate('created_at', '>=', Carbon::now()->subDays(30))->sum('amount');
        $totalEarn = AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 1)->sum('amount') - AffiliateCommissionTransaction::where('affiliate_id', $user->id)->where('result', 2)->sum('amount');

        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

        $promoCodeUserDataMonthWise = AffiliatePromos::where('affliate_user_id', $user->id)
            ->whereYear('created_at', Carbon::now()->year)
            //->whereMonth('created_at', Carbon::now()->month)
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
            'affiliateCommisionData' => $this->getCommitionData($user->id),
        ];
        $payload = [
            'status'         => true,
            'data' => $data,
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
    }
    public function getCommonData()
    {
        $currency = Currency::orderBy('currency_code', 'asc')->get();
        $website = AffiliateWebsite::where('affiliate_id', auth()->id())->get();
        $promo = Promotion::where('user_id', auth()->id())->first();
        $countries  = json_decode(file_get_contents(resource_path('views/partials/country.json')));

        $payload = [
            'status'         => true,
            'data'              => [
                'currency' => $currency,
                'websites' => $website,
                'promo' => $promo,
                'countries' => $countries

            ],
            'app_message'  => 'Successfully Retrieve Data',
            'user_message' => 'Successfully Retrieve Data'
        ];
        return response()->json($payload, 200);
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
    public function getPromotionsData(Request $request){
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $promotions = Promotion::where('user_id', auth()->id())->latest()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = $request->input('search');
                $query->where(function ($query) use ($search) {
                    $query->where('promo_code', 'LIKE', '%' . $search . '%');
                });
            });
        $promo = Promotion::where('user_id', auth()->id())->first();
        if (is_numeric($page)) {
            $promotions = $promotions->paginate($perPage);
        } else {
            $promotions = $promotions->get();
        }

        return response()->json(
            responseBuilder(
                Response::HTTP_OK,
                "Success",
                [
                    'data'          => [
                        'promotions' => PromotionsCollection::collection($promotions),
                        'promo' => $promo],
                    'per_page'      => $promotions->perPage() ?? 10,
                    'current_page'  => $promotions->currentPage() ?? 1,
                    'total'         => $promotions->total() ?? count($promotions),
                    'last_page'     => $promotions->lastPage() ?? count($promotions)
                ]
            ),
            Response::HTTP_OK
        );
    }
    public function createPromo(Request $request){
        $request->validate([
            'title' => 'required',
            'details' => 'nullable|string',
            'promo_code' => 'required|unique:promotions'
        ]);

        $promo                      = new Promotion();
        $promo->title               = $request->title;
        $promo->promo_code          = $request->promo_code;
        $promo->status              = $request->status;
        $promo->details             = $request->details;
        $promo->promo_percentage    = $request->promo_percentage;
        $promo->slug                = slug($request->title);
        $promo->is_admin_approved   = 0;

        if ($request->hasFile('attachments')) {
            $image = $request->file('attachments');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/promotion/', $filename);
            $promo->image = $filename;
        }
        $promo->user_id = auth()->id();
        $promo->save();
        $payload = [
            'status'            => true,
            'data'              => $promo,
            'app_message'       => 'Promotion Created Successfully',
            'user_message'      => 'Promotion Created Successfully'
        ];

        return response()->json($payload, 200);
    }
    
    
     public function updatePromo(Request $request, $id)
    {
        $request->validate([
            'title' => 'required',
            'details' => 'required',
            'promo_code' => 'required|unique:promotions,promo_code,' . $id,
        ]);

        $promo                  = Promotion::findOrFail($id);
        $promo->title           = $request->title;
        $promo->promo_code      = $request->promo_code;
        $promo->status          = $request->status;
        $promo->start_date      = $request->start_date;
        $promo->end_date        = $request->end_date;
        $promo->details         = $request->details;
        $promo->learn_more_link = $request->learn_more_link;
        $promo->slug            = slug($request->title);

        if ($request->hasFile('attachments')) {
            $image_path ='/home/p333r1m2287/public_html/assets/promotion/' . $promo->image;

            if ($promo->image && file_exists($image_path)) {
                unlink($image_path);
            }

            $image = $request->file('attachments');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/promotion/', $filename);
            $promo->image = $filename;
        }

        $promo->save();
        $payload = [
            'status'            => true,
            'app_message'       => 'Promotion Updated Successfully',
            'user_message'      => 'Promotion Updated Successfully'
        ];

        return response()->json($payload, 200);
    }
    
    public function destroyPromo($id)
    {
        $promo = Promotion::findOrFail($id);
        if ($promo->user_id != auth()->id()) {
            abort(404);
        }
        if ($promo->image) {
            $image_path = '/home/p333r1m2287/public_html/assets/promotion/' . $promo->image;
            unlink($image_path);
        }
        $promo->delete();
        $payload = [
            'status'            => true,
            'app_message'       => 'Promotion Successfully Deleted',
            'user_message'      => 'Promotion Successfully Deleted'
        ];

        return response()->json($payload, 200);
    }
    
    
    public function linkGenerate(Request $request){
        $request->validate([
            'website' => 'required',
            'currency' => 'required',
            'campaign' => 'nullable',
            'landingpage' => 'nullable',
        ]);

        $str = $request->website;
        $parts = explode(" | ", $str);

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
        $payload = [
            'status'            => true,
            'data'              => $web,
            'app_message'       => 'Promotion Created Successfully',
            'user_message'      => 'Promotion Created Successfully'
        ];

        return response()->json($payload, 200);
    }
    public function getPromoUsersData($pageNo = null,$perPage = null){
        $perPage = $perPage ?? 10;
        $paginationData = [];
        $affiliates = AffiliatePromos::where('affliate_user_id', auth()->id())->latest();
        if($pageNo){
            $skip = $pageNo == 1 ? 0 : $perPage * $pageNo;
            $affiliates = $affiliates->skip($skip)->take($perPage)->get();
            $paginationData = [
                'currentPage'         => $pageNo,
                'nextPage'         => $pageNo+1,
                'totalPages'         => round($affiliates->count()/$perPage),
                'totalItems'         => $affiliates->count(),
                'itemsPerPage'         => $perPage,
            ];
        }else{
            $affiliates = $affiliates->get();
        }


        $payload = [
            'status'            => true,
            'data'              => $affiliates,
            'paginationData' =>  $paginationData,
            'app_message'       => 'Successfully Retrieve Data',
            'user_message'      => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);
    }
    public function getWebsitesData(Request $request){
        
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $websiteList = AffiliateWebsite::where('affiliate_id', auth()->id())
        ->when($request->filled('search'), function ($query) use ($request) {
            $search = $request->input('search');
            $query->where(function ($query) use ($search) {
                $query->where('website', 'LIKE', '%' . $search . '%')
                      ->orWhere('websiteId', 'LIKE', '%' . $search . '%');
            });
        });
        if (is_numeric($page)) {
            $websiteList = $websiteList->paginate($perPage);
        } else {
            $websiteList = $websiteList->get();
        }

        return response()->json(
            responseBuilder(
                Response::HTTP_OK,
                "Success",
                [
                    'data'          => $websiteList,
                    'per_page'      => $websiteList->perPage() ?? 10,
                    'current_page'  => $websiteList->currentPage() ?? 1,
                    'total'         => $websiteList->total() ?? count($websiteList),
                    'last_page'     => $websiteList->lastPage() ?? count($websiteList)
                ]
            ),
            Response::HTTP_OK
        );
    }

    public function getAffiliateLinkData(Request $request){
        $page = $request->page ?? 1;
        $perPage = $request->per_page ?? 10;
        $websiteList = Website::where('user_id', auth()->id());

        if($request->search and $request->filled('search')){
            $websiteList = $websiteList->where('aff_website', 'LIKE', '%'.$request->search.'%');
        }

        if (is_numeric($page)) {
            $websiteList = $websiteList->paginate($perPage);
        } else {
            $websiteList = $websiteList->get();
        }
        return response()->json(
            responseBuilder(
                Response::HTTP_OK,
                "Success",
                [
                    'data'          => $websiteList,
                    'per_page'      => $websiteList->perPage() ?? 10,
                    'current_page'  => $websiteList->currentPage() ?? 1,
                    'total'         => $websiteList->total() ?? count($websiteList),
                    'last_page'     => $websiteList->lastPage() ?? count($websiteList)
                ]
            ),
            Response::HTTP_OK
        );
    }
    public function affiliateLinkGenerate(AffiliateLinkGenerateStore $request){
        $str = $request->website;
        $parts = explode(" | ", $str);
        $randId = rand(1000, 9999);
        try {
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
//            $web->linkgenarate=config('app.nextjs_root_url');
            $web->linkgenarate  = config('app.nextjs_root_url').'/auth/register' . '?affi_link=' . Promotion::where('status', 1)->where("user_id", auth()->user()->id)->value('promo_code')
                . '&id=' . auth()->user()->user_id . '&site=' . AffiliateWebsite::where('id', $request->website)->value('websiteId') . '&ad=' . $randId;
//            $web->linkgenarate  = url('/') . '?affi_link=' . Promotion::where('status', 1)->where("user_id", auth()->user()->id)->value('promo_code')
//                . '&id=' . auth()->user()->user_id . '&site=' . AffiliateWebsite::where('id', $request->website)->value('websiteId') . '&ad=' . $randId;
            $web->save();

            $payload = [
                'status'         => true,
                'app_message'    => 'Successfully Stored Data',
                'user_message'   => 'Successfully Stored Data'
            ];
            return response()->json($payload, 200);
        }catch (\Exception $exception){
            $payload = [
                'status'         => false,
                'app_message'  => 'Please try again.',
                'user_message' => 'Please try again.'
            ];
            return response()->json($payload, 200);

        }

    }
    public function getDetailsReportData( Request $request){
        if (!empty($request->all())) {
            $interval = $request->input('interval');
            $dateRange = $request->dates;
            $dates = explode(" - ", $dateRange);
            $from = date('Y-m-d H:i:s', strtotime($dates[0]));
            $to = date('Y-m-d H:i:s', strtotime($dates[1]));
            $currency = $request->input('currency');
        }

        $website = AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($request->website, function ($query, $website) {
                return $query->where('website', $website);
            });

        $reportWebsiteData  = $website->groupBy('website')->get()->pluck('website', 'websiteId');

        $reportData = [];
        foreach($reportWebsiteData as $key => $value){
            $dates = explode(' - ', $request->dates);
            $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
            $to = isset($dates[1]) ? date('Y-m-d H:i:s', strtotime($dates[1])) : null;

            $userData = AffiliatePromos::where('affliate_user_id', auth()->id())
                ->where('website', $value)->get();
            $userIds = $userData->pluck('better_user_id');

            $deposits = Deposit::whereIn('user_id', $userIds)
                ->where('status', 1)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->get();
            $newDepositors = $deposits->groupBy('user_id')->count();
            $totalAmount = $deposits->sum('final_amo');

            $totalBonus = User::whereIn('id', $userIds)
                ->where('status', 1)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('bonus_account');


            $transactionSums = AffiliateCommissionTransaction::whereIn('user_id', $userIds)
                ->whereIn('result', [1, 2])
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->groupBy('result')
                ->selectRaw('result, sum(amount) as sum')
                ->pluck('sum', 'result');
            $earn = $transactionSums[1] ?? 0;
            $loss = $transactionSums[2] ?? 0;

            $commission = $earn - $loss;


//            $companyProfit = Bet::whereIn('user_id', $userIds)
//                ->where('status', [1, 3])
//                ->when($from && $to, function ($query) use ($from, $to) {
//                    return $query->whereBetween('created_at', [$from, $to]);
//                })
//                ->groupBy('status')
//                ->selectRaw('status, sum(stake_amount) as sum')
//                ->pluck('sum', 'status');
//
//            $companyProfitWin = $companyProfit[1] ?? 0;
//            $companyProfitLoss = $companyProfit[2] ?? 0;
//            $companyProf = $companyProfitLoss - $companyProfitWin;

            $companyProf = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
                ->where('result', '1')
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('company_expenses');

            $reportData[] = [
                'website_id' => $key,
                'website' => $value,
                'registrations' => $userData->count(),
                'new_depositors' => $newDepositors,
                'total_deposit_amount' =>  showAmount($totalAmount),
                'bonus_amount' => showAmount($totalBonus),
//                'company_profit_prefix' =>  $companyProf >= 0 ? '+' : '-',
                'company_profit' =>  showAmount(abs($companyProf)),
//                'profit_class' =>  $companyProf >= 0 ? 'success' : 'danger',
                'commission_amount_prefix' => $commission >= 0 ? '+' : '-',
                'commission_amount' => showAmount(abs($commission)),
                'commission_class' => $commission >= 0 ? 'success' : 'danger',
                'loss'=>$loss,
                'earn'=>$earn
            ];
        }
        $payload = [
            'status'            => true,
            'data'              => $reportData,
            'app_message'       => 'Successfully Retrieve Data',
            'user_message'      => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);
    }
    public function getPlayerReportData( Request $request)
    {

        $dates = explode(' - ', $request->dates);
        $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
        $to = isset($dates[1]) ? date('Y-m-d 23:59:59', strtotime($dates[1])) : null;
        $currency = $request->currency;
        $country = $request->country;
        $userId = $request->playerId;
        $playerType = $request->playerType;

        $affiliatePromos = AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->when($playerType == "new", function ($query) {
                return $query->where('created_at', '>=', \Illuminate\Support\Carbon::now()->subDays(30));
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
        $affiliatePromos = $affiliatePromos->get();

        $reportData = [];
        foreach($affiliatePromos as $key => $value){

            $deposits = Deposit::where('user_id', $value->better_user_id)
                ->where('status', 1)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('final_amo');

            $transactionSums1 = Bet::where('user_id', $value->better_user_id)
                ->where('status', 1)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('stake_amount');

            $transactionSums2 = Bet::where('user_id', $value->better_user_id)
                ->where('status', 3)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('stake_amount');
            $companyprofit = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
                ->where('result', '1')
                ->where('user_id', $value->better_user_id)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('company_expenses');

            $affiliate_comission = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
                ->where('result', '1')
                ->where('user_id', $value->better_user_id)
                ->when($from && $to, function ($query) use ($from, $to) {
                    return $query->whereBetween('created_at', [$from, $to]);
                })
                ->sum('amount');

            $commission = $transactionSums2 - $transactionSums1;
            $reportData[] = [
                'website_id' => $value->websiteId ?? "-",
                'website' => $value->website ?? "-",
                'sub+id' => optional($value->promo)->promo_code,
                'player_id' => optional($value->betterUser)->user_id,
                'registration_date' =>  $value->created_at->format('d-M-Y'),
                'registration_time' =>  $value->created_at->format('h:i A'),
                'country' => optional($value->betterUser)->country_code,
                'currency' =>  optional($value->betterUser)->currency,
                'sum_of_all_deposit' =>  showAmount($deposits),
                'company_profit_total_prefix' => $companyprofit >= 0 ? '+' : '-',
//                'company_profit_total' => showAmount(abs($commission)),
                'company_profit_total' => showAmount($deposits) >0?showAmount(abs($companyprofit)):0,
                'affiliate_commision' => showAmount(abs($affiliate_comission)),
                'profit_class' => $companyprofit >= 0 ? 'success' : 'danger',
            ];
        }
        $payload = [
            'status'            => true,
            'data'              =>  $reportData,
            'app_message'       => 'Successfully Retrieve Data',
            'user_message'      => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);
    }
    public function getSummeryData(Request $request){

        $dates = explode(' - ', $request->dates);
        $from = isset($dates[0]) ? date('Y-m-d H:i:s', strtotime($dates[0])) : null;
        $to = isset($dates[1]) ? date('Y-m-d 23:59:59', strtotime($dates[1])) : null;

        $registrationCount = AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->get();
        $wining_bonus_amount=AffiliatePromos::where('affliate_user_id', auth()->id())
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('wining_bonus_amount');

//        $profit = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
//            ->where('result', '1')
//            ->when($from && $to, function ($query) use ($from, $to) {
//                return $query->whereBetween('created_at', [$from, $to]);
//            })
//            ->sum('amount');
        $profit = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
            ->where('result', '1')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('company_expenses');

        $loss = AffiliateCommissionTransaction::where('affiliate_id', auth()->id())
            ->where('result', '2')
            ->when($from && $to, function ($query) use ($from, $to) {
                return $query->whereBetween('created_at', [$from, $to]);
            })
            ->sum('amount');

        $newBetter = $registrationCount->pluck("better_user_id");

        $newDepositor = Deposit::whereIn('user_id', $newBetter)
            ->where('status', 1)
            ->whereBetween('created_at', [$from ?? \Illuminate\Support\Carbon::now()->subDays(30), $to ?? Carbon::now()]);

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
        $revenue=User::where('id',auth()->id())->value('affiliate_temp_balance');
        $data = [
            'registration' => $registrationCount->count(),
            'profit' => $profit,
            'loss' => $loss,
//            'revenue' => $profit - $loss,
            'revenue' => $revenue,
            'active_player' => $registrationCount->count(),
            'bonus' => $bonus,
            'webs' =>  Website::where('user_id', auth()->id())->get(),
            'web_count' =>  $webCount->count(),
            'view' => $click,
            'new_depositor' => $newDepositor->groupBy('user_id')->get()->unique('user_id')->count(),
            'new_depositor_amount' => $newDepositor->sum('final_amo'),
            'all_deposit_amount' => $allDepositAmount,
            'wining_bonus_amount' => $wining_bonus_amount,
        ];
        $payload = [
            'status'            => true,
            'data'              => [
                'summery' => $data,
                'currency' => $currency,
                'website' => $website,
            ],
            'app_message'       => 'Successfully Retrieve Data',
            'user_message'      => 'Successfully Retrieve Data'
        ];

        return response()->json($payload, 200);
    }
    
    
    public function websiteAdd(Request $request)
    {
        $request->validate([
            'website' => 'required',
            'type' => 'required'
        ]);

        $website = new AffiliateWebsite();
        $website->affiliate_id = auth()->id();
        $website->website = $request->website;
        $website->webtype = $request->type;
        $website->status = 1;
        $website->websiteId = AffiliateWebsite::latest()->first() ? AffiliateWebsite::latest()->first()->websiteId + 1 : 100001;
        $website->save();
        $payload = [
            'status'            => true,
            'app_message'       => 'Website Successfully Created',
            'user_message'      => 'Website Successfully Created'
        ];
        return response()->json($payload, 200);
    }
    
    
    public function websiteUpdate($id,Request $request){
        $request->validate([
            'website_edit' => 'required',
            'type_edit' => 'required'
        ]);

        $website = AffiliateWebsite::find($id);
        if($website){
            $website->website = $request->website_edit;
            $website->webtype = $request->type_edit;
            $website->save();
            $payload = [
                'status'            => true,
                'app_message'       => 'Website Updated Successfully',
                'user_message'      => 'Website Updated Successfully'
            ];
            return response()->json($payload, 200);
        }else{
            $payload = [
                'status' => false,
                'app_message' => 'Please try again.',
                'user_message' => 'Please try again.'
            ];
            return response()->json($payload, 200);
        }



    }
    public function websiteDelete($id){

        $website = AffiliateWebsite::find($id);
        if($website){
            $website->delete();
            $payload = [
                'status'            => true,
                'app_message'       => 'Website Deleted Successfully',
                'user_message'      => 'Website Deleted Successfully'
            ];
            return response()->json($payload, 200);
        }else{
            $payload = [
                'status' => false,
                'app_message' => 'Please try again.',
                'user_message' => 'Please try again.'
            ];
            return response()->json($payload, 200);
        }



    }

}
