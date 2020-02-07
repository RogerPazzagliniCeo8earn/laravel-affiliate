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
     */
    public function getTransactions(?DateTime $startDate = null, ?DateTime $endDate = null)
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
        //  $trackingCode
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
    protected function transactionFromJson(array $transaction)
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    protected function productFromJson(array $product)
    {
        $image = $product['Images']['Primary']['Medium'];
        $offer = $product['Offers']['Listings'][0];
        return new Product(
            $product['ASIN'],
            $product['ItemInfo']['Title']['DisplayValue'],
            null, // fixme:
            $image['URL'],
            floatval($offer['Price']['Amount']),
            $offer['Price']['Currency'],
            '', // fixme:
            $product
        );
    }
}
