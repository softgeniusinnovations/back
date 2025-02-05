<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class DepositHistoryCollection extends JsonResource
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
                'gateway' => $this->transectionProviders->name ?? 'Cash Agent',
                'trx' =>  $this->method_trx_number,
                'amount' => showAmount($this->amount + $this->charge).' '.__(userCurrency()),
                'deposit_no' =>  $this->trx,
                'initiated' => showDateTime($this->created_at),
                'initiated_human_readable' => diffForHumans($this->created_at),
                'status_badge' => $this->statusBadge,
                'status'=>$this->status,
                'details' => $this->detail != null ? json_encode($this->detail) : '',
                'withdraw_information' => $this->withdraw_information,
                'admin_feedback' => $this->admin_feedback
        ];
    }
}
