<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class NetworkTransactionsRequestBuilder extends TransactionsRequestBuilder
{
    /**
     * @var string[]|null
     */
    protected $programs = null;

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
     * @param string[] $programs
     * @return $this
     */
    public function programs(array $programs)
    {
        // fixme: validate $programs param
        $this->programs = $programs;
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function get()
    {
        return $this->network->executeTransactionsRequest($this->programs, $this->fromDateTime, $this->toDateTime);
    }
}
