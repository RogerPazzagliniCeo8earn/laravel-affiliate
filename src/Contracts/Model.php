<?php

namespace SoluzioneSoftware\LaravelAffiliate\Contracts;

use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

interface Model
{
    /**
     * @return Builder
     */
    public static function query();

    /**
     * @param  array|string  $relations
     * @return Builder|static
     */
    public static function with($relations);

    /**
     * @param  array|mixed  $columns
     * @return Collection|static[]
     */
    public static function all($columns = ['*']);

    /**
     * @return string
     */
    public function getTable();

    /**
     * @return Connection
     */
    public function getConnection();

    /**
     * @return string|null
     */
    public function getConnectionName();

    /**
     * @param  array  $attributes
     * @param  array  $options
     * @return bool
     */
    public function update(array $attributes = [], array $options = []);

    /**
     * @return bool|null
     */
    public function delete();

    /**
     * @return array
     */
    public function toArray();
}
