<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsDetailsCollection extends JsonResource
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
                'title' => $this->data_values->title,
                'details' => $this->data_values->description,
                'image' => asset('assets/images/frontend/blog/'.$this->data_values->image),
                'created_at' => Carbon::parse($this->created_at)->format('Y-m-d  g:i:s A' ),
        ];
    }
}
