<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Models\Tramcard;
use App\Models\Currency;
use App\Models\User;
use App\Models\TramcardUser;
use App\Models\UserNotification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Notifications\TramcardSendNotification;
use Carbon\Carbon;
class TramCardController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $pageTitle = "Tramcard list";
        $tramcards = Tramcard::latest()->searchable(['title', 'trx'])->paginate(10);
        return view('admin.event.tramcard.index', compact('pageTitle', 'tramcards'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $pageTitle = 'Tramcard create';
        $currency = Currency::get();
        return view('admin.event.tramcard.create', compact('pageTitle', 'currency'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
           'title' => 'required|unique:tramcards,title|max:254',
           'value' => 'required|numeric|min:0',
           'currency' => 'required',
           'minimum_bet' => 'required|numeric|min:1',
           'odds' => 'required|numeric|min:0',
           'file' =>  'required|image|mimes:jpeg,png,jpg|max:1024',
        ]);
        
        try{
            if($request->file){
                $file = $request->file('file');
                $fileName = time() . '.' . $file->extension();
            }
            
            $tramcard = new Tramcard;
            $tramcard->title = $request->title;
            $tramcard->trx = getTrx(8);
            $tramcard->value = $request->value;
            $tramcard->currency = $request->currency;
            $tramcard->minimum_bet = $request->minimum_bet;
            $tramcard->odds = $request->odds;
            $tramcard->image = $fileName;
            $tramcard->rules = $request->rules;
            $tramcard->save();
            if($tramcard){
                Storage::put('/public/event/tramcard/' . $fileName, file_get_contents($file));
            }
            $notify[] = ['success', "New Tramcard add successfully"];
            return to_route('admin.event.tramcard.list')->withNotify($notify);
        } catch(\Exception $e){
            $notify[] = ['error', "Something went wrong"];
            return back()->withNotify($notify);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $pageTitle = 'Tramcard Details';
        $tramcard = Tramcard::findOrFail($id);
        return view('admin.event.tramcard.show',compact('pageTitle','tramcard'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $pageTitle = 'Tramcard Edit';
        $currency = Currency::get();
        $tramcard = Tramcard::findOrFail($id);
        return view('admin.event.tramcard.edit', compact('pageTitle','tramcard', 'currency'));
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
        $request->validate([
           'title' =>  ['required', 'string','max:254', Rule::unique('tramcards')->ignore($id)],
           'value' => 'required|numeric|min:0',
           'currency' => 'required',
           'minimum_bet' => 'required|numeric|min:1',
           'odds' => 'required|numeric|min:0',
           'file' =>  'nullable|image|mimes:jpeg,png,jpg|max:1024',
        ]);
        
       try{
            $tramcard = Tramcard::findOrFail($id);
                if ($request->file) {
                    $file = $request->file('file');
                    $fileName = time() . '.' . $file->extension();
                    $previousFile = $tramcard->file;
                    $tramcard->image = $fileName;
                }
                $tramcard->title = $request->title;
                $tramcard->value = $request->value;
                $tramcard->currency = $request->currency;
                $tramcard->minimum_bet = $request->minimum_bet;
                $tramcard->odds = $request->odds;
                $tramcard->image = $fileName;
                $tramcard->rules = $request->rules;
                if($tramcard->save()){
                    if ($request->file && Storage::exists('/public/event/tramcard/' . $previousFile)) {
                        Storage::delete('/public/event/tramcard/' . $previousFile);
                        Storage::put('/public/event/tramcard/' . $fileName, file_get_contents($file));
                    }
                }
                $notify[] = ['success', "Tramcard update successfully"];
                return to_route('admin.event.tramcard.list')->withNotify($notify);
       } catch(\Exception $e){
            $notify[] = ['error', "Something went wrong"];
            return back()->withNotify($notify);
       }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
    
    // Send page
    public function sendTramcard($id) {
        $pageTitle = 'Send Tramcard';
        $tramcard = Tramcard::findOrFail($id);
        $bettors = User::where('currency', $tramcard->currency)->where('status', 1)->get();
        $activeBettors = DB::table('tramcard_users')->leftJoin('users', 'tramcard_users.user_id', '=', 'users.id')->select('tramcard_users.*', 'users.user_id as username')->latest()->paginate(10);
        return view('admin.event.tramcard.send', compact('pageTitle','tramcard','bettors', 'activeBettors'));
    }
    
    // Send user page
    public function sendTramcardByUser(Request $request, $id){
        $request->validate([
            'bettor' => 'required',
            'remark' => 'nullable',
            'valid_time' => 'required'
        ]);
        try{
            
            $existBettor = TramcardUser::where('user_id', $request->bettor)->where('tramcard_id', $id)->where('valid_time','>=', Carbon::now())->first();
            if($existBettor){
                $notify[] = ["error", "This bettor already tramcard activated for this ".$existBettor->tramcard_trx . " tramcard tracking number"];
                return back()->withNotify($notify);
            }else{
                TramcardUser::where('user_id', $request->bettor)->where('tramcard_id', $id)->delete();
            }
            DB::beginTransaction();
            $totalSeconds = 24 * 60 * 60 * $request->valid_time;
            $futureDateTime = Carbon::now()->addSeconds($totalSeconds);
            
            if($request->valid_time == 7){
                $time = '7 days';
            }else if($request->valid_time == 14600){
                $time = 'Life time';
            }else{
                $time = $request->valid_time * 24 . ' hours';
            }
            
            $tramcard = Tramcard::findOrFail($id);
            $data = new TramcardUser;
            $data->tramcard_id = $request->id;
            $data->user_id = $request->bettor;
            $data->valid_time = $request->valid_time != 14600 ? $futureDateTime->subSecond() : Carbon::now()->addYears(100);
            $data->duration = $request->valid_time;
            $data->duration_text = $time;
            $data->remarks = $request->remark;
            $data->amount = $tramcard->value;
            $data->currency = $tramcard->currency;
            $data->tramcard_trx = $tramcard->trx;
            $data->minimum_bet = $tramcard->minimum_bet;
            $data->odds = $tramcard->odds;
            $data->save();
//            $user=User::where('id',$request->bettor)->first();

            $transaction               = new Transaction();
            $transaction->user_id      = $request->bettor;
            $transaction->amount       = $tramcard->value;
//            $transaction->post_balance = $user->balance + $tramcard->value;
            $transaction->trx_type     = '+';
            $transaction->trx          =  $tramcard->trx;
            $transaction->transection_type          = 3;
            $transaction->remark       = 'get tramcard';
            $transaction->details      = 'For get tramcard';
            $transaction->save();
            
            
            
            $userNotify = new UserNotification;
            $userNotify->user_id = $request->bettor;
            $userNotify->title = "Congratulations! You have to got a new tramcard for ".$time;
            $userNotify->url = "/user/tramcards";
            $userNotify->save();
            
            $userNotify->notify(new TramcardSendNotification($userNotify));
            
            DB::commit();
            $notify[] = ["success","Tramcard send for new bettor"];
            return back()->withNotify($notify);
        } catch(\Exception $e){
            DB::rollback();
            $notify[] = ["error",$e->getMessage()];
            return back()->withNotify($notify);
        }
        
    }
}
