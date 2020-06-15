<?php


namespace SoluzioneSoftware\LaravelAffiliate;


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

    /**
     * @inheritDoc
     */
    protected function executeGetForNetwork(Network $network, int $page, int $perPage)
    {
        return $network->executeProductsRequest(
            $this->getPrograms($network), $this->keyword, $this->languages, $this->trackingCode, $page, $perPage
        );
    }

    protected function executeCountForNetwork(Network $network): int
    {
        return $network->executeProductsCountRequest($this->getPrograms($network), $this->keyword, $this->languages);
    }
}
