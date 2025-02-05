<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;
use App\Models\UserBonusList;
use App\Models\User;
use App\Models\Bet;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Notifications\TramcardSendNotification;
class BirthdayGift extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'birthday:gift';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bonus assign for a birthday boy, If has no  active card';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDateTime = Carbon::now();
        $activeUsers = UserBonusList::where('valid_time', '>', $currentDateTime)->pluck('user_id');
        $users = User::select('id','currency', 'bonus_account')
            ->whereMonth('dob', '=', $currentDateTime->format('m'))
            ->whereDay('dob', '=', $currentDateTime->format('d'))
            ->whereNotIn('id', $activeUsers)
            ->whereDate('created_at', '<=', $currentDateTime->subDays(90)->format('Y-m-d'))
            ->get();
        
        foreach($users as $user){
            $doubleCheck = UserBonusList::where('user_id', $user->id)->first();
            if(!$doubleCheck){
                $is_valid=Helper::checkTodaysWithdraw($user->id);
                if($is_valid==false){
                    $this->birthdayGift($user);
                }

            }
            
        }
        
       // \Log::info( count($users));
        
        // return $activeUser;
    }
    
     // Welcome bonus 
    public function birthdayGift($user){
        try{

            DB::beginTransaction();

            $totalSeconds = 24 * 60 * 60;
            $futureDateTime = Carbon::now()->addSeconds($totalSeconds);
            $startDate = Carbon::now()->subDays(90);
            $totalLoss = Bet::where('status', 3)
                ->where('result_time', '>=', $startDate)
                ->where('user_id', $user->id)
                ->sum('stake_amount');
            $lossAmount = $totalLoss * 0.02;
            
            
            $bonus = new UserBonusList;
            $bonus->user_id = $user->id;
            $bonus->type = 'birthday';
            $bonus->initial_amount = $lossAmount;
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
            $userNotify->title = "Congratulations! You have to got ".$lossAmount . $user->currency." birthday bonus for 24 hours";
            $userNotify->url = "/user/bonus";
            $userNotify->save();
            DB::commit();
            $userNotify->notify(new TramcardSendNotification($userNotify));
        } catch(\Exception $e){
            DB::rollback();
            \Log::info($e->getMessage());
        }
    }
}
