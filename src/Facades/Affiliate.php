<?php


namespace SoluzioneSoftware\LaravelAffiliate\Facades;


use Illuminate\Support\Facades\Facade;
use SoluzioneSoftware\LaravelAffiliate\ProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\TransactionsRequestBuilder;

/**
 * @method static ProductsRequestBuilder products()
 * @method static TransactionsRequestBuilder transactions()
 *
 * @see \SoluzioneSoftware\LaravelAffiliate\Affiliate
 */
class Affiliate extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'affiliate';
    }
}
