<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Automatically eager load relationships (Laravel 12.0.8+)
        Model::automaticallyEagerLoadRelationships();

        // Prevent lazy loading in non-production (catch N+1 issues early)
        // Model::preventLazyLoading(! app()->isProduction());

        // Enable HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Optimize queries
        DB::enableQueryLog();

        // Add security headers
        if ($this->app->environment('production')) {
            $this->app['request']->server->set('HTTPS', 'on');
        }
    }
}
