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
            'order_items'  => $this->whenLoaded('orderItems', function () {
                return $this->orderItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'service_id' => $item->service_id,
                        'service' => new ServiceResource($item->whenLoaded('service')),
                        'quantity' => $item->quantity,
                        'unit_price' => $item->unit_price,
                        'total_price' => $item->total_price,
                    ];
                });
            }),
            'note'         => $this->note,
            'total_price'  => $this->total_price,
            'status'       => $this->status,
            'payment_status' => $this->payment_status,
            'coupon_code'  => $this->coupon_code,
            'guest_name'   => $this->guest_name,
            'guest_email'  => $this->guest_email,
            'guest_phone'  => $this->guest_phone,
            'guest_address' => $this->guest_address,
            'customer_info' => $this->user_id ? [
                'type' => 'user',
                'name' => $this->whenLoaded('user', fn() => $this->user->name),
                'email' => $this->whenLoaded('user', fn() => $this->user->email),
            ] : [
                'type' => 'guest',
                'name' => $this->guest_name,
                'email' => $this->guest_email,
                'phone' => $this->guest_phone,
                'address' => $this->guest_address,
            ],
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
