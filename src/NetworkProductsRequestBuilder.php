<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Illuminate\Support\Collection;
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
    protected function executeGet(int $page, int $perPage): Collection
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
