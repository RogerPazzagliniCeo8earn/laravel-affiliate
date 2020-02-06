<?php
namespace SoluzioneSoftware\LaravelAffiliate;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/affiliate.php' => App::configPath('affiliate.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__ . '/../config/affiliate.php', 'affiliate'
        );
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->app->singleton(Affiliate::class, function () {
            return new Affiliate();
        });

        $this->app->alias(Affiliate::class, 'affiliate');
    }
}
