<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $admins = [
            [
                'id' => 1,
                'name' => 'Super Admin',
                'email' => 'root@gmail.com',
                'username' => 'root',
                'country_code' => null,
                'currency' => 'BDT',
                'phone' => null,
                'deposit_commission' => 0,
                'withdraw_commission' => 0,
                'balance' => 0.00000000,
                'ver_code' => null,
                'type' => null,
                'address' => null,
                'email_verified_at' => '2023-11-18 19:48:57',
                'image' => '6238276ac25d11647847274.png',
                'password' => '$2y$10$nFssfOsfaUL3H0L0wR2zsupxuy/OMYgpAr2M2LmX9sdOz.SR0lJa2',
                'remember_token' => 'NXv4hW8eDvK5lddP24sGMm4QiHQPBn9kOnVVga3CgmqFJnHQtUZOVTHOfMnJ',
                'bot_token' => null,
                'bot_username' => null,
                'channel_id' => null,
                'telegram_link' => null,
                'is_login' => null,
                'created_at' => '2023-11-18 19:45:42',
                'updated_at' => '2024-01-19 18:35:27',
                'status' => 1,
            ],
            // Add other admin records here...
        ];

        Admin::insert($admins);
    }
}
