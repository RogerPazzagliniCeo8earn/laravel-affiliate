<?php


namespace SoluzioneSoftware\LaravelAffiliate\Contracts;


use DateTime;
use SoluzioneSoftware\LaravelAffiliate\Objects\Response;

interface Network
{
    /**
     * @param DateTime|null $startDate
     * @param DateTime|null $endDate
     * @return Response
     */
    public function getTransactions(?DateTime $startDate = null, ?DateTime $endDate = null);

    /**
     * @param string|null $query
     * @param array|null $advertisers
     * @param array|null $languages
     * @param int|null $limit
     * @param string|null $trackingCode
     * @return Response
     */
    public function searchProducts(?string $query = null, ?array $advertisers = null, ?array $languages = null, ?int $limit = null, ?string $trackingCode = null);
}
