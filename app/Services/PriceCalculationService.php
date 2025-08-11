<?php

namespace App\Services;

use App\Enums\PricingMethod;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class PriceCalculationService
{
    public function calculateBasePrice(Service $service, float $quantity): float
    {
        return match (PricingMethod::from($service->pricing_method)) {
            PricingMethod::FLAT_RATE => $service->price,
            PricingMethod::PER_KG => $service->price * $quantity,
            PricingMethod::PER_ITEM => $service->price * (int) ceil($quantity),
        };
    }
}
