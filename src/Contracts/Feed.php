<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface Feed extends Model
{
    /**
     * @return Builder
     */
    public static function whereNeedsUpdate();
}
