<?php

namespace SoluzioneSoftware\LaravelAffiliate;

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
        return new ProductsRequestBuilder;
    }
}
