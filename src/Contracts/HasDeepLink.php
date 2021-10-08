<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

interface HasDeepLink
{
    public function getDeepLink(string $advertiser, string $url, ?string $trackingCode = null): string;
}
