<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\ResourceCollection;

class CategoryResourceCollection extends ResourceCollection
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
            'data' => $this->collection,
            'meta' => [
                'page' => $request->page,
                'total_page' => $this->lastPage(),
                'count' => $this->count(),
                'per_page' => $request->per_page,
                'total' => $this->total(),
            ],
        ];
    }
}
