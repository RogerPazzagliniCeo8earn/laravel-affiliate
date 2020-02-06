<?php


namespace SoluzioneSoftware\LaravelAffiliate\Networks;


use Amazon\ProductAdvertisingAPI\v1\ApiException;
use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Revolution\Amazon\ProductAdvertising\Facades\AmazonProduct;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;

class Amazon extends AbstractNetwork implements Network
{

    /**
     * @inheritDoc
     */
    public function getTransactions(array $params = [])
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
        try {
            $response = AmazonProduct::search('All', $query , 1);
        }
        catch (ApiException $exception){
            Log::error('Amazon ApiException: ' . $exception->getMessage());
            return new Collection;
        }

        $products = array_map(function (array $product){
            return $this->productFromJson($product);
        }, Arr::get($response, 'SearchResult.Items', []));

        return new Collection($products);
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
            null,
            $image['URL'],
            floatval($offer['Price']['Amount']),
            $offer['Price']['Currency'],
            $product
        );
    }
}
