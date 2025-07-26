<?php

namespace App\Repositories;

use App\Models\Coupon;

class CouponRepository
{
    public function findValidCoupon(string $code): ?Coupon
    {
        return Coupon::where('code', $code)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->first();
    }
}