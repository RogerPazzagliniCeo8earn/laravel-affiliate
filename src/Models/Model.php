<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Support\Facades\Config;

abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    public function getConnectionName()
    {
        return Config::get('affiliate.db.connection', parent::getConnectionName());
    }
}
