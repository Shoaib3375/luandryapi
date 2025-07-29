<?php

namespace App\DTOs;

readonly class CreateOrderDTO
{
    public function __construct(
        public int     $userId,
        public int     $serviceId,
        public float   $quantity,
        public float   $totalPrice,
        public ?string $note = null,
        public ?string $couponCode = null,
        public ?int    $deliveryAddressId = null,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            serviceId: $data['service_id'],
            quantity: $data['quantity'],
            totalPrice: $data['total_price'],
            note: $data['note'] ?? null,
            couponCode: $data['coupon_code'] ?? null,
            deliveryAddressId: $data['delivery_address_id'] ?? null,
        );
    }
}
