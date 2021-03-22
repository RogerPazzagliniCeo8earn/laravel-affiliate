<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class AdvertisersRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @inheritDoc
     */
    protected function executeGetForNetwork(Network $network, int $page, int $perPage)
    {
        return $network->executeAdvertisersRequest($page, $perPage);
    }

    /**
     * @inheritDoc
     */
    protected function executeCountForNetwork(Network $network): int
    {
        return $network->executeAdvertisersCountRequest();
    }
}
