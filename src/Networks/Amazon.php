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
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Response;

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
     * @throws Exception
     */
    public function getTransactions(?DateTime $startDate = null, ?DateTime $endDate = null)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function executeTransactionsRequest(?array $programs = null, ?DateTime $fromDateTime = null, ?DateTime $toDateTime = null)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    public function searchProducts(?string $query = null, ?array $advertisers = null, ?array $languages = null, ?int $limit = null, ?string $trackingCode = null)
    {
        // fixme: consider
        //  $languages
        //  $limit
        $this->trackingCode = $trackingCode;

        try {
            $response = $this->amazonClient->search('All', $query , 1);
        }
        catch (ApiException $exception){
            Log::error('Amazon ApiException: ' . $exception->getMessage());
            return new Response(false, $exception->getMessage());
        }

        $products = array_map(function (array $product){
            return $this->productFromJson($product);
        }, Arr::get($response, 'SearchResult.Items', []));

        $collection = new Collection($products);

        return new Response(true, null, $collection);
    }

    /**
     * @inheritDoc
     */
    public function getProduct(string $id, ?string $trackingCode = null)
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
     */
    protected function transactionFromJson(array $transaction)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @return null
     */
    protected function programFromJson(array $program)
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function productFromJson(array $product)
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
