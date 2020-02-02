<?php


namespace SoluzioneSoftware\LaravelAffiliate\Contracts;


use Illuminate\Support\Collection;

interface Network
{
    /**
     * @param array $params
     * @return Collection
     */
    public function getTransactions(array $params = []);

    /**
     * @param array $params
     * @return Collection
     */
    public function getProducts(array $params = []);
}
