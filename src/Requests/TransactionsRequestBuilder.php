<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use DateTime;
use Exception;
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
    protected function executeGetForNetwork(Network $network, int $page, int $perPage)
    {
        return $network->executeTransactionsRequest(
            $this->getPrograms($network), $this->fromDateTime, $this->toDateTime, $page, $perPage
        );
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function executeCountForNetwork(Network $network): int
    {
//        fixme: use lazy load and remove limit for 100 items
        return min($network->executeProductsCountRequest($this->getPrograms($network), $this->keyword, $this->languages), 100);
    }
}
