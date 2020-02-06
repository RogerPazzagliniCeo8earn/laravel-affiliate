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
     * @param string|null $query
     * @param array|string|null $languages
     * @param int|null $limit
     * @return Collection Collection of Product objects
     */
    public function searchProducts(?string $query = null, $languages = null, ?int $limit = null);
}
