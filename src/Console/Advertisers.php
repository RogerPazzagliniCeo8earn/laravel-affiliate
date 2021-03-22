<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use InvalidArgumentException;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;

class Advertisers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'affiliate:advertisers {network}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update advertisers for the given network';

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

        Affiliate::updateAdvertisers(
            $network,
            $this->output
        );

        $this->info('Done.');
    }
}
