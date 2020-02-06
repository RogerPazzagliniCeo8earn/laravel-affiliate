<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use DateTime;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * @property int id
 * @property int advertiser_id
 * @property int feed_id
 * @property bool joined
 * @property string region
 * @property string language
 * @property DateTime|null imported_at
 */
class Feed extends Model
{
    protected $fillable = [
        'advertiser_id',
        'feed_id',
        'joined',
        'region',
        'language',
        'imported_at',
    ];

    protected $casts = [
        'joined' => 'boolean',
    ];

    protected $dates = [
        'imported_at',
    ];

    public function getConnectionName()
    {
        return Config::get('affiliate.db.connection');
    }

    public function getTable()
    {
        return Config::get('affiliate.db.tables.feeds');
    }
}
