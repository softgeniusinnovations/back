<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\User;
use App\Models\UserBonusList;
use App\Models\UserNotification;
use Illuminate\Support\Carbon;
use App\Http\Controllers\Controller;
use App\Notifications\TramcardSendNotification;
use Illuminate\Support\Facades\DB;
class NewsController extends Controller
{
   public function bonusLog()
   {
      $user = auth()->user();
      $user->is_welcome_message = true;
      $user->save();


      // Referral tramcard 
      $referralUsers = User::select('username', 'id')
         ->withCount(['deposits' => function ($query) {
            $query->where('amount', '>=', 300);
         }])
         ->where('ref_by', $user->id)
         ->where('is_ref_claim', 0)
         ->whereHas('deposits', function ($query) {
            $query->where('amount', '>=', 300)->where('status', 1);
         })
         ->get();

      $data = [
         'active' =>  UserBonusList::where('user_id', auth()->user()->id)->first(),
         'referrals' => $referralUsers,
      ];

      $payload = [
         'status'         => true,
         'data' => $data,
         'app_message'  => 'Successfully Retrieve Data',
         'user_message' => 'Successfully Retrieve Data'
      ];
      return response()->json($payload, 200);
   }

   public function bonusClaim()
   {
      $user = auth()->user();
      $activeBonus = UserBonusList::where('user_id', $user->id)->first();
      if ($activeBonus) {
         try {
            DB::beginTransaction();
//            $user->withdrawal += $activeBonus->initial_amount;
//            $user->balance += $activeBonus->initial_amount;
            $user->bonus_account += $activeBonus->initial_amount;
            $user->save();
            
            // Send Notification
            $userNotify = new UserNotification;
            $userNotify->user_id = $user->id;
            $userNotify->title = "You have claimed " . showAmount($activeBonus->initial_amount) . $user->currency . " Please check the withdrawal balance.";
            $userNotify->url = "";
            $userNotify->save();

            $userNotify->notify(new TramcardSendNotification($userNotify));

            DB::commit();
            $payload = [
               'status'         => true,
               'data' => [],
               'app_message'  => 'You have claimed ' . showAmount($activeBonus->initial_amount) . $user->currency . ' Please check the withdrawal balance.',
               'user_message' => 'You have claimed ' . showAmount($activeBonus->initial_amount) . $user->currency . ' Please check the withdrawal balance.'
            ];
            return response()->json($payload, 200);
         } catch (\Exception $e) {
            DB::rollback();
            $payload = [
               'status'         => true,
               'data' => [],
               'app_message'  => 'Something went wrong!',
               'user_message' => 'Something went wrong!'
            ];
            return response()->json($payload, 200);
         }
      } else {
         $payload = [
            'status'         => true,
            'data' => [],
            'app_message'  => 'No active bonus found!',
            'user_message' => 'No active bonus found!'
         ];
         return response()->json($payload, 200);
      }
   }

   public function referralClaim($id)
   {

      try {
         $user = auth()->user();
         $isActiveCard = UserBonusList::where('user_id', $user->id)->first();
         if ($isActiveCard) {
            $payload = [
               'status'         => true,
               'data' => [],
               'app_message'  => 'You have already an active card',
               'user_message' => 'You have already an active card'
            ];
            return response()->json($payload, 200);
         }

         DB::beginTransaction();

         $totalSeconds = 24 * 60 * 60;
         $futureDateTime = Carbon::now()->addSeconds($totalSeconds);
         $referrar = User::where('id', $id)->first();
         $referrar->is_ref_claim = 1;
         $referrar->save();

         $bonus = new UserBonusList;
         $bonus->user_id = $user->id;
         $bonus->type = 'referral';
         $bonus->initial_amount = 300;
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
         $userNotify->title = "Congratulations! You have to got 300 " . $user->currency . " referral (" . $referrar->username . ")  bonus for 24 hours";
         $userNotify->url = "/user/bonus";
         $userNotify->save();
         DB::commit();
         $userNotify->notify(new TramcardSendNotification($userNotify));

         $payload = [
            'status'         => true,
            'data' => [],
            'app_message'  => 'Successfully active referral bonus card',
            'user_message' => 'Successfully active referral bonus card'
         ];
         return response()->json($payload, 200);
      } catch (\Exception $e) {
         DB::rollback();
         $payload = [
            'status'         => true,
            'data' => [],
            'app_message'  => 'Something went wrong.',
            'user_message' => 'Something went wrong.'
         ];
         return response()->json($payload, 200);
      }
   }
}