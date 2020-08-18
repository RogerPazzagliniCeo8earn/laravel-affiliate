<?php

namespace Tests\Unit\Networks;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use SoluzioneSoftware\LaravelAffiliate\Networks\Zanox;
use Tests\TestCase;

class ZanoxTest extends TestCase
{
    /**
     * @test
     * @throws GuzzleException
     */
    public function execute_commission_rates_request()
    {
        $programId = "1234";

        $data = [
            "page" => 0,
            "items" => 1,
            "total" => 1,
            "trackingCategoryItem" => [
                "trackingCategoryItem" => [
                    [
                        "@id" => "0",
                        "name" => "default",
                        "program" => [
                            "@id" => $programId,
                        ],
                        "adspace" => [
                            "@id" => "4321",
                        ],
                        "transactionType" => "sales",
                        "description" => "default",
                        "saleFixed" => .0,
                        "salePercent" => 8.,
                    ],
                ],
            ],
        ];


        $mock = new MockHandler([
            new Response(200, [], json_encode($data)),
        ]);
        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        $commissionGroups = (new Zanox())->executeCommissionRatesRequest($programId);

        $this->assertCount(count($data['trackingCategoryItem']['trackingCategoryItem']), $commissionGroups);
    }

    /**
     * @test
     * @throws GuzzleException
     */
    public function execute_transactions_request()
    {
        $advertiserId = 7052;

        $leadsData = [
            'page' => 0,
            'items' => 2,
            'total' => 2,
            'leadItems' => [
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
            ],
        ];

        $salesData = [
            'page' => 0,
            'items' => 1,
            'total' => 1,
            'saleItems' => [
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
            ]
        ];

        $mock = new MockHandler([
            new Response(200, [], json_encode($leadsData)),
            new Response(200, [], json_encode($salesData)),
        ]);

        $handlerStack = HandlerStack::create($mock);
        $this->instance('affiliate.client', new Client(['handler' => $handlerStack]));

        $commissionGroups = (new Zanox())->executeTransactionsRequest([$advertiserId]);

        $this->assertCount(count($leadsData['leadItems']) + count($salesData['saleItems']), $commissionGroups);
    }
}
