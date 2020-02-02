<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Illuminate\Support\Facades\Facade;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

/**
 * @see \SoluzioneSoftware\LaravelAffiliate\NetworksManager
 *
 * @method static Network network(string $class)
 */
class LaravelAffiliate extends Facade
{
    public static function getFacadeAccessor()
    {
        return 'laravel-affiliate';
    }
}
