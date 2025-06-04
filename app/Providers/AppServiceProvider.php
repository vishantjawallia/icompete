<?php

namespace App\Providers;

use Carbon\CarbonImmutable;
use Date;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model::shouldBeStrict(!app()->isProduction());
        Date::use(CarbonImmutable::class);
        Paginator::useBootstrap();
    }
}
