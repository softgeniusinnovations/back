<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminTransectionProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $adminTransectionProviders = [
            [
                'id' => 35,
                'admin_id' => 163,
                'transection_provider_id' => 1,
                'wallet_name' => 'Nagad wallet',
                'mobile' => '0174550777',
                'status' => 1,
            ],
            [
                'id' => 36,
                'admin_id' => 163,
                'transection_provider_id' => 4,
                'wallet_name' => 'Sure cash wallet',
                'mobile' => '0174550775',
                'status' => 1,
            ],
            [
                'id' => 37,
                'admin_id' => 163,
                'transection_provider_id' => 6,
                'wallet_name' => 'First Cash wallet',
                'mobile' => '0174550787',
                'status' => 1,
            ],
            [
                'id' => 38,
                'admin_id' => 163,
                'transection_provider_id' => 7,
                'wallet_name' => 'uPay wallet',
                'mobile' => '0174550775',
                'status' => 1,
            ],
            [
                'id' => 39,
                'admin_id' => 132,
                'transection_provider_id' => 1,
                'wallet_name' => 'Nagad wallet',
                'mobile' => '0174550775',
                'status' => 1,
            ],
            [
                'id' => 40,
                'admin_id' => 132,
                'transection_provider_id' => 1,
                'wallet_name' => 'Nagad wallet two',
                'mobile' => '0174550776',
                'status' => 1,
            ],
            [
                'id' => 41,
                'admin_id' => 132,
                'transection_provider_id' => 2,
                'wallet_name' => 'Bkash wallet',
                'mobile' => '0174550778',
                'status' => 1,
            ],
            // Add other admin_transection_providers records here...
        ];

        DB::table('admin_transection_providers')->insert($adminTransectionProviders);
    }
}
