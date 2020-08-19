<?php

namespace Tests\Unit;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\ExcelServiceProvider;
use PHPUnit\Framework\Constraint\FileExists;
use PHPUnit\Framework\Constraint\LogicalNot;
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
        $this->withFactories(__DIR__ . '/../../database/factories');
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
    public function downloads_file_when_updating_feeds()
    {
        $path = Affiliate::path('feeds.csv');

        File::delete($path);
        $this->assertThat($path, new LogicalNot(new FileExists));

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/csv;charset=UTF-8'], file_get_contents(__DIR__ . '/../Fixtures/feeds.csv'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        (new Affiliate())->updateFeeds();

        $this->assertFileExists(Affiliate::path('feeds.csv'));
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

        $this->assertTrue(Feed::query()->exists());
    }

    /**
     * @test
     */
    public function old_records_are_removed_when_updating_feeds()
    {
        factory(Feed::class, 10)->create();

        $this->assertEquals(10, Feed::query()->count());

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/csv;charset=UTF-8'], file_get_contents(__DIR__ . '/../Fixtures/1_feeds.csv'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        (new Affiliate())->updateFeeds();

        $this->assertEquals(1, Feed::query()->count());
    }
}
