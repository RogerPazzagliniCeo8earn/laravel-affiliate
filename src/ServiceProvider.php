<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use GuzzleHttp\Client;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use SoluzioneSoftware\LaravelAffiliate\Console\Feeds;
use SoluzioneSoftware\LaravelAffiliate\Console\Products;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
use SoluzioneSoftware\LaravelAffiliate\Observers\FeedObserver;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/affiliate.php' => App::configPath('affiliate.php'),
        ]);

        $this->mergeConfigFrom(
            __DIR__.'/../config/affiliate.php', 'affiliate'
        );

        $this->migrations();

        $this->console();

        $this->observers();
    }

    private function migrations()
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2020_01_01_000000_create_affiliate_feeds_table.php');
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations/2020_01_01_000000_create_affiliate_products_table.php');
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

    private function observers()
    {
        Feed::observe(FeedObserver::class);
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerAffiliate();
        $this->registerClient();
        $this->registerEloquentFactories();
        $this->registerBindings();
    }

    private function registerAffiliate()
    {
        $this->app->singleton(Affiliate::class, function () {
            return new Affiliate();
        });

        $this->app->alias(Affiliate::class, 'affiliate');
    }

    private function registerClient()
    {
        $this->app->singleton('affiliate.client', function () {
            return new Client();
        });
    }

    private function registerEloquentFactories()
    {
        $this->app->extend(Factory::class, function (Factory $factory) {
            return $factory->load(__DIR__.'/../database/factories');
        });
    }

    private function registerBindings()
    {
        $this->app->bind(Feed::class, Feed::class);
        $this->app->bind(Product::class, Product::class);
    }
}
