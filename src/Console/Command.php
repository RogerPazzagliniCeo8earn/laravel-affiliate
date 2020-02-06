<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;

abstract class Command extends \Illuminate\Console\Command
{
    protected $client;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->client = new Client();
    }

    protected function apiKey()
    {
        return Config::get('affiliate.credentials.awin.product_feed_api_key');
    }

    protected function path(string $path = '')
    {
        $basePath =
            Config::get('affiliate.product_feeds.directory_path')
            ?? App::storagePath() . DIRECTORY_SEPARATOR.'affiliate'.DIRECTORY_SEPARATOR.'product_feed';
        File::isDirectory($basePath) or File::makeDirectory($basePath, 0777, true, true);
        return $basePath . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}
