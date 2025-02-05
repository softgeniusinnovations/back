<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use App\Models\DepositBonusSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TransectionProviders;
use Illuminate\Support\Facades\DB;

class PromotionController extends Controller
{


   public function index()
   {
      // Retrieve promotions with status 1
      $promotions = DepositBonusSetting::where('status', 1)->orderBy('id', 'desc')->get();

      // Process each promotion
      foreach ($promotions as $promotion) {
         $days = is_string($promotion->days) ? json_decode($promotion->days, true) : $promotion->days;
         $promotion->days = is_array($days) ? implode(', ', $days) : $promotion->days;

         $providerIds = is_string($promotion->providers) ? json_decode($promotion->providers, true) : $promotion->providers;
         $providerNames = [];

         if (is_array($providerIds)) {
            $providers =  TransectionProviders::where('id', $providerIds)->get();

            foreach ($providerIds as $providerId) {
               if ($providerId === 'cash_agent') {
                  $providerNames[] = 'cash agent';
               } else {
                  $provider = $providers->firstWhere('id', $providerId);
                  if ($provider) {
                     $providerNames[] = $provider->name;
                  }
               }
            }
         }

         $promotion->providers = implode(', ', $providerNames);
      }

      // Prepare the payload for response
      $payload = [
         'data' => [
            'status'        => true,
            'data'          => $promotions,
            'app_message'   => 'Successful',
            'user_message'  => 'Successful'
         ]
      ];

      // Return JSON response with processed promotions data
      return response()->json($payload, 200);
   }
}
