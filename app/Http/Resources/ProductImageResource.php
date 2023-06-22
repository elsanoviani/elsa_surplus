<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ProductImageResource extends JsonResource
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
            "image_id" => $this->image_id,
            "product_name" => $this->product_name,
            "image_name" => $this->image_name
        ];
    }
}
