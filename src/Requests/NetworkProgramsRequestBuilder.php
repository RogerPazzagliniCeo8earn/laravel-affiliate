<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class NetworkProgramsRequestBuilder extends ProgramsRequestBuilder
{
    /**
     * @var Network
     */
    private $network;

    public function __construct(Network $network)
    {
        parent::__construct();

        $this->network = $network;
    }

    /**
     * @inheritDoc
     */
    protected function executeGet(int $page, int $perPage): Collection
    {
        return $this->network->executeProgramsRequest();
    }

    /**
     * @inheritDoc
     */
    protected function getNetworks()
    {
        return [$this->network];
    }
}
