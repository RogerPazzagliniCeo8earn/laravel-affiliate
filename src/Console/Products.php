<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use Chumper\Zipper\Facades\Zipper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
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
        $feeds = $this->getFeeds();

        $this->info("Found {$feeds->count()} feeds to update.");

        if ($feeds->count() === 0){
            return;
        }

        $fileName = "products";
        $this->downloadProducts($feeds, $this->path("$fileName.zip"));
        $this->extract($this->path("$fileName.zip"), $this->path($fileName));
        $this->deleteFile($this->path("$fileName.zip"));

        foreach (glob("{$this->path($fileName)}/*.csv") as $file) {
            $this->importProducts($feeds, $file);

            $feeds->each(function (Feed $feed){
                $feed->update(['products_updated_at' => Date::now()]);
            });
        }
    }

    private function getFeeds()
    {
        $query = Feed::query();

        if (Config::get('affiliate.product_feeds.only_joined')){
            $query->where('joined', true);
        }

        if (Config::get('affiliate.product_feeds.only_joined')){
            $query->where('joined', true);
        }

        if (!is_null($regions = Config::get('affiliate.product_feeds.regions'))){
            $query->whereIn('region', $regions);
        }

        if (!is_null($languages = Config::get('affiliate.product_feeds.languages'))){
            $query->whereIn('language', $languages);
        }

        // consider updating only new feeds
        $query->where(function (Builder $query){
            $query
                ->whereNull('products_updated_at')
                ->orWhere(function (Builder $query){
                    $query
                        ->whereNotNull('imported_at')
                        ->whereRaw('imported_at >= products_updated_at');
                });
        });

        return $query->get();
    }

    private function downloadProducts(Collection $feeds, string $path)
    {
        $columns = [
            'product_name',
            'description',
            'aw_product_id',
            'merchant_image_url',
            'search_price',
            'currency',
            'data_feed_id',
            'last_updated',
        ];
        $url = "https://productdata.awin.com"
            . "/datafeed/download"
            . "/apikey/{$this->apiKey()}"
            . "/fid/" . implode(',', $feeds->pluck('feed_id')->toArray())
            . "/format/csv"
            . "/language/any"
            . "/delimiter/%2C" // comma
            . "/compression/zip"
            . "/columns/" . implode('%2C', $columns);

        $this->client->get($url, ['sink' => $path]);
    }

    private function importProducts(Collection $feeds, string $path)
    {
//        fixme: delete old
        $dateFilter = Date::now()->subHour(); // see ServiceProvider@console
        Excel::import(new ProductsImport($feeds, $dateFilter), $path);
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
