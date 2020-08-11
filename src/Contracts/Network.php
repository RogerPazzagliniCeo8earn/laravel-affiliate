<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

use DateTime;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkTransactionsRequestBuilder;

interface Network
{
    /**
     * @return int|null
     */
    public static function getMaxPerPage(): ?int;

    /**
     * @return NetworkProductsRequestBuilder
     */
    public static function products(): NetworkProductsRequestBuilder;

    /**
     * @param  string  $id
     * @param  string|null  $trackingCode
     * @return Product|null
     */
    public static function getProduct(string $id, ?string $trackingCode = null): ?Product;

    /**
     * @param string[]|null $programs
     * @param string|null $keyword
     * @param string[]|null $languages
     * @return int
     */
    public function executeProductsCountRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null
    );

    /**
     * @param  string[]|null  $programs
     * @param  string|null  $keyword
     * @param  string[]|null  $languages
     * @param  string|null  $trackingCode
     * @param  int  $page
     * @param  int  $perPage
     * @return Collection
     */
    public function executeProductsRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null,
        ?string $trackingCode = null,
        int $page = 1,
        int $perPage = 10
    ): Collection;

    /**
     * @param string $id
     * @param string|null $trackingCode
     * @return Product|null
     */
    public function executeGetProduct(string $id, ?string $trackingCode = null): ?Product;

    /**
     * @return NetworkTransactionsRequestBuilder
     */
    public static function transactions();

    /**
     * @param  array|null  $programs
     * @param  DateTime|null  $fromDateTime
     * @param  DateTime|null  $toDateTime
     * @param  int  $page
     * @param  int  $perPage
     * @return Collection
     */
    public function executeTransactionsRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null,
        int $page = 1,
        int $perPage = 10
    ): Collection;
}
