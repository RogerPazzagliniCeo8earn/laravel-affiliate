<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Support\Facades\Config;

class Advertiser extends Model
{
    protected $fillable = [
        'network',
        'advertiser_id',
        'url',
        'name',
        'region',
        'currency',
        'original_data',
    ];

    protected $casts = [
        'original_data' => 'array',
    ];

    public function getTable()
    {
        return Config::get('affiliate.db.tables.advertisers', parent::getTable());
    }
}
