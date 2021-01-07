<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

interface Feed extends Model
{
    /**
     * @return Builder
     */
    public static function whereNeedsUpdate();

    /**
     * @return HasMany
     */
    public function products();
}
