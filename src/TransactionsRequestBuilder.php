<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use DateTime;
use Exception;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class TransactionsRequestBuilder extends AbstractRequestBuilder
{
    /**
     * @var DateTime|null
     */
    protected $fromDateTime = null;

    /**
     * @var DateTime|null
     */
    protected $toDateTime = null;

    public function dateFrom(DateTime $dateTime)
    {
        $this->fromDateTime = $dateTime;
        return $this;
    }

    public function dateTo(DateTime $dateTime)
    {
        $this->toDateTime = $dateTime;
        return $this;
    }

    /**
     * @inheritDoc
     */
    protected function executeGet(int $page = 1, int $perPage = 10)
    {
        $transactions = new Collection;
        foreach ($this->getNetworks() as $network) {
            $networkTransactions = $network->executeTransactionsRequest(null, $this->fromDateTime, $this->toDateTime);
            $transactions = $transactions->merge($networkTransactions);
        }
        return $transactions;
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function executeCount(): int
    {
        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function executeCountForNetwork(Network $network): int
    {
        throw new Exception('Not implemented');
    }
}
