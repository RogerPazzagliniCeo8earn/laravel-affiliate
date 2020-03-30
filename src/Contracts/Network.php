<?php


namespace SoluzioneSoftware\LaravelAffiliate\Contracts;


use DateTime;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Response;
use SoluzioneSoftware\LaravelAffiliate\TransactionsRequestBuilder;

interface Network
{
    /**
     * @deprecated Please use "transactions" method
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @return Response
     */
    public function getTransactions(?DateTime $startDate = null, ?DateTime $endDate = null);

    /**
     * @param string|null $query
     * @param array|null $advertisers
     * @param array|null $languages
     * @param int|null $limit
     * @param string|null $trackingCode
     * @return Response
     */
    public function searchProducts(?string $query = null, ?array $advertisers = null, ?array $languages = null, ?int $limit = null, ?string $trackingCode = null);

    /**
     * @param string $id
     * @param string|null $trackingCode
     * @return Product|null
     */
    public function getProduct(string $id, ?string $trackingCode = null);

    /**
     * @return TransactionsRequestBuilder
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
