<?php

namespace Tests\Unit\Networks;

use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Psr7\Response;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Networks\Zanox;

class ZanoxTest extends AbstractNetwork
{
    protected function makeNetwork(): Network
    {
        return new Zanox();
    }

    protected function getLeadsTransactions(): array
    {
        return [
            [
                '@id' => '10293919-129c-dcc1-c67a-1523adsc2123',
                'reviewState' => 'confirmed',
                'trackingDate' => '2014-02-01T04:15:35.703+01:00',
                'modifiedDate' => '2014-05-02T04:30:29.480+02:00',
                'clickDate' => '2014-01-30T04:34:50.707+01:00',
                'adspace' => [
                    '@id' => '102938',
                    '$' => 'Adspace one'
                ],
                'admedium' => [
                    '@id' => '102948',
                    '$' => 'Sidebar 120x600'
                ],
                'program' => [
                    '@id' => '33354',
                    '$' => 'Test Program'
                ],
                'clickId' => '4920304020939182822',
                'clickInId' => '0',
                'commission' => '0.4',
                'currency' => 'EUR',
                'gpps' => [
                    [
                        '@id' => 'zpar0',
                        '$' => 'test19293019x19238ad3716d51adcd1626371ada2716311'
                    ],
                ],
            ],
            [
                '@id' => '1523415a-abbc-22ac-1234-15234abcd123',
                'reviewState' => 'confirmed',
                'trackingDate' => '2014-02-01T08:56:34.897+01:00',
                'modifiedDate' => '2014-04-11T19:05:51.993+02:00',
                'clickDate' => '2014-02-01T08:46:54.777+01:00',
                'adspace' => [
                    '@id' => '102939',
                    '$' => 'Adspace two'
                ],
                'admedium' => [
                    '@id' => '493857',
                    '$' => '120x60 Logo Main'
                ],
                'program' => [
                    '@id' => '33123',
                    '$' => 'TEST Prog DE'
                ],
                'clickId' => '1283918483783492844',
                'clickInId' => '0',
                'commission' => '9.0',
                'currency' => 'EUR',
                'gpps' => [
                    [
                        '@id' => 'zpar0',
                        '$' => '1727371aXXAAtest81273619199hdjauwhduaowidjksjhagaj'
                    ],
                ],
            ],
        ];
    }

    protected function getSalesTransactions(): array
    {
        return [
            [
                '@id' => '19ac662c-c460-4f09-b001-abcdedg',
                'reviewState' => 'confirmed',
                'trackingDate' => '2012-08-05T18:27:09.373+02:00',
                'modifiedDate' => '2012-08-20T10:04:18.417+02:00',
                'clickDate' => '2012-08-05T18:22:48.977+02:00',
                'adspace' => [
                    '@id' => '12345',
                    '$' => 'AdSpace name'
                ],
                'admedium' => [
                    '@id' => '654321',
                    '$' => 'Affiliate link name'
                ],
                'program' => [
                    '@id' => '1234',
                    '$' => 'Advertiser program'
                ],
                'clickId' => '17995036542191361',
                'clickInId' => '0',
                'amount' => '10.00',
                'commission' => '1.0',
                'currency' => 'EUR',
                'gpps' => [
                    [
                        '@id' => 'zpar0',
                        '$' => '1227247'
                    ],
                    [
                        '@id' => 'zpar1',
                        '$' => 'ABCHDE'
                    ],
                    [
                        '@id' => 'zpar2',
                        '$' => '102.01'
                    ],
                    [
                        '@id' => 'zpar3',
                        '$' => 'TopBannerA'
                    ],
                ],
                'reviewNote' => 'confirmed',
                'trackingCategory' => [
                    '@id' => '98765',
                    '$' => 'Tracking category name'
                ],
            ],
        ];
    }

    protected function getTransactions(): array
    {
        return array_merge($this->getLeadsTransactions(), $this->getSalesTransactions());
    }

    protected function getLeadsTransactionsResponse(): array
    {
        $transactions = $this->getLeadsTransactions();
        return [
            "page" => 0,
            "items" => count($transactions),
            "total" => count($transactions),
            "leadItems" => $transactions,
        ];
    }

    protected function getSaleTransactionsResponse(): array
    {
        $transactions = $this->getSalesTransactions();
        return [
            "page" => 0,
            "items" => count($transactions),
            "total" => count($transactions),
            "saleItems" => $transactions,
        ];
    }

    protected function getCommissionRates(): array
    {
        return [
            [
                "@id" => "0",
                "name" => "default",
                "program" => [
                    "@id" => "1234",
                ],
                "adspace" => [
                    "@id" => "4321",
                ],
                "transactionType" => "sales",
                "description" => "default",
                "saleFixed" => .0,
                "salePercent" => 8.,
            ],
        ];
    }

    protected function getCommissionRatesResponse(): array
    {
        $getCommissionRates = $this->getCommissionRates();
        return [
            "page" => 0,
            "items" => count($getCommissionRates),
            "total" => count($getCommissionRates),
            "trackingCategoryItem" => [
                "trackingCategoryItem" => $getCommissionRates
            ],
        ];
    }

    protected function getTransactionsMockHandler(): MockHandler
    {
        return new MockHandler([
            new Response(200, [], json_encode($this->getLeadsTransactionsResponse())),
            new Response(200, [], json_encode($this->getSaleTransactionsResponse())),
        ]);
    }
}
