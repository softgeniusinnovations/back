<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class TicketBetsCollection extends JsonResource
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
                'bet_num' => $this->bet_num,
                'bet_number' => $this->bet_number,
                'type' => $this->type,
                'amount' => $this->amount
        ];
    }
}
