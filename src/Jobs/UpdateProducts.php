<?php

namespace SoluzioneSoftware\LaravelAffiliate\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

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
     * @param  Feed  $commissionRate
     */
    public function __construct(Feed $commissionRate)
    {
        $this->feed = $commissionRate;
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
