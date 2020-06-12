<?php


namespace SoluzioneSoftware\LaravelAffiliate;


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
