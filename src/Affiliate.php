<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use Chumper\Zipper\Facades\Zipper;
use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Imports\FeedsImport;
use SoluzioneSoftware\LaravelAffiliate\Imports\ProductsImport;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Requests\CommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkCommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkTransactionsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\ProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\TransactionsRequestBuilder;

class Affiliate
{
    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->client = Container::getInstance()->make('affiliate.client');
    }

    /**
     * @return TransactionsRequestBuilder
     */
    public function transactions()
    {
        return new TransactionsRequestBuilder;
    }

    /**
     * @return ProductsRequestBuilder
     */
    public function products()
    {
        return new ProductsRequestBuilder();
    }

    /**
     * @return CommissionRatesRequestBuilder
     */
    public function commissionRates()
    {
        return new CommissionRatesRequestBuilder();
    }

    /**
     * @param  Network  $network
     * @return CommissionRatesRequestBuilder
     */
    public function networkCommissionRates(Network $network)
    {
        return new NetworkCommissionRatesRequestBuilder($network);
    }

    /**
     * @param  Network  $network
     * @return NetworkTransactionsRequestBuilder
     */
    public function networkTransactions(Network $network)
    {
        return new NetworkTransactionsRequestBuilder($network);
    }

    public function updateFeeds()
    {
        $listPath = $this->path() . DIRECTORY_SEPARATOR . 'feeds.csv';
        $this->downloadFeeds($listPath);
        $this->importFeeds($listPath);
    }

    public function updateProducts(Feed $feed)
    {
        $path = $this->path("products");
        $zipPath = $path . DIRECTORY_SEPARATOR . "{$feed->feed_id}.zip";
        $this->downloadProducts($feed, $zipPath);
        $this->extract($zipPath, $path . DIRECTORY_SEPARATOR . $feed->feed_id);
        $this->deleteFile($zipPath);

        foreach (glob($path . DIRECTORY_SEPARATOR . $feed->feed_id . DIRECTORY_SEPARATOR . '*.csv') as $file) {
            $this->importProducts($feed, $file);
        }

        $feed->update(['products_updated_at' => Date::now()]);
    }

    private function downloadFeeds(string $path)
    {
        $url = "https://productdata.awin.com/datafeed/list/apikey/{$this->apiKey()}";

        $this->client->get($url, ['sink' => $path]);
    }

    protected function importFeeds(string $path)
    {
        Excel::import(new FeedsImport(), $path);
    }

    private function downloadProducts(Feed $feed, string $path)
    {
        $columns = [
            'product_name',
            'description',
            'aw_product_id',
            'merchant_image_url',
            'search_price',
            'currency',
            'merchant_deep_link',
            'data_feed_id',
            'last_updated',
        ];
        $url = "https://productdata.awin.com"
            . "/datafeed/download"
            . "/apikey/{$this->apiKey()}"
            . "/fid/{$feed->feed_id}"
            . "/format/csv"
            . "/language/any"
            . "/delimiter/%2C" // comma
            . "/compression/zip"
            . "/columns/" . implode('%2C', $columns);

        $this->client->get($url, ['sink' => $path]);
    }

    private function importProducts(Feed $feed, string $path)
    {
        Excel::import(new ProductsImport($feed), $path);
    }

    protected function extract(string $source, string $destination)
    {
        Zipper::make($source)->extractTo($destination);
    }

    protected function deleteFile(string $file)
    {
        File::delete($file);
    }

    protected function apiKey()
    {
        return Config::get('affiliate.credentials.awin.product_feed_api_key');
    }

    public static function path(string $path = '')
    {
        $basePath =
            Config::get('affiliate.product_feeds.directory_path')
            ?? App::storagePath() . DIRECTORY_SEPARATOR . 'affiliate';
        $fullPath = $basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
        static::ensureDirectoryExists($fullPath);
        return $fullPath;
    }

    protected static function ensureDirectoryExists(string $path)
    {
        return File::isDirectory($path) or File::makeDirectory($path, 0777, true, true);
    }
}
