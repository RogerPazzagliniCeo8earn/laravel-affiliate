<?php

namespace SoluzioneSoftware\LaravelAffiliate\Enums;

use Konekt\Enum\Enum;

/**
 * @method static CONFIRMED()
 * @method static DECLINED()
 * @method static PENDING()
 */
class TransactionStatus extends Enum
{
    const CONFIRMED = 'confirmed';
    const DECLINED = 'declined';
    const PENDING = 'pending';

    public const __DEFAULT = self::PENDING;
}
