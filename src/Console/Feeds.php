<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use Maatwebsite\Excel\Facades\Excel;
use SoluzioneSoftware\LaravelAffiliate\Imports\FeedsImport;

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
        $listPath = $this->path("feeds.csv");
        $this->downloadFeeds($listPath);
        $this->importFeeds($listPath);
    }

    private function downloadFeeds(string $path)
    {
        $url = "https://productdata.awin.com"
            . "/datafeed/list"
            . "/apikey/{$this->apiKey()}";

        $this->client->get($url, ['sink' => $path]);
    }

    private function importFeeds(string $path)
    {
//        fixme: delete old
        Excel::import(new FeedsImport(), $path);
    }
}
