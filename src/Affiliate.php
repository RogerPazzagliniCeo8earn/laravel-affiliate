<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use Chumper\Zipper\Facades\Zipper;
use GuzzleHttp\ClientInterface;
use Illuminate\Console\OutputStyle;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\File;
use InvalidArgumentException;
use Maatwebsite\Excel\Facades\Excel;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Contracts\NetworkWithProductFeeds;
use SoluzioneSoftware\LaravelAffiliate\Imports\FeedsImport;
use SoluzioneSoftware\LaravelAffiliate\Imports\FeedsImportWithProgress;
use SoluzioneSoftware\LaravelAffiliate\Imports\ProductsImport;
use SoluzioneSoftware\LaravelAffiliate\Imports\ProductsImportWithProgress;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Requests\CommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkCommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkTransactionsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\ProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\TransactionsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Traits\InteractsWithConsoleOutput;

class Affiliate
{
    use InteractsWithConsoleOutput;

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * Registered networks.
     *
     * @var string[]
     */
    protected $networks = [];

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
     * @param  string  $class
     * @return void
     */
    public function registerNetwork(string $class)
    {
        $interface = Network::class;

        if (!isset(class_implements($class)[$interface])) {
            throw new InvalidArgumentException("Class '$class' must implement '$interface' interface.");
        }

        /** @var Network $class */
        $this->networks[$class::getKey()] = $class;
    }

    /**
     * @return string[]
     */
    public function getNetworks()
    {
        return $this->networks;
    }

    /**
     * @param  string  $key
     * @return Network
     * @throws InvalidArgumentException
     */
    public function resolveNetwork(string $key): Network
    {
        if (!array_key_exists($key, $this->networks)) {
            throw new InvalidArgumentException("'$key' is not a valid network key.");
        }

        return new $this->networks[$key]();
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

    public function updateFeeds(NetworkWithProductFeeds $network, ?OutputStyle $output = null)
    {
        $this->output = $output;

        $path = $this->path($network->getKey()).DIRECTORY_SEPARATOR.'feeds.csv';
        $this->downloadFeeds($network, $path);
        $this->importFeeds($network, $path);
    }

    public static function path(string $path = '')
    {
        $basePath = Config::get('affiliate.product_feeds.directory_path');
        if (empty($basePath)) {
            $basePath = App::storagePath().DIRECTORY_SEPARATOR.'affiliate';
        }
        $fullPath = $basePath.(!empty($path) ? DIRECTORY_SEPARATOR.$path : $path);
        static::ensureDirectoryExists($fullPath);
        return $fullPath;
    }

    protected static function ensureDirectoryExists(string $path)
    {
        return File::isDirectory($path) || File::makeDirectory($path, 0777, true, true);
    }

    private function downloadFeeds(NetworkWithProductFeeds $network, string $path)
    {
        $total = 0;

        $this->writeLine('Downloading...');

        $this->progressStart();

        $network->downloadFeeds($path, $this->getDownloadProgressCallable($total));

        $this->progressFinish();
    }

    protected function getDownloadProgressCallable(int &$total): callable
    {
        return function ($downloadTotal, $downloadedBytes) use (&$total) {
            if ($downloadTotal === 0) {
                return;
            }

            if ($downloadTotal !== $total) {
                $this->callMethod('setMaxSteps', $this->progressBar, $total = $downloadTotal);
            }
            $this->callMethod('setProgress', $this->progressBar, $downloadedBytes);
        };
    }

    protected function importFeeds(NetworkWithProductFeeds $network, string $path)
    {
        $import = $this->getFeedsImport($network);

        $this->writeLine('Importing...');
        Excel::import($import, $path);
    }

    /**
     * @param  NetworkWithProductFeeds  $network
     * @return FeedsImport
     */
    protected function getFeedsImport(NetworkWithProductFeeds $network): FeedsImport
    {
        return $this->output
            ? new FeedsImportWithProgress($network, $this->output)
            : new FeedsImport($network);
    }

    /**
     * @param  Feed  $feed
     * @param  OutputStyle|null  $output
     * @param  bool  $forceDownload
     * @throws BindingResolutionException
     */
    public function updateProducts(
        Feed $feed,
        ?OutputStyle $output = null,
        bool $forceDownload = false
    ) {
        $this->output = $output;
        $feedKey = $feed->getKey();
        $path = $this->path('products');
        $feedPath = $this->path('products'.DIRECTORY_SEPARATOR.$feedKey);
        $zipPath = $path.DIRECTORY_SEPARATOR."{$feedKey}.zip";

        $this->writeLine("Processing feed ID:{$feedKey}...");

        if (!count(glob($feedPath.DIRECTORY_SEPARATOR.'*.csv')) || $forceDownload || $feed->needsDownload()) {
            $this->downloadProducts($feed, $zipPath);
            $this->extract($zipPath, $path.DIRECTORY_SEPARATOR.$feedKey);
            $this->deleteFile($zipPath);
        } else {
            $this->writeLine('Using cached file...');
        }

        foreach (glob($feedPath.DIRECTORY_SEPARATOR.'*.csv') as $file) {
            $this->importProducts($feed, $file);
        }

        $feed->update(['products_updated_at' => Date::now()]);
    }

    private function downloadProducts(Feed $feed, string $path)
    {
        $this->writeLine('Downloading...');

        $total = 0;
        $this->progressStart();

        $feed->getNetwork()->downloadFeedProducts($feed, $path, $this->getDownloadProgressCallable($total));

        $this->progressFinish();

        $feed->update(['downloaded_at' => Date::now()]);
    }

    protected function extract(string $source, string $destination)
    {
        $this->writeLine('Extracting...');
        /** @noinspection PhpUndefinedMethodInspection */
        Zipper::make($source)->extractTo($destination);
    }

    protected function deleteFile(string $file)
    {
        File::delete($file);
    }

    /**
     * @param  Feed  $feed
     * @param  string  $path
     * @throws BindingResolutionException
     */
    private function importProducts(Feed $feed, string $path)
    {
        $import = $this->getProductsImport($feed);

        $this->writeLine('Importing...');
        $import->import($path);
    }

    /**
     * @param  Feed  $feed
     * @return ProductsImport
     * @throws BindingResolutionException
     */
    protected function getProductsImport(Feed $feed): ProductsImport
    {
        return $this->output
            ? new ProductsImportWithProgress($feed, $this->output)
            : new ProductsImport($feed);
    }
}
