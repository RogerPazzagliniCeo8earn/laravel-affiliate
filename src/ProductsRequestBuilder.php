<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Illuminate\Support\Collection;

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

    /**
     * @inheritDoc
     */
    public function get()
    {
        $products = new Collection;
        foreach ($this->getNetworks() as $network) {
            $networkProducts = $network->executeProductsRequest(null, $this->keyword, $this->languages, $this->trackingCode);
            $products = $products->merge($networkProducts);
        }
        return $products;
    }
}
