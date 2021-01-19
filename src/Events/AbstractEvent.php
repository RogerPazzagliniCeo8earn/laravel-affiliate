<?php

namespace SoluzioneSoftware\LaravelAffiliate\Events;

abstract class AbstractEvent
{
    /**
     * @var array
     */
    public $products;

    /**
     * @param  array  $products
     */
    public function __construct(array $products)
    {
        $this->products = $products;
    }
}
