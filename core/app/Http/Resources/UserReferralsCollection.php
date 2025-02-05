<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Collection;

class UserReferralsCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'user_id' => $this->user_id ?? '',
            'firstname' => $this->firstname ?? '',
            'lastname' => $this->lastname ?? '',
            'username ' => $this->username ?? ''
        ];
    }
}
