<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use GuzzleHttp\ClientInterface;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Imports\FeedsImport;
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
        $listPath = $this->path("feeds.csv");
        $this->downloadFeeds($listPath);
        $this->importFeeds($listPath);
    }

    private function downloadFeeds(string $path)
    {
        $url = "https://productdata.awin.com/datafeed/list/apikey/{$this->apiKey()}";

        $this->client->get($url, ['sink' => $path]);
    }

    protected function importFeeds(string $path)
    {
//        fixme: delete old
        Excel::import(new FeedsImport(), $path);
    }

    protected function apiKey()
    {
        return Config::get('affiliate.credentials.awin.product_feed_api_key');
    }

    public static function path(string $path = '')
    {
        $basePath =
            Config::get('affiliate.product_feeds.directory_path')
            ?? App::storagePath() . DIRECTORY_SEPARATOR.'affiliate'.DIRECTORY_SEPARATOR.'product_feed';
        File::isDirectory($basePath) or File::makeDirectory($basePath, 0777, true, true);
        return $basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
