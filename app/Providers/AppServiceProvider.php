<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            \App\Repositories\OrderRepository::class,
            \App\Repositories\OrderRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\CouponRepository::class,
            \App\Repositories\CouponRepository::class
        );
        
        $this->app->bind(
            \App\Repositories\ServiceRepository::class,
            \App\Repositories\ServiceRepository::class
        );
        
        $this->app->bind(
            \App\Services\OrderService::class,
            \App\Services\OrderService::class
        );
        
        $this->app->bind(
            \App\Services\CouponService::class,
            \App\Services\CouponService::class
        );
        
        $this->app->bind(
            \App\Services\PriceCalculationService::class,
            \App\Services\PriceCalculationService::class
        );
        
        $this->app->bind(
            \App\Services\NotificationService::class,
            \App\Services\NotificationService::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
