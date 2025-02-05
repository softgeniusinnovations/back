<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TramcardUser;
use App\Models\UserBonusList;
use App\Models\User;
use Carbon\Carbon;
class RemoveExpiredUserFromBounsAndTramcard extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'expireuser:remove';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove expired users from trmacard and other bouns';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $currentDateTime = Carbon::now();
         // Remove inactive tramcard user
        $expiredTramcardUsers = TramcardUser::where('valid_time', '<', $currentDateTime)->get();
        foreach ($expiredTramcardUsers as $tramcardUser) {
             $user = User::find($tramcardUser->user_id);
             if ($user) {
                if ($tramcardUser->rule_1 !== 1 || $tramcardUser->rule_2 !== 1 || $tramcardUser->rule_3 !== 1 || $tramcardUser->rule_4 !== 1) {
                    $user->bonus_account -= $tramcardUser->amount;
                    $user->save();
                }
             }
             $tramcardUser->delete();
        }
        
        // Remove inactive bonus user
        $expiredBonusUsers = UserBonusList::where('valid_time', '<', $currentDateTime)->get();
        foreach ($expiredBonusUsers as $active) {
            if($active->game_type==1){
                $user = User::find($active->user_id);
                if ($user) {
                    if ($active->rule_1 !== 1 || $active->rule_2 !== 1 || $active->rule_3 !== 1 || $active->rule_4 !== 1) {
                        $user->bonus_account -= $active->initial_amount;
                        $user->save();
                    }
                }
                $active->delete();
            }
            else{
                $user = User::find($active->user_id);
                if ($user) {
                    if ($active->rule_1 !== 1 || $active->rule_2 !== 1 || $active->rule_3 !== 1 || $active->rule_4 !== 1) {
                        $user->casino_bonus_account -= $active->initial_amount;
                        $user->save();
                    }
                }
                $active->delete();
            }

        }
        
        // \Log::info(count($expiredTramcardUsers) . ' expired TramcardUser records removed successfully.');
        // \Log::info(count($expiredBonusUsers) . ' expired active bonus records removed successfully.');
    
    }
}
