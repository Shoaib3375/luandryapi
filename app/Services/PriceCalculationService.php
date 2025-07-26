<?php

namespace App\Services;

use App\Enums\PricingMethod;
use App\Models\Service;

class PriceCalculationService
{
    public function calculateBasePrice(Service $service, float $quantity): float
    {
        return match (PricingMethod::from($service->pricing_method)) {
            PricingMethod::WEIGHT => $service->price * $quantity,
            PricingMethod::FIXED => $service->price * (int) $quantity,
        };
    }
}