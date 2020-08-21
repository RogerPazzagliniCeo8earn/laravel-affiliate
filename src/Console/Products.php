<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Config;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;
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

        $feeds
            ->each(function (Feed $feed) {
                Affiliate::updateProducts($feed, $this->output);
            });

        $this->info('Done.');
    }

    private function getFeeds(): Collection
    {
        $query = Feed::enabled();

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

}
