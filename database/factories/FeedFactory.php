<?php

/* @var $factory Factory */

use Faker\Generator as Faker;
use Illuminate\Database\Eloquent\Factory;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

$factory
    ->define(Feed::class, function (Faker $faker) {
        return [
            'advertiser_id' => $faker->numberBetween(1, 4294967295),
            'advertiser_name' => $faker->company,
            'feed_id' => $faker->unique()->numberBetween(1, 4294967295),
            'joined' => $faker->boolean,
            'enabled' => $faker->boolean,
            'region' => $faker->countryCode,
            'language' => $faker->languageCode,
            'imported_at' => $faker->optional()->dateTime,
            'products_count' => $faker->unique()->numberBetween(1, 4294967295),
            'products_updated_at' => $faker->optional()->dateTime,
            'original_data' => [],
        ];
    });
