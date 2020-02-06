<?php


namespace SoluzioneSoftware\LaravelAffiliate\Facades;


use Illuminate\Support\Facades\Facade;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

/**
 * @see \SoluzioneSoftware\LaravelAffiliate\Affiliate
 *
 * @method static Network network(string $class)
 */
class Affiliate extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'affiliate';
    }
}
