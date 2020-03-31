<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Traits\HasPrograms;

class NetworkProductsRequestBuilder extends ProductsRequestBuilder
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
    public function get()
    {
        return $this->network->executeProductsRequest($this->programs, $this->keyword, $this->languages, $this->trackingCode);
    }
}
