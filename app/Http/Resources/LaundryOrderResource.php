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
            'coupon_code'  => $this->coupon_code,
            'created_at'   => $this->created_at,
            
            // User information (for admin)
            'user' => $this->whenLoaded('user', function () {
                return [
                    'id' => $this->user->id,
                    'name' => $this->user->name,
                    'email' => $this->user->email,
                ];
            }),
            
            // Delivery address
            'delivery_address' => $this->whenLoaded('deliveryAddress', function () {
                return [
                    'street_address' => $this->deliveryAddress->street_address,
                    'city' => $this->deliveryAddress->city,
                    'state' => $this->deliveryAddress->state,
                    'postal_code' => $this->deliveryAddress->postal_code,
                    'type' => $this->deliveryAddress->type,
                ];
            }),
        ];
    }
}
