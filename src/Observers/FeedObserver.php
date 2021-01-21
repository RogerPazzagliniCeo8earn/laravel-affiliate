<?php

namespace SoluzioneSoftware\LaravelAffiliate\Observers;

use SoluzioneSoftware\LaravelAffiliate\Jobs\UpdateProducts;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class FeedObserver
{
    /**
     * Handle the feed "created" event.
     *
     * @param  Feed  $feed
     * @return void
     */
    public function created(Feed $feed)
    {
        if ($feed->needsUpdate()) {
            UpdateProducts::dispatch($feed);
        }
    }

    /**
     * Handle the feed "updated" event.
     *
     * @param  Feed  $feed
     * @return void
     */
    public function updated(Feed $feed)
    {
        if (
            $feed->wasChanged(['enabled', 'region', 'language', 'imported_at', 'products_updated_at'])
            && $feed->needsUpdate()
        ) {
            UpdateProducts::dispatch($feed);
        }
    }

    /**
     * Handle the feed "deleted" event.
     *
     * @param  Feed  $feed
     * @return void
     */
    public function deleted(Feed $feed)
    {
        //
    }

    /**
     * Handle the feed "restored" event.
     *
     * @param  Feed  $feed
     * @return void
     */
    public function restored(Feed $feed)
    {
        //
    }

    /**
     * Handle the feed "force deleted" event.
     *
     * @param  Feed  $feed
     * @return void
     */
    public function forceDeleted(Feed $feed)
    {
        //
    }
}
