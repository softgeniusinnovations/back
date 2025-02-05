<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Resources\Json\JsonResource;

class PromotionsCollection extends JsonResource
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
                'title' => $this->title,
                'slug' => $this->slug,
                'image' => $this->image,
                'details' => $this->details,
                'promo_code' => $this->promo_code,
                'promo_percentage' => $this->promo_percentage,
                'promo_amount' => $this->promo_amount,
                'is_admin_approved' => $this->is_admin_approved,
                'created_at' => $this->created_at->format('d M, Y'),
                'admin_comment' => $this->admin_comment,
                'status' => $this->status,
        ];
    }
}
