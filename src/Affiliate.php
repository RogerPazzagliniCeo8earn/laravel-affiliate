<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Requests\CommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkCommissionRatesRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\ProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\TransactionsRequestBuilder;

class Affiliate
{
    /**
     * @return TransactionsRequestBuilder
     */
    public function transactions()
    {
        return new TransactionsRequestBuilder;
    }

    /**
     * @return ProductsRequestBuilder
     */
    public function products()
    {
        return new ProductsRequestBuilder();
    }

    /**
     * @return CommissionRatesRequestBuilder
     */
    public function commissionRates()
    {
        return new CommissionRatesRequestBuilder();
    }

    /**
     * @param  Network  $network
     * @return CommissionRatesRequestBuilder
     */
    public function networkCommissionRates(Network $network)
    {
        return new NetworkCommissionRatesRequestBuilder($network);
    }
}
