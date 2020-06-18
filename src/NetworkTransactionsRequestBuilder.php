<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class NetworkTransactionsRequestBuilder extends TransactionsRequestBuilder
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
        return $this->network->executeTransactionsRequest(
            $this->getPrograms($this->network), $this->fromDateTime, $this->toDateTime, $page, $perPage
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
}
