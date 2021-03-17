<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Collection;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;

class Products extends Command
{
    use ResolvesBindings;

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
     * @throws BindingResolutionException
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

    /**
     * @return Collection
     * @throws BindingResolutionException
     */
    private function getFeeds(): Collection
    {
        return static::resolveFeedModelBinding()::whereNeedsUpdate()->get();
    }

}
