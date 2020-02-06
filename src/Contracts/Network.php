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
     * @param array|null $advertisers
     * @param array|null $languages
     * @param int|null $limit
     * @param string|null $trackingCode
     * @return Collection Collection of Product objects
     */
    public function searchProducts(?string $query = null, ?array $advertisers = null, ?array $languages = null, ?int $limit = null, ?string $trackingCode = null);
}
