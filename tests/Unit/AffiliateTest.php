<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Maatwebsite\Excel\ExcelServiceProvider;
use SoluzioneSoftware\LaravelAffiliate\Affiliate;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use Tests\TestCase;

class AffiliateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Config::set('affiliate.db.connection', 'testing');
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    protected function getPackageProviders($app)
    {
        return [
            ExcelServiceProvider::class,
        ];
    }

    /**
     * @test
     */
    public function new_records_are_added_when_updating_feeds()
    {
        $this->assertTrue(Feed::query()->doesntExist());

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/csv;charset=UTF-8'], file_get_contents(__DIR__ . '/../Fixtures/feeds.csv'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        (new Affiliate())->updateFeeds();

        $this->assertFileExists(Affiliate::path('feeds.csv'));

        $this->assertTrue(Feed::query()->exists());
    }
}
