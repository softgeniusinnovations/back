<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\News;
use App\Models\CashbackSetting;
use App\Models\DepositBonusSetting;
use App\Models\TransectionProviders;
use App\Models\User;
use App\Models\UserBonusList;
use App\Models\Bet;
use App\Models\PromoBanner;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class EventController extends Controller
{
    public function index()
    {
        $pageTitle = 'Event List';
        $events = News::latest()->paginate(getPaginate(10));
        return view('admin.event.list', compact('pageTitle', 'events'));
    }

    public function create()
    {
        $pageTitle = 'Create Event';
        return view('admin.event.create', compact('pageTitle'));
    }

    public function store(Request $request)
    {
        //Add Validation
        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'bonus_percentage' => 'required|min:0|max:100|numeric',
            'attachments' => 'required|mimes:jpg,jpeg,png,svg',
            'details' => 'required',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:1,0|numeric',
        ]);

        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $event = new News();
        $event->title = $request->title;
        $event->sub_title = $request->sub_title;
        $event->type = $request->type;
        $event->bonus_percentage = $request->bonus_percentage;
        $event->description = $request->details;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->status = $request->status;
        $event->featured = $request->featured;
        if ($request->hasFile('attachments')) {
            $image = $request->file('attachments');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/news/', $filename);
            $event->image = $filename;
        }
        $event->save();

        $notify[] = ['success', 'Event Created Successfully'];
        return redirect()->route('admin.event.list')->withNotify($notify);
    }

    public function show($id)
    {
        // return view('admin.event.show');
    }

    public function edit($id)
    {
        $pageTitle = 'Event & News Update';
        $news = News::find($id);
        return view('admin.event.edit', compact('pageTitle', 'news'));
    }

    public function update(Request $request, $id)
    {
        $event = News::find($id);
        //Add Validation
        $validate = Validator::make($request->all(), [
            'title' => 'required',
            'bonus_percentage' => 'required|min:0|max:100|numeric',
            'attachments' => $event->image ? 'nullable|mimes:jpg,jpeg,png,svg' : 'required|mimes:jpg,jpeg,png,svg',
            'details' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'status' => 'required|in:1,0|numeric',
        ]);

        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $event->title = $request->title;
        $event->bonus_percentage = $request->bonus_percentage;
        $event->description = $request->details;
        $event->start_date = $request->start_date;
        $event->end_date = $request->end_date;
        $event->status = $request->status;
        $event->featured = $request->featured;
        if ($request->hasFile('attachments')) {
            $image_path = public_path() . '/assets/news/' . $event->image;
            if ($event->image && file_exists($image_path)) {
                unlink($image_path);
            }
            $image = $request->file('attachments');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/news/', $filename);
            $event->image = $filename;
        }
        $event->save();

        $notify[] = ['success', 'Event Updated Successfully'];
        return redirect()->route('admin.event.list')->withNotify($notify);
    }

    public function destroy($id)
    {
        $event = News::find($id);
        if ($event->image) {
            $image_path = public_path() . '/assets/news/' . $event->image;
            if ($event->image && file_exists($image_path)) {
                unlink($image_path);
            }
        }
        $event->delete();
        $notify[] = ['success', 'Event Deleted Successfully'];
        return response()->json(['success' => 'News Deleted Successfully']);
    }

    // Cashback settings
    public function cashbackSettings()
    {
        $pageTitle = 'Cashback Settings';
        $setting =  CashbackSetting::where('type', 'cashback')->where('game_type','upcoming')->first();
        $casinosetting =  CashbackSetting::where('type', 'cashback')->where('game_type','casino')->first();
        return view('admin.event.cashback_settings', compact('pageTitle', 'setting','casinosetting'));
    }
    public function cashbackSettingsUpdate(Request $r)
    {
        $r->validate([
            'cashback_percentage' => 'required|numeric|min:0|max:100',
            // 'loss_calculation_start' => 'required|string',
            'loss_calculation_end' => 'required|string',
            'game_type' => 'required|string',
            'wager' => 'required|numeric|min:1',
            'rollover' => 'required|numeric|min:1',
            'minimum_bet' => 'required|numeric|min:1',
            'odd_selection' => 'required|min:0',
            'valid_time' => 'required|numeric',
            'activation_day' => 'required|numeric|min:1',
            'maximum_claim_in_week' => 'required|numeric|min:1',
            'type' => 'required'
        ]);

        try {
            // dd($r->all());
            $cashbackSetting = CashbackSetting::updateOrCreate(

                ['type' => 'cashback','game_type' => $r->game_type],
                [
                    'cashback_percentage' => $r->cashback_percentage,
                    // 'loss_calculation_start' => $r->loss_calculation_start,
                    'loss_calculation_end' => $r->loss_calculation_end,
                    'game_type' => $r->game_type,
                    'type' => $r->type,
                    'wager' => $r->wager,
                    'rollover' => $r->rollover,
                    'minimum_bet' => $r->minimum_bet,
                    'odd_selection' => $r->odd_selection,
                    'valid_time' => $r->valid_time,
                    'activation_day' => $r->activation_day,
                    'maximum_claim_in_week' => $r->maximum_claim_in_week,
                ]
            );
            if ($cashbackSetting) {
                $notify[] = ['success', 'Successfully updated'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'Something went wrong!'];
                return back()->withNotify($notify);
            }
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }
    public function cashbackSettingsCasinoUpdate(Request $r)
    {
        $r->validate([
            'cashback_percentage' => 'required|numeric|min:0|max:100',
            // 'loss_calculation_start' => 'required|string',
            'loss_calculation_end' => 'required|string',
            'game_type' => 'required|string',
            'wager' => 'required|numeric|min:1',

            'valid_time' => 'required|numeric',
            'activation_day' => 'required|numeric|min:1',
            'maximum_claim_in_week' => 'required|numeric|min:1',
            'type' => 'required'
        ]);

        try {
            // dd($r->all());
            $cashbackSetting = CashbackSetting::updateOrCreate(
                ['type' => 'cashback','game_type' => $r->game_type],
                [
                    'cashback_percentage' => $r->cashback_percentage,
                    // 'loss_calculation_start' => $r->loss_calculation_start,
                    'loss_calculation_end' => $r->loss_calculation_end,
                    'game_type' => $r->game_type,
                    'type' => $r->type,
                    'wager' => $r->wager,

                    'valid_time' => $r->valid_time,
                    'activation_day' => $r->activation_day,
                    'maximum_claim_in_week' => $r->maximum_claim_in_week,
                ]
            );
            if ($cashbackSetting) {
                $notify[] = ['success', 'Successfully updated'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'Something went wrong!'];
                return back()->withNotify($notify);
            }
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }



    public function cashbackCommand()
    {
        $setting = CashbackSetting::where('type', 'cashback')->first();
        $date = Carbon::now();
        if ($setting) {
            $lossCalculationDay = $setting->loss_calculation_end;
            $today = $date->format('l');

            if ($lossCalculationDay == $today) {
                $lossCalculationStartDay  = $date->subDay();
                $lossCalculationEndDay = Carbon::now()->subDays(8);
                $activeBettorLastDay = Carbon::now()->subDays($setting->activation_day);

                $activeUsers =  $usersWithBetsEveryDay = Bet::select('user_id')
                    ->whereBetween('created_at', [$activeBettorLastDay->format('Y-m-d'), $lossCalculationStartDay->addDay()->format('Y-m-d')])
                    ->groupBy('user_id')
                    ->havingRaw('COUNT(DISTINCT DATE(created_at)) = ?', [Carbon::parse($lossCalculationStartDay)->diffInDays(Carbon::parse($activeBettorLastDay)) + 1])
                    ->where('user_id', auth()->user()->id)->get();
            }
        }
    }

    // Deposit settings
    public function depositSettings()
    {
        $pageTitle = 'Deposit Bonus';
        $depositBonus =  DepositBonusSetting::where('type', 'deposit')->orderBy('id', 'desc')->paginate(20);
        $providers = TransectionProviders::where('status', 1)->get();
        return view('admin.event.deposit_bonus_settings', compact('pageTitle', 'depositBonus', 'providers'));
    }

    public function deositSettingsCreate(Request $r)
    {
//        dd($r);
        $r->validate([
            'deposit_percentage' => 'required|numeric|min:0|max:100',
            'bonus_type' => 'required|string',
            'game_type' => 'required|string',
            'file' => 'required|image|mimes:jpeg,png,jpg|max:1024',
            'days' => [
                'required_if:bonus_type,days',
                'nullable',
                'array'
            ],
            'providers' => [
                'required_if:bonus_type,providers',
                'nullable',
                'array'
            ],
            'wager' => 'required|numeric|min:1',
            'rollover' => 'required|numeric|min:1',
            'minimum_bet' => 'required|numeric|min:1',
            'odd_selection' => 'required|numeric|min:1.4|max:3.0',
            'valid_time' => 'required|numeric',
            'maximum_claim_in_day' => 'required|numeric|min:1',
            'min_bonus' => 'required|numeric|min:0',
            'max_bonus' => 'required|numeric|gt:min_bonus',
            'type' => 'required'
        ]);

        $days = null;
        $providers = null;

        if ($r->bonus_type == 'days') {
            $days = array_values($r->days);
        } else {
            $providers = array_values($r->providers);
        }

        try {
            $file = $r->file('file');
            $fileName = time() . '.' . $file->extension();

            $depositBonus = new DepositBonusSetting();
            $depositBonus->type = $r->type;
            $depositBonus->deposit_percentage = $r->deposit_percentage;
            $depositBonus->bonus_type = $r->bonus_type;
            $depositBonus->days = $days ? json_encode($days) : null;
            $depositBonus->providers = $providers ? json_encode($providers) : null;
            $depositBonus->game_type = $r->game_type=='sports'?1:2;
            $depositBonus->wager = $r->wager;
            $depositBonus->rollover = $r->game_type=='casino'?0:$r->rollover;
            $depositBonus->minimum_bet = $r->game_type=='casino'?0:$r->minimum_bet;
            $depositBonus->odd_selection = $r->game_type=='casino'?0:$r->odd_selection;
            $depositBonus->min_bonus = $r->min_bonus;
            $depositBonus->max_bonus = $r->max_bonus;
            $depositBonus->valid_time = $r->valid_time;
            $depositBonus->maximum_claim_in_day = $r->maximum_claim_in_day;
            $depositBonus->file = $fileName;
            $depositBonus->save();

            if ($depositBonus) {
                Storage::put('/public/bonus/' . $fileName, file_get_contents($file));
                $notify[] = ['success', 'Successfully Create'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'Something went wrong!'];
                return back()->withNotify($notify);
            }
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function deositSettingsEdit($id)
    {
        $pageTitle = 'Deposit Bonus Edit';
        $depositBonus =  DepositBonusSetting::findOrFail($id);
        $providers = TransectionProviders::get();
        return view('admin.event.deposit_bonus_settings_edit', compact('pageTitle', 'depositBonus', 'providers'));
    }

    public function depositSettingUpdate(Request $r, $id)
    {
         $r->validate([
            'deposit_percentage' => 'required|numeric|min:0|max:100',
            'bonus_type' => 'required|string',
            'game_type' => 'required|string',
            'file' => 'nullable|image|mimes:jpeg,png,jpg|max:1024',
            'days' => [
                'required_if:bonus_type,days',
                'nullable',
                'array'
            ],
            'providers' => [
                'required_if:bonus_type,providers',
                'nullable',
                'array'
            ],
            'wager' => 'required|numeric|min:1',
            'rollover' => 'required|numeric|min:1',
            'minimum_bet' => 'required|numeric|min:1',
            'odd_selection' => 'required|numeric|min:1.4|max:3.0',
            'valid_time' => 'required|numeric',
            'maximum_claim_in_day' => 'required|numeric|min:1',
            'min_bonus' => 'required|numeric|min:0',
            'max_bonus' => 'required|numeric|gt:min_bonus',
            'type' => 'required'
        ]);

        $days = null;
        $providers = null;

        if ($r->bonus_type == 'days') {
            $days = array_values($r->days);
        } else {
            $providers = array_values($r->providers);
        }

        try {
            $file = $r->file('file');

            $depositBonus = DepositBonusSetting::findOrFail($id);

            $oldFile = $depositBonus->file;
            if($file){

            $fileName = time() . '.' . $file->extension();
            $depositBonus->file = $fileName;
            }

            $depositBonus->type = $r->type;
            $depositBonus->deposit_percentage = $r->deposit_percentage;
            $depositBonus->bonus_type = $r->bonus_type;
            $depositBonus->days = $days ? json_encode($days) : null;
            $depositBonus->providers = $providers ? json_encode($providers) : null;
            $depositBonus->game_type = $r->game_type;
            $depositBonus->wager = $r->wager;
            $depositBonus->rollover = $r->rollover;
            $depositBonus->minimum_bet = $r->minimum_bet;
            $depositBonus->odd_selection = $r->odd_selection;
            $depositBonus->min_bonus = $r->min_bonus;
            $depositBonus->max_bonus = $r->max_bonus;
            $depositBonus->valid_time = $r->valid_time;
            $depositBonus->maximum_claim_in_day = $r->maximum_claim_in_day;
            $depositBonus->save();

            if ($depositBonus) {
                // Check if an old file exists and remove it
                

                if($file){
                    if ($oldFile && Storage::exists('/public/bonus/' . $oldFile)) {
                        Storage::delete('/public/bonus/' . $oldFile);
                    }
                    Storage::put('/public/bonus/' . $fileName, file_get_contents($file));
                }
                $notify[] = ['success', 'Successfully Update'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'Something went wrong!'];
                return back()->withNotify($notify);
            }
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }

    public function deositSettingsDelete($id){
        $depositBonus = DepositBonusSetting::find($id);
        if ($depositBonus->image) {
           $image_path = 'public/bonus/' . $depositBonus->image;

            if (Storage::exists($image_path)) {
                Storage::delete($image_path);
            }
        }
        $depositBonus->delete();
        $notify[] = ['success', 'Deleted Successfully'];
        return response()->json(['success' => 'Deleted Successfully']);
    }

    public function depositSettingsUpdate(Request $r)
    {
        $r->validate([
            'cashback_percentage' => 'required|numeric|min:0|max:100',
            // 'loss_calculation_start' => 'required|string',f
            'loss_calculation_end' => 'required|string',
            'game_type' => 'required|string',
            'wager' => 'required|numeric|min:1',
            'rollover' => 'required|numeric|min:1',
            'minimum_bet' => 'required|numeric|min:1',
            'odd_selection' => 'required|min:0',
            'valid_time' => 'required|numeric',
            'activation_day' => 'required|numeric|min:1',
            'maximum_claim_in_week' => 'required|numeric|min:1',
            'type' => 'required'
        ]);

        try {
            // dd($r->all());
            $cashbackSetting = CashbackSetting::updateOrCreate(
                ['type' => 'cashback'],
                [
                    'cashback_percentage' => $r->cashback_percentage,
                    // 'loss_calculation_start' => $r->loss_calculation_start,
                    'loss_calculation_end' => $r->loss_calculation_end,
                    'game_type' => $r->game_type,
                    'type' => $r->type,
                    'wager' => $r->wager,
                    'rollover' => $r->rollover,
                    'minimum_bet' => $r->minimum_bet,
                    'odd_selection' => $r->odd_selection,
                    'valid_time' => $r->valid_time,
                    'activation_day' => $r->activation_day,
                    'maximum_claim_in_week' => $r->maximum_claim_in_week,
                ]
            );
            if ($cashbackSetting) {
                $notify[] = ['success', 'Successfully updated'];
                return back()->withNotify($notify);
            } else {
                $notify[] = ['error', 'Something went wrong!'];
                return back()->withNotify($notify);
            }
        } catch (\Exception $e) {
            $notify[] = ['error', $e->getMessage()];
            return back()->withNotify($notify);
        }
    }
    
    public function promoBannersList() {
        $pageTitle = 'Promo Banner List';
        $events = PromoBanner::latest()->paginate(getPaginate(10));
        return view('admin.event.promo.list', compact('pageTitle', 'events'));
    }
    
    public function promoBannerCreate(Request $r) {
        $validate = Validator::make($r->all(), [
            'banners' => 'required|mimes:jpg,jpeg,png,svg',
        ]);

        if ($validate->fails()) {
            return back()->withErrors($validate)->withInput();
        }

        $event = new PromoBanner();
        if ($r->hasFile('banners')) {
            $image = $r->file('banners');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/promo_banners/', $filename);
            $event->image = $filename;
        }
        $event->save();

        $notify[] = ['success', 'Banner uploaded'];
        return redirect()->route('admin.event.promo.banner.list')->withNotify($notify);
    }
    
    public function promoBannerDelete($id)
    {
        $promoBanner = PromoBanner::findOrFail($id);
        $image_path = public_path() . '/assets/promo_banners/' . $promoBanner->image;
            if (file_exists($image_path)) {
                unlink($image_path);
            }

        $promoBanner->delete();
        $notify[] = ['success', 'Banner Deleted'];
        return redirect()->back()->withNotify($notify);
    }

    public function eventSendUser(){
        $pageTitle="Search User";
        $users=[];
        return view('admin.event.sendbonus',compact('pageTitle','users'));
    }
    public function userSearch(Request $request){
        $pageTitle="Search User";
        $query = $request->input('input');
        $users = User::where('user_id', $query)
            ->orWhere('username', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->get();
        foreach ($users as $user) {
            $user->has_active_bonus = UserBonusList::where('user_id', $user->id)->exists();
            $user->has_active_casinobonus = UserBonusList::where('user_id', $user->id)->where('game_type',2)->exists();
//            $user->bonus_data = UserBonusList::where('user_id', $user->id)->first();
        }
//        return $users;
        return view('admin.event.sendbonus',compact('pageTitle','users'));
    }

    public function sendBonus(Request $request, $id) {

        DB::beginTransaction();

        try {
            $user = User::find($id);
            if (!$user) {
                throw new \Exception("User not found");
            }



            $duration_text = (int) $request->valid_time * 24 . " hours";
            $bonus = new UserBonusList();
            $bonus->user_id = $user->id;
            $bonus->type = 'Admin';
            $bonus->initial_amount = $request->amount;
            $bonus->rollover_limit = $request->rollover;
            $bonus->min_bet_multi = $request->minimum_betin_multibet;
            $bonus->minimum_odd = $request->minimum_odd;
            $bonus->duration = $request->valid_time;



            $bonus->valid_time = now()->addDays($request->valid_time)->toDateTimeString();
            $bonus->duration_text = $duration_text;


            if (!$bonus->save()) {
                throw new \Exception("Failed to save bonus");
            }


            $user->increment('bonus_account', $bonus->initial_amount);


            $userNotify = new UserNotification();
            $userNotify->user_id = $user->id;
            $userNotify->title = "Congratulations! You have received " . $bonus->initial_amount . ' ' . $bonus->currency . " deposit bonus for " . $bonus->duration_text;
            $userNotify->url = "/user/bonus";

            if (!$userNotify->save()) {
                throw new \Exception("Failed to save user notification");
            }

            // Commit the transaction if all operations are successful
            DB::commit();

            $notify[] = ["success", "Bonus sent successfully"];
        } catch (\Exception $e) {

            DB::rollBack();
            $notify[] = ["error", $e->getMessage()];
        }

        return back()->with('notify', $notify);
    }

    public function sendCasinoBonus(Request $request,$id){

        DB::beginTransaction();

        try {
            $user = User::find($id);
            if (!$user) {
                throw new \Exception("User not found");
            }



            $duration_text = (int) $request->valid_time * 24 . " hours";
            $bonus = new UserBonusList();
            $bonus->user_id = $user->id;
            $bonus->type = 'Admin';
            $bonus->initial_amount = $request->amount;
            $bonus->rollover_limit = $request->rollover??'';
            $bonus->min_bet_multi = $request->minimum_betin_multibet??'';
            $bonus->minimum_odd = $request->minimum_odd??'';
            $bonus->duration = $request->valid_time;
            $bonus->game_type = 2;
            $bonus->wager_limit = $request->wager;



            $bonus->valid_time = now()->addDays($request->valid_time)->toDateTimeString();
            $bonus->duration_text = $duration_text;


            if (!$bonus->save()) {
                throw new \Exception("Failed to save bonus");
            }


            $user->increment('casino_bonus_account', $bonus->initial_amount);


            $userNotify = new UserNotification();
            $userNotify->user_id = $user->id;
            $userNotify->title = "Congratulations! You have received " . $bonus->initial_amount . ' ' . $bonus->currency . " deposit bonus for " . $bonus->duration_text;
            $userNotify->url = "/user/bonus";

            if (!$userNotify->save()) {
                throw new \Exception("Failed to save user notification");
            }

            // Commit the transaction if all operations are successful
            DB::commit();

            $notify[] = ["success", "Bonus sent successfully"];
        } catch (\Exception $e) {

            DB::rollBack();
            $notify[] = ["error", $e->getMessage()];
        }

        return back()->with('notify', $notify);
    }
}
