<?php

namespace SoluzioneSoftware\LaravelAffiliate\Facades;

use Illuminate\Support\Facades\Facade;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Requests\CommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkCommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkTransactionsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\ProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\TransactionsRequestBuilder;

/**
 * @method static CommissionRatesRequestBuilder commissionRates()
 * @method static NetworkCommissionRatesRequestBuilder networkCommissionRates(Network $network)
 * @method static ProductsRequestBuilder products()
 * @method static TransactionsRequestBuilder transactions()
 * @method static NetworkTransactionsRequestBuilder networkTransactions(Network $network)
 * @method static void updateFeeds()
 * @method static void updateProducts(Feed $feed)
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
