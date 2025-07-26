<?php

namespace App\DTOs;

readonly class CouponDiscountDTO
{
    public function __construct(
        public float  $total,
        public bool   $couponApplied,
        public ?float $discountPercent = null,
        public ?float $discountAmount = null,
    ) {}
}
