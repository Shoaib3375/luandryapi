<?php

namespace App\Services;

use App\DTOs\CouponDiscountDTO;
use App\Repositories\CouponRepository;

class CouponService
{
    public function __construct(
        private readonly CouponRepository $couponRepository
    ) {}

    public function calculateDiscount(float $total, ?string $couponCode): CouponDiscountDTO
    {
        if (!$couponCode) {
            return new CouponDiscountDTO($total, false);
        }

        $coupon = $this->couponRepository->findValidCoupon($couponCode);

        if (!$coupon) {
            return new CouponDiscountDTO($total, false);
        }

        $discountAmount = $total * $coupon->discount_percent / 100;
        $discountedTotal = $total - $discountAmount;

        return new CouponDiscountDTO(
            total: $discountedTotal,
            couponApplied: true,
            discountPercent: $coupon->discount_percent,
            discountAmount: $discountAmount
        );
    }
}