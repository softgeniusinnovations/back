<?php

namespace Database\Seeders;

use App\Models\TransectionProviders;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TransectionProviderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        dump('Seeder Running ....');
        dump('Transection Provider Initializations ....');

        // Providers list
        $transectionProviders = [
            'Nagad',
            'bKash',
            'Rocket',
            'SureCash',
            'MYCash',
            'FirstCash',
            'Upay',
            'OK Wallet',
            'TeleCash',
            'Meghna Pay'
        ];

        dump('Transection proviers table truncate...');
        // Trancate
        DB::table('transection_providers')->truncate();

        dump('Transection proviers Insertion...');
        foreach ($transectionProviders as $provider) {
            if (!TransectionProviders::where('name', $provider)->exists()) {
                TransectionProviders::create([
                    'name' => $provider,
                    'country_code' => 'BD',
                    'dep_min_am' => '300',
                    'dep_max_am' => '20000',
                    'with_min_am' => '300',
                    'with_max_am' => '20000',
                    'note_dep' => '',
                    'note_with' => '',
                ]);
            }
        }

        dump('Successfully completed', $transectionProviders);
    }
}
