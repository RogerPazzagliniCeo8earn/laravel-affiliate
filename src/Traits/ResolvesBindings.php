<?php

namespace SoluzioneSoftware\LaravelAffiliate\Traits;

use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;

trait ResolvesBindings
{
    /**
     * @return Feed
     * @throws BindingResolutionException
     */
    private static function resolveFeedModelBinding(): Feed
    {
        return Container::getInstance()->make(Feed::class);
    }

    /**
     * @return Product
     * @throws BindingResolutionException
     */
    private static function resolveProductModelBinding(): Product
    {
        return Container::getInstance()->make(Product::class);
    }
}
