<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class NetworkCommissionRatesRequestBuilder extends CommissionRatesRequestBuilder
{
    /**
     * @var Network
     */
    private $network;

    /**
     * @param Network $network
     */
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
        return $this->network->executeCommissionRatesRequest(
            Arr::first($this->getPrograms($this->network)), $page, $perPage
        );
    }

    /**
     * @param  Network  $network
     * @return array|null
     */
    protected function getPrograms(Network $network): ?array
    {
        return count($this->programs) ? $this->programs : null;
    }

    /**
     * @inheritDoc
     */
    protected function getNetworks()
    {
        return [$this->network];
    }
}
