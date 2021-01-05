<?php

namespace SoluzioneSoftware\LaravelAffiliate\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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
