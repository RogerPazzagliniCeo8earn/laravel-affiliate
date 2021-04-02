<?php

namespace Tests;

use Chumper\Zipper\ZipperServiceProvider;
use Maatwebsite\Excel\ExcelServiceProvider;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
use SoluzioneSoftware\LaravelAffiliate\ServiceProvider;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(Feed::class);
        $this->app->bind(Product::class);

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->withFactories(__DIR__.'/../../database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            ExcelServiceProvider::class,
            ServiceProvider::class,
            ZipperServiceProvider::class,
        ];
    }
}
