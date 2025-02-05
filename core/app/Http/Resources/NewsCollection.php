<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class NewsCollection extends JsonResource
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
                'details' => strip_tags(substr($this->data_values->description, 0, 100)),
                'image' => asset('assets/images/frontend/blog/'.$this->data_values->image),
                'created_at' => Carbon::parse($this->created_at)->format('Y-m-d  g:i:s A' ),
        ];
    }
}
