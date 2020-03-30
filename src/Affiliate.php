<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Exceptions\NetworkNotFoundException;

class Affiliate
{
    /**
     * @param string $class
     * @return Network
     * @throws NetworkNotFoundException
     */
    public function network(string $class)
    {
        if (!class_exists($class)){
            throw new NetworkNotFoundException;
        }

        // todo: NetworkNotImplementsInterface exception

        return new $class;
    }

    /**
     * @return TransactionsRequestBuilder
     */
    public function transactions()
    {
        return new TransactionsRequestBuilder;
    }
}
