<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\AffiliateWithdrawSetting;
use App\Models\User;
use Carbon\Carbon;

class TransferAffiliateBalance extends Command
{
    protected $signature = 'affiliate:transfer-balance';
    protected $description = 'Transfer affiliate temp balance to affiliate balance if today is the specified day';

    public function handle()
    {

        $today = Carbon::now()->format('l');


        $affiliateSetting = AffiliateWithdrawSetting::first();


        if ($affiliateSetting && $affiliateSetting->withdraw_date === $today) {

            $users = User::where('affiliate_temp_balance', '>', 0)->get();

            foreach ($users as $user) {

                $user->affiliate_balance += $user->affiliate_temp_balance;
                $user->affiliate_temp_balance = 0;
                $user->save();
            }

            $this->info("Affiliate balances transferred successfully.");
        } else {
            $this->info("Today is not the withdrawal day. No transfer performed.");
        }

        return Command::SUCCESS;
    }
}
