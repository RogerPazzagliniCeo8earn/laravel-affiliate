<?php

namespace Tests\Unit\Networks;

use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Networks\Awin;

class AwinTest extends AbstractNetwork
{
    protected function makeNetwork(): Network
    {
        return new Awin();
    }

    protected function getTransactions(): array
    {
        return [
            [
                "id" => 259630312,
                "url" => "http://www.publisher.com",
                "advertiserId" => 7052,
                "publisherId" => 189069,
                "commissionSharingPublisherId" => 55555,
                "commissionSharingSelectedRatePublisherId" => 189069,
                "siteName" => "Publisher",
                "commissionStatus" => "pending",
                "commissionAmount" => [
                    "amount" => 5.59,
                    "currency" => "GBP"
                ],
                "saleAmount" => [
                    "amount" => 55.96,
                    "currency" => "GBP"
                ],
                "ipHash" => "-66667778889991112223",
                "customerCountry" => "GB",
                "clickRefs" => [
                    "clickRef" => "12345",
                    "clickRef2" => "22222",
                    "clickRef3" => "33333",
                    "clickRef4" => "44444",
                    "clickRef5" => "55555",
                    "clickRef6" => "66666"
                ],
                "clickDate" => "2017-01-23T12:18:00",
                "transactionDate" => "2017-02-20T22:04:00",
                "validationDate" => null,
                "type" => "Commission group transaction",
                "declineReason" => null,
                "voucherCodeUsed" => true,
                "voucherCode" => "example123",
                "lapseTime" => 2454307,
                "amended" => false,
                "amendReason" => null,
                "oldSaleAmount" => null,
                "oldCommissionAmount" => null,
                "clickDevice" => "Windows",
                "transactionDevice" => "Windows",
                "publisherUrl" => "http://www.publisher.com/search?query=dvds",
                "advertiserCountry" => "GB",
                "orderRef" => "111222333444",
                "customParameters" => [
                    [
                        "key" => "1",
                        "value" => "555666"
                    ],
                    [
                        "key" => "2",
                        "value" => "example entry"
                    ],
                    [
                        "key" => "3",
                        "value" => "LLLMMMNNN"
                    ]
                ],
                "transactionParts" => [
                    [
                        "commissionGroupId" => 12345,
                        "amount" => 44.76,
                        "commissionAmount" => 4.50,
                        "commissionGroupCode" => "DEFAULT",
                        "commissionGroupName" => "Default Commission"
                    ],

                    [

                        "commissionGroupId" => 654321,
                        "amount" => 11.20,
                        "commissionAmount" => 1.50,
                        "commissionGroupCode" => "EXISTING",
                        "commissionGroupName" => "EXISTING"
                    ]


                ],
                "paidToPublisher" => false,
                "paymentId" => 0,
                "transactionQueryId" => 0,
                "originalSaleAmount" => null
            ],
        ];
    }

    protected function getCommissionRates(): array
    {
        return [
            [
                "groupId" => 147,
                "groupCode" => "GP1",
                "groupName" => "group 1",
                "type" => "percentage",
                "percentage" => 2
            ],
            [
                "groupId" => 19474,
                "groupCode" => "JS",
                "groupName" => "Julius's's",
                "type" => "fix",
                "amount" => 100,
                "currency" => "GBP"
            ],
        ];
    }

    protected function getCommissionRatesResponse(): array
    {
        return [
            "advertiser" => 1001,
            "publisher" => 45628,
            "commissionGroups" => $this->getCommissionRates(),
        ];
    }
}
