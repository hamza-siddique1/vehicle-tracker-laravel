<?php

namespace App\Providers;

use App\Services\Ndtc\NdtcApiService;
use App\Services\Ndtc\NdtcAuthService;
use App\Services\Ndtc\NdtcDescriptionParser;
use App\Services\Ndtc\NdtcPayloadBuilder;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(NdtcAuthService::class);
        $this->app->singleton(NdtcApiService::class);
        $this->app->singleton(NdtcPayloadBuilder::class);
        $this->app->singleton(NdtcDescriptionParser::class);

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }
}
