<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Collection;

class PaymentMethodCollection extends JsonResource
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
                'id' => $this->id,
                'name' => $this->name,
                'country_code' => $this->country_code,
                'file' => asset('core/public/storage/providers/' . $this->file),
                'currency' => $this->currency,
                'minimum_deposit_amount' => $this->dep_min_am,
                'maximum_deposit_amount' => $this->dep_max_am,
                'minimum_withdraw_amount' => $this->with_min_am,
                'maximum_withdraw_amount' => $this->with_max_am,
        ];
    }
}
