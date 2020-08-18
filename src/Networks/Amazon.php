<?php

namespace SoluzioneSoftware\LaravelAffiliate\Networks;

use Amazon\ProductAdvertisingAPI\v1\ApiException;
use DateTime;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Revolution\Amazon\ProductAdvertising\AmazonClient;
use Revolution\Amazon\ProductAdvertising\Facades\AmazonProduct;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Objects\CommissionRate;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;

class Amazon extends AbstractNetwork implements Network
{
    /**
     * ASIN (Default), SKU, UPC, EAN, and ISBN
     * @var string $idType
     */
    private static $idType = 'ASIN';

    /**
     * @var AmazonClient $amazonClient
     */
    private $amazonClient;

    public function __construct()
    {
        parent::__construct();

        $this->amazonClient = AmazonProduct::setIdType(static::$idType);
    }

    /**
     * @inheritDoc
     */
    public static function getMaxPerPage(): ?int
    {
        return 10;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function executeProductsCountRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null
    ): int
    {
        // fixme: consider $languages

        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @throws ApiException
     */
    public function executeProductsRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null,
        ?string $trackingCode = null,
        int $page = 1,
        int $perPage = 10
    ): Collection
    {
        // fixme: consider $languages
        // fixme: consider $perPage
        // todo: cache results

        $this->trackingCode = $trackingCode;
        $response = $this->amazonClient->search('All', $keyword, $page);

        $products = array_map(function (array $product){
            return $this->productFromJson($product);
        }, Arr::get($response, 'SearchResult.Items', []));

        return new Collection($products);
    }

    /**
     * @inheritDoc
     */
    public function executeGetProduct(string $id, ?string $trackingCode = null): ?Product
    {
        $this->trackingCode = $trackingCode;

        try {
            $response = $this->amazonClient->item($id);
        }
        catch (ApiException $exception){
            Log::error('Amazon ApiException: ' . $exception->getMessage());
            return null;
        }

        $products = Arr::get($response, 'ItemsResult.Items', []);
        if (!count($products)){
            Log::info("Amazon product not found for id $id");
            return null;
        }

        return $this->productFromJson(Arr::first($products));
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function executeTransactionsCountRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null
    ): int
    {
        throw new Exception('Not implemented');
    }
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function executeTransactionsRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null,
        int $page = 1,
        int $perPage = 10
    ): Collection
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function executeCommissionRatesCountRequest(string $programId): int
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function executeCommissionRatesRequest(
        string $programId,
        int $page = 1,
        int $perPage = 100
    ): Collection
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function transactionFromJson(array $transaction)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @return null
     */
    public function programFromJson(array $program)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function productFromJson(array $product)
    {
        /** @var array|null $offer */
        $offer = Arr::first(Arr::get($product, 'Offers.Listings')); // fixme: what about other offers?

        return new Product(
            null,
            $product[static::$idType],
            $product['ItemInfo']['Title']['DisplayValue'],
            null, // fixme:
            $this->getProductImage($product),
            floatval(Arr::get($offer, 'Price.Amount')),
            Arr::get($offer, 'Price.Currency', 'EUR'), // fixme: parametrize
            $this->getDetailsLink($product),
            $this->getTrackingLink($product),
            $product
        );
    }

    /**
     * @param  string  $programId
     * @param  array  $commissionRate
     * @return CommissionRate
     * @throws Exception
     */
    public function commissionRateFromJson(string $programId, array $commissionRate): CommissionRate
    {
        throw new Exception('Not implemented');
    }

    protected function getDetailsLink(array $product)
    {
        return $product['DetailPageURL'];
    }

    protected function getTrackingLink(array $product)
    {
        return $this->getDetailsLink($product); // fixme: consider $trackingCode
    }

    /**
     * @see https://webservices.amazon.com/paapi5/documentation/images.html
     * @param array $product
     * @return string|null
     */
    private function getProductImage(array $product)
    {
        return Arr::get($product, 'Images.Primary.Medium.URL') ?? Arr::get($product, 'Images.Variants.0.Medium.URL');
    }
}
