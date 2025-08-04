<?php

namespace App\Providers;

use App\Contracts\NotificationServiceInterface;
use App\Contracts\OrderRepositoryInterface;
use App\Repositories\CouponRepository;
use App\Repositories\OrderRepository;
use App\Repositories\ServiceRepository;
use App\Services\CouponService;
use App\Services\NotificationService;
use App\Services\OrderService;
use App\Services\PriceCalculationService;
use App\Services\Validators\OrderValidator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            OrderRepositoryInterface::class,
            OrderRepository::class
        );

        $this->app->bind(
            CouponRepository::class,
            CouponRepository::class
        );

        $this->app->bind(
            ServiceRepository::class,
            ServiceRepository::class
        );

        $this->app->bind(
            OrderService::class,
            OrderService::class
        );

        $this->app->bind(
            CouponService::class,
            CouponService::class
        );

        $this->app->bind(
            PriceCalculationService::class,
            PriceCalculationService::class
        );

        $this->app->bind(
            NotificationServiceInterface::class,
            NotificationService::class
        );

        $this->app->bind(
            OrderValidator::class,
            OrderValidator::class
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
