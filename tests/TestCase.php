<?php

namespace Tests;

use SoluzioneSoftware\LaravelAffiliate\Contracts\Feed as FeedContract;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Product as ProductContract;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;

abstract class TestCase extends \Orchestra\Testbench\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(FeedContract::class, Feed::class);
        $this->app->bind(ProductContract::class, Product::class);
    }
}
