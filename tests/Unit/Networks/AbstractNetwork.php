<?php

namespace Tests\Unit\Networks;

use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Config;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use Tests\TestCase;

abstract class AbstractNetwork extends TestCase
{
    /**
     * @test
     */
    public function execute_commission_rates_request()
    {
        $this->mockClient($this->getCommissionRatesMockHandler());
        Config::set('affiliate.credentials.awin.publisher_id', '123');

        $commissionRates = $this->makeNetwork()->executeCommissionRatesRequest(0);

        $this->assertCount(count($this->getCommissionRates()), $commissionRates);
    }

    /**
     * @test
     */
    public function execute_transactions_request()
    {
        $this->mockClient($this->getTransactionsMockHandler());
        Config::set('affiliate.networks.awin.tracking_code_param', 'abc');

        $commissionGroups = $this->makeNetwork()->executeTransactionsRequest();

        $this->assertCount(count($this->getTransactions()), $commissionGroups);
    }

    protected abstract function makeNetwork(): Network;

    protected abstract function getCommissionRates(): array;

    protected abstract function getTransactions(): array;

    protected function getCommissionRatesResponse(): array
    {
        return $this->getCommissionRates();
    }

    protected function getTransactionsResponse(): array
    {
        return $this->getTransactions();
    }

    protected function getCommissionRatesMockHandler(): MockHandler
    {
        return new MockHandler([
            new Response(200, [], json_encode($this->getCommissionRatesResponse()))
        ]);
    }

    protected function getTransactionsMockHandler(): MockHandler
    {
        return new MockHandler([
            new Response(200, [], json_encode($this->getTransactionsResponse()))
        ]);
    }

    protected function mockClient(MockHandler $mock)
    {
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));
    }
}
