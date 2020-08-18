<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Exception;
use Illuminate\Support\Arr;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class CommissionRatesRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @inheritDoc
     */
    protected function executeGetForNetwork(Network $network, int $page, int $perPage)
    {
//         fixme: what about other programs?
        return $network->executeCommissionRatesRequest(
            Arr::first($this->getPrograms($network)), $page, $perPage
        );
    }

    /**
     * @param  Network  $network
     * @return int
     * @throws Exception
     */
    protected function executeCountForNetwork(Network $network): int
    {
//         fixme: what about other programs?
        return $network->executeCommissionRatesCountRequest(Arr::first($this->getPrograms($network)));
    }
}
