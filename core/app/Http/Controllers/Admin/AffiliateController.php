<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Models\AffiliatePromos;
use App\Models\AffiliateWebsite;
use App\Models\AffiliateWithdrawSetting;
use App\Models\ApplicationForm;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;

class AffiliateController extends Controller
{
    public function affiliateList(){
        $affiliate = User::where('is_affiliate', 1)->latest()->paginate(getPaginate(10));
        $pageTitle = 'Affiliate List';
        return view('admin.affiliate.affiliatelist', compact('pageTitle', 'affiliate'));
    }

    public function affiliateDetails($id){
        $pageTitle = 'Affiliate Details';
        $affiliate = User::findOrFail($id);
        $promocode = Promotion::where('user_id', $id)->latest()->paginate(getPaginate(10));
        $registredUser = AffiliatePromos::where('affliate_user_id', $id)->latest()->paginate(getPaginate(10));
        return view('admin.affiliate.affiliatedetails', compact('pageTitle', 'affiliate', 'promocode','registredUser'));
    }

    public function promocodeList(){
        $promocode = Promotion::latest()->paginate(getPaginate(10));
        $pageTitle = 'Promocodes';
        return view('admin.affiliate.promoList', compact('pageTitle', 'promocode'));
    }

    public function promoCodeEdit($id){
        $promo = Promotion::findOrFail($id);
        return response()->json($promo);
    }

    public function promoCodeUpdate(Request $request){
        $promo = Promotion::findOrFail($request->id);
        $promo->promo_percentage = $request->percentage;
        $promo->company_expenses = $request->company_expenses;
        $promo->is_admin_approved = $request->is_admin_approved;
        $promo->save();
        $notify[] = ['success', 'Promocode Updated Successfully'];
        return response()->json(['success', 'Promocode Updated Successfully']);
    }

    public function promoCodeReject(Request $request){
        $promo = Promotion::findOrFail($request->id);
        $promo->admin_comment = $request->reject_reason;
        $promo->is_admin_approved = $request->is_admin_approved;
        $promo->save();
        $notify[] = ['success', 'Promocode Is rejected'];
        return response()->json(['success', 'Promocode Is Rejected']);
    }

    public function betterApplication(){
        $application = ApplicationForm::latest()->paginate(getPaginate(10));
        $pageTitle = 'Better Application';
        return view('admin.affiliate.betterApplication', compact('pageTitle', 'application'));
    }

    public function applicationForm($id){
        $application = ApplicationForm::findOrFail($id);
        return response()->json($application);
    }

    public function applicationapprove(Request $request){
        try {
            DB::beginTransaction();

            $application = ApplicationForm::findOrFail($request->id);
            $application->promo_code = $request->promo_code;
            $application->company_expenses = $request->company_expenses;
            $application->is_approved = $request->is_approved;
            $application->save();

            $user = User::findOrFail($application->user_id);
            $user->is_affiliate = 1;
            $user->save();

            $website = new AffiliateWebsite();
            $website->affiliate_id = $application->user_id;
            $website->website = $application->website;
            $website->webtype = "website";
            $website->company_expenses = $request->company_expenses;
            $website->websiteId = AffiliateWebsite::latest()->first() ? AffiliateWebsite::latest()->first()->websiteId + 1 : 100001;
            $website->status = 1;
            $website->save();

            $promocode = new Promotion();
            $promocode->user_id = $application->user_id;
            $promocode->promo_code = $request->promo_code;
            $promocode->promo_percentage = $request->percentage;
            $promocode->company_expenses = $request->company_expenses;;
            $promocode->is_admin_approved = 1;
            $promocode->status = 1;
            $promocode->save();

            DB::commit();

            $notify[] = ['success', 'Application Approved Successfully'];
            return response()->json(['success', 'Application Approved Successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error', 'There was an error processing your request.'], 500);
        }
    }
    public function applicationreject(Request $request){
        $application = ApplicationForm::findOrFail($request->id);
        $application->is_approved = $request->is_approved;
        $application->save();
        $notify[] = ['success', 'Application Is rejected'];
        return response()->json(['success', 'Application Is Rejected']);
    }
    public function companyExpenses(Request $request){
        $pageTitle="Company Expenses";

        return view('admin.affiliate.companyExpenses', compact('pageTitle'));
    }
    public function companyExpenseStore(Request $request){
        $company_expenses = AffiliateWebsite::all();
        $company_expenses->each(function ($affiliateWebsite) use ($request) {
            $affiliateWebsite->company_expenses = $request->company_expenses;
            $affiliateWebsite->save();
        });
        return redirect()->route('admin.affiliate.list');


    }

    public function affliateWithdrawsettingView(Request $request)
    {
        $pageTitle='Affiliate Withdraw Setting';
        $sevenDays = collect();
        for ($i = 6; $i >= 0; $i--) {
            $sevenDays->push(now()->subDays($i)->format('l'));
        }
        $existingRecord=AffiliateWithdrawSetting::first();
        return view('admin.affiliate.withdraw_setting',compact('pageTitle','existingRecord','sevenDays'));
    }

    public function affliateWithdrawsettingStore(Request $request){

        $validated = $request->validate([
            'withdraw_date' => 'required|string',
        ]);


        $affiliateWithdrawSetting = AffiliateWithdrawSetting::updateOrCreate(
            ['id' => 1], // or another condition to identify the row
            [
                'withdraw_date' => $validated['withdraw_date'],
                'can_withdraw_after' => $request->can_withdraw_after,
                'created_at'=>Carbon::now()
            ]
        );
        if($affiliateWithdrawSetting){
            User::where('is_affiliate', 1)->update(['can_withdraw_after' => $affiliateWithdrawSetting->can_withdraw_after]);
        }

        $notify[] = ['success', 'Affiliate withdraw Day save Successfully'];
        return redirect()->route('admin.affiliate.withdraw.setting')->withNotify($notify);
    }
}
