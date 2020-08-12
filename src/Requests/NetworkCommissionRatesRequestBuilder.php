<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Exception;
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
     * @throws Exception
     */
    protected function executeGet(int $page, int $perPage): Collection
    {
        throw new Exception('Not implemented');
    }

    /**
     * @return int
     * @throws Exception
     */
    protected function executeCount(): int
    {
        throw new Exception('Not implemented');
    }

    /**
     * @param  Network  $network
     * @return array|null
     */
    protected function getPrograms(Network $network): ?array
    {
        return count($this->programs) ? $this->programs : null;
    }
}
