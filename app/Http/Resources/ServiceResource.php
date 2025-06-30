<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ServiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'name'           => $this->name,
            'category'       => $this->category,
            'price'          => $this->price,
            'pricing_method' => $this->pricing_method,
            'price_per_unit' => $this->price_per_unit,
        ];
    }
}
