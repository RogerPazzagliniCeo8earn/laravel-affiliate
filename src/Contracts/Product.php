<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

use Closure;
use Laravel\Scout\Builder;

interface Product extends Model
{
    /**
     * Perform a search against the model's indexed data.
     *
     * @param  string  $query
     * @param  Closure  $callback
     * @return Builder
     */
    public static function search($query = '', $callback = null);
}
