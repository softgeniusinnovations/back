<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use phpDocumentor\Reflection\Types\Collection;

class PagignationCollection extends JsonResource
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
                'current_page' => $this->currentPage,
                'next_page' => $this->nextPage,
                'per_page' => $this->itemsPerPage,
                'total_page' => $this->totalPages,
                'total_items' => $this->totalItems
        ];
    }
}
