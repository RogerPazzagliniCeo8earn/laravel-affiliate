<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;

class Feeds extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'affiliate:feeds';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and update feeds';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        Affiliate::updateFeeds();
    }
}
