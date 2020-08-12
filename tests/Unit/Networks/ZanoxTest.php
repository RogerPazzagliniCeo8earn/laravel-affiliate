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
    public function execute_commission_groups_request()
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
}
