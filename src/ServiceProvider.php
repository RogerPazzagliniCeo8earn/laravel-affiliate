<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SoluzioneSoftware\LaravelAffiliate\Console\Feeds;
use SoluzioneSoftware\LaravelAffiliate\Console\Products;

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

        $this->migrations();

        $this->console();
    }

    private function migrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/2020_01_01_000000_create_affiliate_feeds_table.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/2020_01_01_000000_create_affiliate_products_table.php');
    }

    private function console()
    {
        $this->commands([Feeds::class, Products::class]);

        /** @var Schedule $schedule */
        $schedule = $this->app->get(Schedule::class);
        $schedule
            ->command('affiliate:feeds')
            ->daily();
        $schedule
            ->command('affiliate:products')
            ->hourly();
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
