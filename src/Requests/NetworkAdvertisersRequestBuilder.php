<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class NetworkAdvertisersRequestBuilder extends AdvertisersRequestBuilder
{
    /**
     * @var Network
     */
    private $network;

    /**
     * @param  Network  $network
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
        return $this->network->executeAdvertisersRequest($page, $perPage);
    }

    /**
     * @inheritDoc
     */
    protected function getNetworks()
    {
        return [$this->network];
    }
}
