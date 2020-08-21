<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * @property int id
 * @property int advertiser_id
 * @property string advertiser_name
 * @property int feed_id
 * @property bool joined
 * @property bool enabled
 * @property string region
 * @property string language
 * @property Carbon|null products_updated_at
 * @property Carbon|null imported_at
 * @property int products_count
 * @property array original_data
 * @method static Builder enabled(bool $enabled = true)
 */
class Feed extends Model
{
    protected $fillable = [
        'advertiser_id',
        'advertiser_name',
        'feed_id',
        'joined',
        'enabled',
        'region',
        'language',
        'products_updated_at',
        'imported_at',
        'products_count',
        'original_data',
    ];

    protected $casts = [
        'feed_id' => 'integer',
        'joined' => 'boolean',
        'enabled' => 'boolean',
        'products_count' => 'integer',
        'original_data' => 'array',
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

    public static function scopeEnabled(Builder $query, bool $enabled = true)
    {
        return $query->where('enabled', $enabled);
    }
}
