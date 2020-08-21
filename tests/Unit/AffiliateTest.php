<?php

namespace Tests\Unit;

use Chumper\Zipper\ZipperServiceProvider;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\ExcelServiceProvider;
use Maatwebsite\Excel\Facades\Excel;
use PHPUnit\Framework\Constraint\FileExists;
use PHPUnit\Framework\Constraint\LogicalNot;
use SoluzioneSoftware\LaravelAffiliate\Affiliate;
use SoluzioneSoftware\LaravelAffiliate\Imports\FeedsImport;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
use Tests\TestCase;

class AffiliateTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->withFactories(__DIR__ . '/../../database/factories');
    }

    protected function getPackageProviders($app)
    {
        return [
            ExcelServiceProvider::class,
            ZipperServiceProvider::class,
        ];
    }

    /**
     * @test
     */
    public function downloads_file_when_updating_feeds()
    {
        $path = Affiliate::path() . DIRECTORY_SEPARATOR . 'feeds.csv';

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
        $path = __DIR__ . '/../Fixtures/feeds.csv';

        $this->assertTrue(Feed::query()->doesntExist());

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/csv;charset=UTF-8'], file_get_contents($path))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        (new Affiliate())->updateFeeds();

        $feeds = Feed::all(FeedsImport::getAttributeNames());

        $this->assertEquals(3, $feeds->count());

        $feedsArray = array_map(function (array $row) {
            return FeedsImport::map($row);
        }, Excel::toArray(new FeedsImport(), $path)[0]);

        $diff = array_udiff($feedsArray, $feeds->toArray(), function (array $a, array $b) {
                return array_diff(Arr::except($a, 'original_data'), Arr::except($b, 'original_data'));
            });

        $this->assertCount(0, $diff);
    }

    /**
     * @test
     */
    public function old_records_are_removed_when_updating_feeds()
    {
        $path = __DIR__ . '/../Fixtures/1_feeds.csv';

        factory(Feed::class, 10)->create();

        $this->assertEquals(10, Feed::query()->count());

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'text/csv;charset=UTF-8'], file_get_contents($path))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        (new Affiliate())->updateFeeds();

        $feeds = Feed::all(FeedsImport::getAttributeNames());

        $this->assertEquals(1, Feed::query()->count());

        $feedsArray = array_map(function (array $row) {
            return FeedsImport::map($row);
        }, Excel::toArray(new FeedsImport(), $path)[0]);

        $diff = array_udiff($feedsArray, $feeds->toArray(), function (array $a, array $b) {
            return array_diff(Arr::except($a, 'original_data'), Arr::except($b, 'original_data'));
        });

        $this->assertCount(0, $diff);
    }

    /**
     * @test
     */
    public function new_records_are_added_when_updating_feed_products()
    {
        /** @var Feed $feed */
        $feed = factory(Feed::class)->create();

        $mock = new MockHandler([
            new Response(200, ['Content-Type' => 'application/zip'], file_get_contents(__DIR__ . '/../Fixtures/products.zip'))
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        $this->assertEquals(0, Product::query()->count());

        (new Affiliate())->updateProducts($feed);

        $this->assertEquals(5, Product::query()->count());
    }
}
