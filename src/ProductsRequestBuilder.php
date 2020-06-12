<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class ProductsRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @var string|null
     */
    protected $keyword = null;

    /**
     * @var string[]|null
     */
    protected $languages = null;

    /**
     * @var string|null
     */
    protected $trackingCode = null;

    /**
     * @param string $keyword
     * @return $this
     */
    public function keyword(string $keyword)
    {
        $this->keyword = $keyword;
        return $this;
    }

    /**
     * @param string[] $languages
     * @return $this
     */
    public function languages(array $languages)
    {
        $this->languages = $languages;
        return $this;
    }

    /**
     * @param string $trackingCode
     * @return $this
     */
    public function withTrackingCode(string $trackingCode)
    {
        $this->trackingCode = $trackingCode;
        return $this;
    }

    protected function executeGet(int $page, int $perPage)
    {
//        fixme: consider $page & $perPage parameters

        $products = new Collection;
        foreach ($this->getNetworks() as $network) {
            $networkProducts = $network->executeProductsRequest(
                null, $this->keyword, $this->languages, $this->trackingCode
            );
            $products = $products->merge($networkProducts);
        }
        return $products;
    }

    protected function executeCount(): int
    {
        $count = 0;
        foreach ($this->getNetworks() as $network) {
            $count += $network->executeProductsCountRequest(null, $this->keyword, $this->languages);
        }
        return $count;
    }

    protected function executeCountForNetwork(Network $network): int
    {
        return $network->executeProductsCountRequest(null, $this->keyword, $this->languages);
    }
}
