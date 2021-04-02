<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use SoluzioneSoftware\LaravelAffiliate\Affiliate;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;
use Tests\TestCase;

/**
 * @see Affiliate
 */
class AffiliateTest extends TestCase
{
    use RefreshDatabase;
    use ResolvesBindings;
}
