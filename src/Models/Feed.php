<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * @property int id
 * @property int advertiser_id
 * @property string advertiser_name
 * @property int feed_id
 * @property bool joined
 * @property string region
 * @property string language
 * @property Carbon|null products_updated_at
 * @property Carbon|null imported_at
 * @property int products_count
 */
class Feed extends Model
{
    protected $fillable = [
        'advertiser_id',
        'advertiser_name',
        'feed_id',
        'joined',
        'region',
        'language',
        'products_updated_at',
        'imported_at',
        'products_count',
    ];

    protected $casts = [
        'feed_id' => 'integer',
        'joined' => 'boolean',
        'products_count' => 'integer',
    ];

    protected $dates = [
        'products_updated_at',
        'imported_at',
    ];

    public function getConnectionName()
    {
        return Config::get('affiliate.db.connection', parent::getConnectionName());
    }

    public function getTable()
    {
        return Config::get('affiliate.db.tables.feeds', parent::getTable());
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
