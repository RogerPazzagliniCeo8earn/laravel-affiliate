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

    public function paginate(int $page = 1, int $perPage = 10): Paginator
    {
        if ($this->catchErrors){
            $count = $this->attempt(function () {return $this->executeCount();}, 0);
            $items = $this->attempt(function () use ($page, $perPage) {return $this->executeGet($page, $perPage);}, collect());
        }
        else{
            $count = $this->executeCount();
            $items = $this->executeGet($page, $perPage);
        }
        return new Paginator($items, $count, $page, $perPage);
    }

    /**
     * @inheritDoc
     */
    protected function executeGet(int $page, int $perPage)
    {
        return $this->network->executeProductsRequest(
            $this->programs, $this->keyword, $this->languages, $this->trackingCode, $page, $perPage
        );
    }

    protected function executeCount(): int
    {
        return $this->network->executeProductsCountRequest($this->programs, $this->keyword, $this->languages);
    }
}
