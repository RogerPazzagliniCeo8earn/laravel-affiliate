<?php


namespace SoluzioneSoftware\LaravelAffiliate\Contracts;


use DateTime;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\NetworkProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\NetworkTransactionsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;

interface Network
{
    /**
     * @return NetworkProductsRequestBuilder
     */
    public function products();

    /**
     * @param string[]|null $programs
     * @param string|null $keyword
     * @param string[]|null $languages
     * @param string|null $trackingCode
     * @return Collection
     */
    public function executeProductsRequest(?array $programs = null,
                                           ?string $keyword = null,
                                           ?array $languages = null,
                                           ?string $trackingCode = null);

    /**
     * @param string $id
     * @param string|null $trackingCode
     * @return Product|null
     */
    public function getProduct(string $id, ?string $trackingCode = null);

    /**
     * @return NetworkTransactionsRequestBuilder
     */
    public function transactions();

    /**
     * @param array|null $programs
     * @param DateTime|null $fromDateTime
     * @param DateTime|null $toDateTime
     * @return Collection
     */
    public function executeTransactionsRequest(?array $programs = null, ?DateTime $fromDateTime = null, ?DateTime $toDateTime = null);
}
