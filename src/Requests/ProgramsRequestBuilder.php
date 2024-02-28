<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class ProgramsRequestBuilder extends AbstractRequestBuilder
{
    protected function executeGetForNetwork(Network $network, int $page, int $perPage)
    {
        return $network->executeProgramsRequest();
    }

    protected function executeCountForNetwork(Network $network): int
    {
        throw new \RuntimeException('Method not implemented');
    }
}
