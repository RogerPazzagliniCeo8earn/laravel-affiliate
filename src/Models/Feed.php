<?php

namespace SoluzioneSoftware\LaravelAffiliate\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Facades\Affiliate;

/**
 * @property int id
 * @property string network
 * @property string feed_id
 * @property int advertiser_id
 * @property string advertiser_name
 * @property bool joined
 * @property bool enabled
 * @property string region
 * @property string language
 * @property Carbon|null products_updated_at
 * @property Carbon|null imported_at
 * @property Carbon|null downloaded_at
 * @property int products_count
 * @property array original_data
 * @method static Builder enabled(bool $enabled = true)
 */
class Feed extends Model
{
    protected $fillable = [
        'network',
        'feed_id',
        'advertiser_id',
        'advertiser_name',
        'joined',
        'enabled',
        'region',
        'language',
        'products_updated_at',
        'imported_at',
        'downloaded_at',
        'products_count',
        'original_data',
    ];

    protected $casts = [
        'joined' => 'boolean',
        'enabled' => 'boolean',
        'products_count' => 'integer',
        'original_data' => 'array',
    ];

    protected $dates = [
        'products_updated_at',
        'imported_at',
        'downloaded_at',
    ];

    /**
     * @param  Builder|QueryBuilder  $query
     * @param  bool  $enabled
     * @return mixed
     */
    public static function scopeEnabled($query, bool $enabled = true)
    {
        return $query->where('enabled', $enabled);
    }

    /**
     * @param  Builder|QueryBuilder  $query
     * @return Builder|QueryBuilder
     */
    public static function scopeWhereNeedsUpdate($query)
    {
        $query->where('enabled', true);

        if (Config::get('affiliate.product_feeds.only_joined')) {
            $query->where('joined', true);
        }

        if (!is_null($regions = Config::get('affiliate.product_feeds.regions'))) {
            $query->whereIn('region', $regions);
        }

        if (!is_null($languages = Config::get('affiliate.product_feeds.languages'))) {
            $query->whereIn('language', $languages);
        }

        // consider updating only new feeds
        $query
            ->where(function (Builder $query) {
                $query
                    ->whereNull('products_updated_at')
                    ->orWhereNull('imported_at')
                    ->orWhereRaw('imported_at > products_updated_at');
            });

        return $query;
    }

    public function getConnectionName()
    {
        return Config::get('affiliate.db.connection', parent::getConnectionName());
    }

    public function getTable()
    {
        return Config::get('affiliate.db.tables.feeds', parent::getTable());
    }

    /**
     * @return Network
     * @throws InvalidArgumentException
     */
    public function getNetwork(): Network
    {
        return Affiliate::resolveNetwork($this->network);
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function needsUpdate()
    {
        $onlyJoined = Config::get('affiliate.product_feeds.only_joined');
        $regions = Config::get('affiliate.product_feeds.regions');
        $languages = Config::get('affiliate.product_feeds.languages');

        return $this->enabled
            && (!$onlyJoined || $this->joined)
            && (is_null($regions) || in_array($this->region, $regions))
            && (is_null($languages) || in_array($this->language, $languages))
            && (
                is_null($this->products_updated_at)
                || is_null($this->imported_at)
                || $this->imported_at->greaterThan($this->products_updated_at)
            );
    }

    public function needsDownload(): bool
    {
        return
            !$this->downloaded_at
            || !$this->imported_at
            || $this->imported_at->greaterThan($this->downloaded_at);
    }

    public function getProductsCount(): int
    {
        return $this->products_count;
    }
}
