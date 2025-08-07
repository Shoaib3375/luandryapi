<?php

namespace App\Services;

use App\Enums\PricingMethod;
use App\Models\Service;

class PriceCalculationService
{
    public function calculateBasePrice(Service $service, float $quantity): float
    {
        return match (PricingMethod::from($service->pricing_method)) {
            PricingMethod::PER_KG, PricingMethod::FLAT_RATE => $service->price * $quantity,
            PricingMethod::PER_ITEM => $service->price * (int) $quantity,
        };
    }
}
