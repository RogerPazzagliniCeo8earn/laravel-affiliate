<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use Illuminate\Database\Eloquent\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Feed;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;

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
        return Feed::whereNeedsUpdate()->get();
    }

}
