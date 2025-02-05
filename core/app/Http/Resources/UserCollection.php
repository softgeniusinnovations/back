<?php

namespace App\Http\Resources;

use App\Models\TramcardUser;
use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Collection;

class UserCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $tramcardData = TramcardUser::where('user_id', $this->id)->first();
        return [
            'id'                        => $this->id,
            'user_id'                   => $this->user_id,
            'firstname'                 => $this->firstname,
            'lastname'                  => $this->lastname,
            'username'                  => $this->username,
            'email'                     => $this->email,
            'profile_photo'             => $this->profile_photo,
            'mobile'                    => $this->mobile,
            'dob'                       => $this->dob,
            'country_code'              => $this->country_code,
            'address'                   => $this->address,
            'ref_by'                    => $this->ref_by,
            'currency'                  => $this->currency,
            'balance'                   => $this->balance,
            'deposit'                   => $this->balance,
            'occupation'                => $this->occupation,
            'withdrawal'                => $this->withdrawal ,
            'bonus_account'             => $this->bonus_account,
            'casino_bonus_account'             => $this->casino_bonus_account,
            'bonus'             => $this->bonus_account,
            'affiliate_temp_balance'    => $this->affiliate_temp_balance,
            'affiliate_balance'         => $this->affiliate_balance,
            'can_withdraw_after'         => $this->can_withdraw_after,
            'status'                    => $this->status,
            'kyc_data'                  => $this->kyc_data,
            'kv'                        => $this->kv,
            'kyc'                       => $this->kv == 1 ? true : false,
            'is_welcome_message'        => $this->is_welcome_message,
            'ev'                        => $this->ev,
            'sv'                        => $this->sv,
            'profile_complete'          => $this->profile_complete,
            'ts'                        => $this->ts,
            'tv'                        => $this->tv,
            'tsc'                       => $this->tsc,
            'ban_reason'                => $this->ban_reason,
            'is_affiliate'              => $this->is_affiliate,
            'profile_mode'              => $this->profile_mode,
            'one_time_pass'             => $this->one_time_pass,
            'youtube_link'              => $this->youtube_link,
            'website'                   => $this->website,
            'is_one_click_user'         => $this->is_one_click_user,
            'oev'                       => $this->oev,
            'omv'                       => $this->omv,
            'referal_link'              => url($this->referral_code),
            'tramcard' => $tramcardData ? $tramcardData->amount > 0 ? $tramcardData->amount : 0 : 0,
            'notifications' => [
                'total' => $this->userNotifications->where('is_read', 0)->count(),
                'latest' => UserNotificationCollection::collection($this->userNotifications),
            ]
        ];
    }
}
