<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Exception;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class CommissionRatesRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function executeGetForNetwork(Network $network, int $page, int $perPage)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @param  Network  $network
     * @return int
     * @throws Exception
     */
    protected function executeCountForNetwork(Network $network): int
    {
        throw new Exception('Not implemented');
    }
}
