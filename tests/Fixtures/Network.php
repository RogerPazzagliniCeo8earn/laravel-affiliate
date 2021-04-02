<?php

namespace Tests\Fixtures;

use DateTime;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network as NetworkContract;
use SoluzioneSoftware\LaravelAffiliate\Enums\TransactionStatus;
use SoluzioneSoftware\LaravelAffiliate\Enums\ValueType;
use SoluzioneSoftware\LaravelAffiliate\Objects\CommissionRate;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product as ProductObject;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;

class Network extends AbstractNetwork implements NetworkContract
{
    public static function getMaxPerPage(): ?int
    {
        return null;
    }

    public static function getTrackingUrl(string $trackingCode, array $params = []): string
    {
        return '';
    }

    public static function getKey(): string
    {
        return 'network';
    }

    public function executeGetProduct(string $id, ?string $trackingCode = null): ?Product
    {
        return null;
    }

    public function executeProductsRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null,
        ?string $trackingCode = null,
        int $page = 1,
        int $perPage = 10
    ): Collection {
        return Collection::make();
    }

    public function executeTransactionsRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null,
        int $page = 1,
        ?int $perPage = null
    ): Collection {
        return Collection::make();
    }

    public function executeTransactionsCountRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null
    ): int {
        return 0;
    }

    public function executeCommissionRatesCountRequest(string $programId): int
    {
        return 0;
    }

    public function executeCommissionRatesRequest(string $programId, int $page = 1, int $perPage = 100): Collection
    {
        return Collection::make();
    }

    public function transactionFromJson(array $transaction)
    {
        return new Transaction(
            null,
            '1',
            new TransactionStatus(),
            null,
            .0,
            'EUR',
            now(),
            null,
            $transaction
        );
    }

    public function productFromJson(array $product)
    {
        return new ProductObject(
            $this->programFromJson([]),
            '1',
            'foo product',
            null,
            null,
            .0,
            'EUR',
            null,
            null,
            $product
        );
    }

    public function programFromJson(array $program)
    {
        return new Program($this, '1', 'foo program');
    }

    public function commissionRateFromJson(string $programId, array $commissionRate): CommissionRate
    {
        return new CommissionRate($programId, '1', 'foo', ValueType::FIXED(), .0, $commissionRate);
    }

    public function executeProductsCountRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null
    ): int {
        return 0;
    }

    public function executeAdvertisersCountRequest(): int
    {
        return 0;
    }

    public function executeAdvertisersRequest(int $page = 1, ?int $perPage = null): Collection
    {
        return Collection::make();
    }

    protected function getDetailsUrl(array $product)
    {
        return null;
    }
}
