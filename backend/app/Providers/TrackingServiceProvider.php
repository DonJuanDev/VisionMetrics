<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TrackingService;
use App\Services\MetaConversionsService;
use App\Services\GoogleAdsService;

class TrackingServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(TrackingService::class, function ($app) {
            return new TrackingService();
        });

        $this->app->singleton(MetaConversionsService::class, function ($app) {
            return new MetaConversionsService(
                config('tracking.meta_conversions.pixel_id'),
                config('tracking.meta_conversions.access_token'),
                config('tracking.meta_conversions.api_version'),
                config('tracking.meta_conversions.base_url')
            );
        });

        $this->app->singleton(GoogleAdsService::class, function ($app) {
            return new GoogleAdsService(
                config('tracking.google_ads.customer_id'),
                config('tracking.google_ads.developer_token'),
                config('tracking.google_ads.client_id'),
                config('tracking.google_ads.client_secret'),
                config('tracking.google_ads.refresh_token')
            );
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
