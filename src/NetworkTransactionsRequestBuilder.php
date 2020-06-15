<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Traits\HasPrograms;

class NetworkTransactionsRequestBuilder extends TransactionsRequestBuilder
{
    use HasPrograms;

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
            $this->programs, $this->fromDateTime, $this->toDateTime, $page, $perPage
        );
    }
}
