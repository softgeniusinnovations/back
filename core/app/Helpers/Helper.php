<?php
namespace App\Helpers;
use App\Models\Withdrawal;
use Carbon\Carbon;


class Helper{
    public static function checkTodaysWithdraw($user_id){

        $user_today = Carbon::today();
        $start_of_today = $user_today->startOfDay();
        $end_of_today = $user_today->endOfDay();

        $withdrawal = Withdrawal::where('user_id', $user_id)
            ->where('status',1)
            ->whereBetween('created_at', [$start_of_today, $end_of_today])
            ->first();

        if ($withdrawal) {
            return true;
        } else {
            return false;
        }
    }

}