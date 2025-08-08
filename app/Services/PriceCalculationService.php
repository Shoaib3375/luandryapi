<?php

namespace App\Services;

use App\Enums\PricingMethod;
use App\Models\Service;
use Illuminate\Support\Facades\Cache;

class PriceCalculationService
{
    public function calculateBasePrice(Service $service, float $quantity): float
    {
        $cacheKey = "price:{$service->id}:{$quantity}";
        
        return Cache::remember($cacheKey, 600, function () use ($service, $quantity) {
            return match (PricingMethod::from($service->pricing_method)) {
                PricingMethod::PER_KG, PricingMethod::FLAT_RATE => $service->price * $quantity,
                PricingMethod::PER_ITEM => $service->price * (int) $quantity,
            };
        });
    }
}
