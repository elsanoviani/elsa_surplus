<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CategoryProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            "product_id" => $this->product_id,
            "category_id" => $this->category_id,
            "product_name" => $this->product_name,
            "category_name" => $this->category_name
        ];
    }
}
