<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class CasinoCollection extends JsonResource
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
                'game_id' => $this->game_id,
                'source' => $this->source,
                'team_one_id' => $this->team_one_id,
                'team_two_id' => $this->team_two_id,
                'league_id' => $this->league_id,
                'slug' => $this->slug,
                'start_time' => $this->start_time,
                'bet_start_time' => $this->bet_start_time,
                'bet_end_time' => $this->bet_end_time,
                'status' => $this->bet_end_time
        ];
    }
}
