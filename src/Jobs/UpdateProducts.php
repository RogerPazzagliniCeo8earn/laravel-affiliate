<?php

namespace SoluzioneSoftware\LaravelAffiliate\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Config;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Feed;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;

class UpdateProducts implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @var Feed
     */
    private $feed;

    /**
     * @param  Feed  $feed
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;

        $this
            ->onConnection(Config::get('affiliate.queue.connection'))
            ->onQueue(Config::get('affiliate.queue.name'));
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Affiliate::updateProducts($this->feed);
    }
}
