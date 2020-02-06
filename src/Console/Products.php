<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use Chumper\Zipper\Facades\Zipper;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use SoluzioneSoftware\LaravelAffiliate\Imports\ProductsImport;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class Products extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'affiliate:products';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and update products';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $feeds = Feed::query()
            ->where('joined', true) // fixme: put to config
            ->whereIn('region', ['it', 'en']) // fixme: put to config
            ->whereIn('language', ['it', 'en']) // fixme: put to config
            ->get();

        /** @var Feed $feed */
        foreach ($feeds as $feed) {
            $fileName = "feed_{$feed->id}";
            $this->downloadFeed($feed, $this->path("$fileName.zip"));
            $this->extract($this->path("$fileName.zip"), $this->path($fileName));
            $this->deleteFile($this->path("$fileName.zip"));

            foreach (glob("{$this->path($fileName)}/*.csv") as $file) {
                $this->importProducts($feed, $file);
            }
        }
    }

    private function downloadFeed(Feed $productFeed, string $path)
    {
        $columns = [
            'product_name',
            'description',
            'aw_product_id',
            'merchant_image_url',
            'search_price',
            'currency',
        ];
        $url = "https://productdata.awin.com"
            . "/datafeed/download"
            . "/apikey/{$this->apiKey()}"
            . "/fid/{$productFeed->feed_id}"
            . "/format/csv"
            . "/language/{$productFeed->language}"
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

}
