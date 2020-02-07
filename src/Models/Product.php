<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Database\Eloquent\Model;
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
    ];

    public function getConnectionName()
    {
        return Config::get('affiliate-networks.db.connection');
    }

    public function getTable()
    {
        return Config::get('affiliate.db.tables.products');
    }
}
