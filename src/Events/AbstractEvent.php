<?php

namespace SoluzioneSoftware\LaravelAffiliate\Events;

use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

abstract class AbstractEvent
{
    /**
     * @var Feed
     */
    public $feed;

    /**
     * @var array
     */
    public $products;

    /**
     * @param  Feed  $feed
     * @param  array  $products
     */
    public function __construct(Feed $feed, array $products)
    {
        $this->feed = $feed;
        $this->products = $products;
    }
}
