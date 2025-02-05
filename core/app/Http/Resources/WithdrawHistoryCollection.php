<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class WithdrawHistoryCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'currency' => Auth::user()->currency,
            'date' => $this->created_at->format('d.m.Y'),
            'payout' => showAmount($this->amount + $this->charge) . ' ' . __(getSymbol(userCurrency())),
            'revenue' => showAmount($this->available_amount) . ' ' . __(getSymbol(userCurrency())),
            'balance' => showAmount($this->available_amount - ($this->amount + $this->charge)) . ' ' . __(getSymbol(userCurrency())),
            'status_badge' => $this->statusBadge,
            'status' => $this->status,
            'wallet_number' => $this->phone,
            'order_number' => $this->trx,
            'method' => $this->method ? $this->method : null,  // Null check for method
            'transectionProviders' => $this->whenLoaded('transectionProviders', fn() => $this->transectionProviders),

//            'method' => $this->whenLoaded('method', fn() => $this->method->toArray()),  // Safely include WithdrawMethod data
//            'transectionProviders' => $this->whenLoaded('transectionProviders', fn() => $this->transectionProviders->map->toArray()),
        ];
    }
}
