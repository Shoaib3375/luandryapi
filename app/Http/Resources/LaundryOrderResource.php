<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LaundryOrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'user_id'      => $this->user_id,
            'service'      => new ServiceResource($this->whenLoaded('service')),
            'quantity'     => $this->quantity,
            'note'         => $this->note,
            'total_price'  => $this->total_price,
            'status'       => $this->status,
            'payment_status' => $this->payment_status,
            'created_at'   => $this->created_at,
        ];
    }
}
