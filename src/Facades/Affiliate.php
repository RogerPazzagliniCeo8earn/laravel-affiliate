<?php

namespace SoluzioneSoftware\LaravelAffiliate\Facades;

use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Facade;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Contracts\NetworkWithProductFeeds;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Requests\AdvertisersRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\CommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkAdvertisersRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkCommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkTransactionsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\ProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\TransactionsRequestBuilder;

/**
 * @method static void registerNetwork(string $class)
 * @method static array getNetworks()
 * @method static Network resolveNetwork(string $key)
 * @method static CommissionRatesRequestBuilder commissionRates()
 * @method static NetworkCommissionRatesRequestBuilder networkCommissionRates(Network $network)
 * @method static AdvertisersRequestBuilder advertisers()
 * @method static NetworkAdvertisersRequestBuilder networkAdvertisers(Network $network)
 * @method static ProductsRequestBuilder products()
 * @method static TransactionsRequestBuilder transactions()
 * @method static NetworkTransactionsRequestBuilder networkTransactions(Network $network)
 * @method static void updateAdvertisers(Network $network, ?OutputStyle $output = null)
 * @method static void updateFeeds(NetworkWithProductFeeds $network, ?OutputStyle $output = null)
 * @method static void updateProducts(Feed $feed, ?OutputStyle $output = null, bool $forceDownload = false)
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
