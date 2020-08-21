<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;

/**
 * @property int feed_id
 * @property string product_id
 * @property string title
 * @property string|null description
 * @property string|null image_url
 * @property string details_link
 * @property float price
 * @property string currency
 * @property Carbon|null last_updated_at
 */
class Product extends Model
{
    protected $fillable = [
        'feed_id',
        'product_id',
        'title',
        'description',
        'image_url',
        'details_link',
        'price',
        'currency',
        'last_updated_at',
    ];

    protected $dates = [
        'last_updated_at',
    ];

    public function getConnectionName()
    {
        return Config::get('affiliate.db.connection', parent::getConnectionName());
    }

    public function getTable()
    {
        return Config::get('affiliate.db.tables.products', parent::getTable());
    }

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }
}
