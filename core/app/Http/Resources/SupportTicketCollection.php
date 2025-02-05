<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class SupportTicketCollection extends JsonResource
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
                'user_id' => $this->user_id,
                'name' => $this->name,
                'email' => $this->email,
                'ticket' => $this->ticket,
                'subject' => $this->subject,
                'trx_no' => $this->trx_no,
                'trx_date' => $this->trx_date,
                'bet_id' => $this->bet_id,
                'status' => $this->status,
                'priority' => $this->priority,
                'last_reply' => $this->last_reply,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at
        ];
    }
}
