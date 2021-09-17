<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Laravel\Scout\Searchable;

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
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'feed_id',
        'product_id',
        'title',
        'description',
        'image_url',
        'details_link',
        'price',
        'currency',
        'checksum',
        'last_updated_at',
    ];

    protected $dates = [
        'last_updated_at',
    ];

    public function getTable()
    {
        return Config::get('affiliate.db.tables.products', parent::getTable());
    }

    public function feed()
    {
        return $this->belongsTo(Feed::class);
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return Arr::only($this->toArray(), ['title', 'description']);
    }
}
