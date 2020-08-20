<?php

/* @var $factory Factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;

$factory
    ->define(Product::class, function (Faker $faker) {
        return [
            'feed_id' => factory(Feed::class),
            'product_id' => $faker->numberBetween(1),
            'title' => $faker->sentence(3),
            'description' => $faker->optional()->text(),
            'image_url' => $faker->optional()->imageUrl(),
            'details_link' => '',
            'price' => $faker->randomFloat(2, .01, 1000),
            'currency' => $faker->countryCode,
        ];
    });
