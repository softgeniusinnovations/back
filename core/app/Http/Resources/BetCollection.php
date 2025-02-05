<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\Json\ResourceCollection;

class BetCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'bet_number' => $this->bet_number,
            'category' => $this->category,
            'leauge' => $this->leauge,
            'oddId' => $this->oddId,
            'bookmarkId' => $this->bookmarkId,
            'matchId' => $this->matchId,
            'odds' => $this->odds,
            'odds_point' => $this->odds_point,
            'odds_name' => $this->odds_name,
            'checker' => $this->checker,
            'api_source_type' => $this->api_source_type,
            'is_live' => $this->is_live,
            'type' => $this->type == 1 ? 'Single' : ($this->type == 2 ? 'Multi' : null),
            'stake_amount' => number_format($this->stake_amount, 2),
            'return_amount' => number_format($this->return_amount, 2),
            'amount_returned' => number_format($this->amount_returned, 2),
            'status' =>  $this->getStatus($this->status),
            'team_one' => $this->team1,
            'team_two' => $this->team2,
            'market_name' => $this->market_name,
            'odd_details' => $this->odd_details,
            'result_time' => $this->result_time,
            'comments' => $this->comments,
            'bet_details' => $this->bets,
            'created_at'=>$this->created_at
        ];
    }
    
    private function getStatus($status)
    {
        switch ((int)$status) {
            case 1:
                return 'Win';
            case 2:
                return 'Pending';
            case 3:
                return 'Lose';
            case 4:
                return 'Refunded';
            case 5:
                return 'Half Win';
            case 6:
                return 'Half Loss';
            default:
                return 'Unknown';
        }
    }
}
