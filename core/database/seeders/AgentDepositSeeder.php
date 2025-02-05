<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AgentDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $agentDeposits = [
            [
                'id' => 1,
                'agent_id' => 132,
                'wallet_id' => 2,
                'currency' => 'BDT',
                'trx' => 'VSKF4YKNQH1D',
                'amount' => '10',
                'rate' => '1',
                'deposit_trx' => 'WEQWEWQ',
                'depositor_account' => 'aSDWEFSADASDWDASDWQE',
                'deposit_currency' => 'USDT',
                'file' => null,
                'feedback' => null,
                'status' => 1,
                'created_at' => '2024-01-07 21:38:09',
                'updated_at' => '2024-01-07 21:38:09',
            ],
            [
                'id' => 2,
                'agent_id' => 132,
                'wallet_id' => 4,
                'currency' => 'BDT',
                'trx' => 'B99ZX95EKGQA',
                'amount' => '3000',
                'rate' => '1',
                'deposit_trx' => 'WEQWEWQs',
                'depositor_account' => 'aSDWEFSADASDWDASDWQEa',
                'deposit_currency' => 'BNB',
                'file' => null,
                'feedback' => null,
                'status' => 1,
                'created_at' => '2024-01-07 21:43:03',
                'updated_at' => '2024-01-07 21:43:03',
            ],
            // Add other agent_deposits records here...
        ];

        DB::table('agent_deposits')->insert($agentDeposits);
    }
}
