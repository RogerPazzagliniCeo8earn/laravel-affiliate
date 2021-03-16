<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use InvalidArgumentException;
use SoluzioneSoftware\LaravelAffiliate\Contracts\NetworkWithProductFeeds;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;

class Feeds extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:feeds {network}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Download and update feeds for the given network';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $networkKey = $this->argument('network');
        try {
            $network = Affiliate::resolveNetwork($networkKey);
        } catch (InvalidArgumentException $e) {
            $this->error($e->getMessage());
            return;
        }

        if (!($network instanceof NetworkWithProductFeeds)) {
            $this->error("'$networkKey' network must implement '".NetworkWithProductFeeds::class."' interface.");
            return;
        }

        Affiliate::updateFeeds(
            $network,
            $this->output
        );

        $this->info('Done.');
    }
}
