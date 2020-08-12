<?php

namespace SoluzioneSoftware\LaravelAffiliate\Enums;

use Konekt\Enum\Enum;

/**
 * @method static FIXED()
 * @method static PERCENTAGE()
 */
class ValueType extends Enum
{
    const FIXED = 'fixed';
    const PERCENTAGE = 'percentage';
}
