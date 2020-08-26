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
use Maatwebsite\Excel\Facades\Excel;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
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

    public function updateFeeds(?OutputStyle $output = null)
    {
        $this->output = $output;

        $listPath = $this->path() . DIRECTORY_SEPARATOR . 'feeds.csv';
        $this->downloadFeeds($listPath);
        $this->importFeeds($listPath);
    }

    public function updateProducts(Feed $feed, ?OutputStyle $output = null)
    {
        $this->output = $output;

        $path = $this->path('products');
        $feedPath = $this->path('products' . DIRECTORY_SEPARATOR . $feed->feed_id);
        $zipPath = $path . DIRECTORY_SEPARATOR . "{$feed->feed_id}.zip";

        if (!count(glob($feedPath . DIRECTORY_SEPARATOR . '*.csv')) || $feed->needsDownload()){
            $this->downloadProducts($feed, $zipPath);
            $this->extract($zipPath, $path . DIRECTORY_SEPARATOR . $feed->feed_id);
            $this->deleteFile($zipPath);
        }
        else{
            $this->writeLine('Using cached file...');
        }

        foreach (glob($feedPath . DIRECTORY_SEPARATOR . '*.csv') as $file) {
            $this->importProducts($feed, $file);
        }

        $feed->update(['products_updated_at' => Date::now()]);
    }

    private function downloadFeeds(string $path)
    {
        $url = "https://productdata.awin.com/datafeed/list/apikey/{$this->apiKey()}";

        $this->writeLine('Downloading...');

        $total = 0;
        $this->progressStart();

        $this->client->get(
            $url,
            [
                'sink' => $path,
                'progress' => $this->getDownloadProgressCallable($total),
            ]);

        $this->progressFinish();
    }

    protected function importFeeds(string $path)
    {
        $import = $this->output
            ? new FeedsImportWithProgress($this->output)
            : new FeedsImport();

        $this->writeLine('Importing...');
        Excel::import($import, $path);
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

        $this->writeLine('Downloading...');

        $total = 0;
        $this->progressStart();

        $this->client->get(
            $url,
            [
                'sink' => $path,
                'progress' => $this->getDownloadProgressCallable($total),
            ]
        );

        $this->progressFinish();

        $feed->update(['downloaded_at' => Date::now()]);
    }

    private function importProducts(Feed $feed, string $path)
    {
        $import = $this->output
            ? new ProductsImportWithProgress($feed, $this->output)
            : new ProductsImport($feed);

        $this->writeLine('Importing...');
        Excel::import($import, $path);
    }

    protected function extract(string $source, string $destination)
    {
        $this->writeLine('Extracting...');
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

    protected function getDownloadProgressCallable(int &$total): Callable
    {
        return function($downloadTotal, $downloadedBytes) use (&$total) {
            if ($downloadTotal === 0){
                return;
            }

            if ($downloadTotal !== $total) {
                $this->callMethod('setMaxSteps', $this->progressBar, $total = $downloadTotal);
            }
            $this->callMethod('setProgress', $this->progressBar, $downloadedBytes);
        };
    }
}
