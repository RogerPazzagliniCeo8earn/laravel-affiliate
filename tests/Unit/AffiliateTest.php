<?php

namespace Tests\Unit;

use Chumper\Zipper\ZipperServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\ExcelServiceProvider;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;
use Tests\TestCase;

class AffiliateTest extends TestCase
{
    use RefreshDatabase;
    use ResolvesBindings;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__.'/../../database/migrations');
        $this->withFactories(__DIR__.'/../../database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            ExcelServiceProvider::class,
            ZipperServiceProvider::class,
        ];
    }
}
