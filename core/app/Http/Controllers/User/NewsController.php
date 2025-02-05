<?php

namespace App\Http\Controllers\User;

use App\Models\News;
use App\Models\Bonuse;
use App\Models\User;
use App\Models\Deposit;
use App\Models\UserBonusList;
use App\Models\UserNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Notifications\TramcardSendNotification;
use Illuminate\Support\Facades\DB;
class NewsController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = 'All News';
        $news = News::latest()->paginate(getPaginate());
        return view($this->activeTemplate . 'user.affiliate.news.index', compact('pageTitle', 'news'));
    }

    public function events()
    {
        $pageTitle = 'All Events';
        // $date = Carbon::now()->toDateTimeString();
        $today = Carbon::now()->toDateString();
        $news = News::where('start_date', '<=', $today)
                ->where('end_date', '>=', $today)
                ->orderBy('created_at', 'desc')
                ->paginate(getPaginate());
        return view($this->activeTemplate . 'user.events', compact('pageTitle', 'news'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Create News';
        return view($this->activeTemplate . 'user.affiliate.news.create', compact('pageTitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = 'Edit News';
        $news = News::findOrFail($id);
        if ($news->user_id != auth()->id()) {
            abort(404);
        }
        return view($this->activeTemplate . 'user.affiliate.news.update', compact('pageTitle', 'news'));
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
        $news              = News::findOrFail($id);
        $news->title       = $request->title;
        $news->description = $request->details;
        $news->status      = $request->status;
        $news->featured    = $request->featured;
        $news->slug        = slug($request->title);

        if ($request->hasFile('attachments')) {
            $image_path = public_path() . '/assets/news/' . $news->image;
            if ($news->image && file_exists($image_path)) {
                unlink($image_path);
            }
            $image = $request->file('attachments');
            $filename = time() . '.' . $image->getClientOriginalExtension();
            $image->move('assets/news/', $filename);
            $news->image = $filename;
        }

        $news->save();
        return redirect()->route('user.news.index')->with('success', 'News Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $news = News::findOrFail($id);
        if ($news->user_id != auth()->id()) {
            abort(404);
        }

        if ($news->image) {
            $image_path = public_path() . '/assets/news/' . $news->image;
            unlink($image_path);
        }

        $news->delete();
        return response()->json(['success' => 'News Deleted Successfully']);
    }

    public function bonuseLog(){
        $pageTitle = 'Active bonus';
        // $bonus = Bonuse::where('user_id', auth()->id())->latest()->paginate(getPaginate());
        
        $user = auth()->user();
        $user->is_welcome_message = true;
        $user->save();
        
        
        // Referral tramcard 
        $referralUsers = User::select('username', 'id')
            ->withCount(['deposits' => function ($query) {
                $query->where('amount', '>=', 300);
            }])
            ->where('ref_by', $user->id)
            ->where('is_ref_claim', 0)
            ->whereHas('deposits', function ($query) {
                $query->where('amount', '>=', 300)->where('status', 1);
            })
            ->paginate(1);
    
        
        
        
        $activeBonus = UserBonusList::where('user_id', auth()->user()->id)->first();
        return view($this->activeTemplate . 'user.bonus', compact('activeBonus', 'pageTitle', 'referralUsers'));
    }
    
    public function bonusClaim(){
        $user = auth()->user();
        $activeBonus = UserBonusList::where('user_id', $user->id)->first();
        if($activeBonus){
            try {
                DB::beginTransaction();
                $user->withdrawal += $activeBonus->initial_amount;
                $user->bonus_account = 0;
                $user->save();
                
                // Send Notification
                 $userNotify = new UserNotification;
                 $userNotify->user_id = $user->id;
                 $userNotify->title = "You have claimed ".showAmount($activeBonus->initial_amount). $user->currency." Please check the withdrawal balance.";
                 $userNotify->url = "";
                 $userNotify->save();
                
                 $userNotify->notify(new TramcardSendNotification($userNotify));
                
                DB::commit(); 
                $notify[] = ['success', 'You have claimed '.showAmount($activeBonus->initial_amount). $user->currency.' Please check the withdrawal balance.'];
                return back()->withNotify($notify);
            } catch (\Exception $e) {
                DB::rollback(); 
                 $notify[] = ['error', 'Something went wrong!'];
                 return back()->withNotify($notify);
            }
        }else{
            $notify[] = ['error', 'You have no active bonus now'];
            return back()->withNotify($notify);
        }
    }
    
    public function referralClaim($id){
           
        try{
            $user = auth()->user();
            $isActiveCard = UserBonusList::where('user_id', $user->id)->first();
            if($isActiveCard){
                $notify[] = ['error', 'You have already an active card'];
                return back()->withNotify($notify);
            }
        
            DB::beginTransaction();

            $totalSeconds = 24 * 60 * 60;
            $futureDateTime = Carbon::now()->addSeconds($totalSeconds);
            $referrar = User::where('id', $id)->first();
            $referrar->is_ref_claim = 1;
            $referrar->save();
            
            $bonus = new UserBonusList;
            $bonus->user_id = $user->id;
            $bonus->type = 'referral';
            $bonus->initial_amount = 300;
            $bonus->currency = $user->currency;
            $bonus->valid_time = $futureDateTime;
            $bonus->duration = 1;
            $bonus->duration_text = '24 hours';
            $bonus->save();
            
            //get user
            $user->bonus_account  = $bonus->initial_amount;
            $user->save();
             
             
            // Notify to user
            $userNotify = new UserNotification;
            $userNotify->user_id = $user->id;
            $userNotify->title = "Congratulations! You have to got 300 ".$user->currency." referral (".$referrar->username.")  bonus for 24 hours";
            $userNotify->url = "/user/bonus";
            $userNotify->save();
            DB::commit();
            $userNotify->notify(new TramcardSendNotification($userNotify));
            
            $notify[] = ['success', 'Successfully active referral bonus card'];
            return back()->withNotify($notify);
        } catch(\Exception $e){
            DB::rollback();
            $notify[] = ['error', 'Something went wrong!'];
            return back()->withNotify($notify);
        }
    }
}
