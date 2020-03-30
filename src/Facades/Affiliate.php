<?php


namespace SoluzioneSoftware\LaravelAffiliate\Facades;


use Illuminate\Support\Facades\Facade;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\TransactionsRequestBuilder;

/**
 * @see \SoluzioneSoftware\LaravelAffiliate\Affiliate
 *
 * @method static Network network(string $class)
 * @method static TransactionsRequestBuilder transactions()
 */
class Affiliate extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'affiliate';
    }
}
