<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use SoluzioneSoftware\LaravelAffiliate\Contracts\NetworkWithProductFeeds;

abstract class AbstractImport
{
    /**
     * @var NetworkWithProductFeeds
     */
    protected $network;

    /**
     * @param  NetworkWithProductFeeds  $network
     */
    public function __construct(NetworkWithProductFeeds $network)
    {
        $this->network = $network;
    }
}
