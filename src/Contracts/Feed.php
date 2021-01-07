<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Query\Builder as QueryBuilder;

interface Feed extends Model
{
    /**
     * @param  Builder|QueryBuilder  $query
     * @return Builder|QueryBuilder
     */
    public static function scopeWhereNeedsUpdate($query);

    /**
     * @return HasMany
     */
    public function products();

    public function getProductsCount(): int;
}
