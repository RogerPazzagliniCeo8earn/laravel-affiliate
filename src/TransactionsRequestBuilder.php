<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use DateTime;
use Illuminate\Support\Collection;

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
    public function get()
    {
        $transactions = new Collection;
        foreach ($this->getNetworks() as $network) {
            $networkTransactions = $network->executeTransactionsRequest($this->programs, $this->fromDateTime, $this->toDateTime);
            $transactions = $transactions->merge($networkTransactions);
        }
        return $transactions;
    }
}
