<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class ProductsImport implements WithHeadingRow, OnEachRow, WithChunkReading
{
    /**
     * @var Collection
     */
    private $feeds;

    /**
     * @var Carbon
     */
    private $lastUpdatedFilter;

    /**
     * @var Collection
     */
    private $products;

    /**
     * @param Collection $feeds
     * @param Carbon $lastUpdatedFilter
     */
    public function __construct(Collection $feeds, Carbon $lastUpdatedFilter)
    {
        $this->feeds = $feeds;
        $this->lastUpdatedFilter = $lastUpdatedFilter;
        $this->products = Product::all(['product_id'])->pluck('product_id')->toArray();
    }

    /**
     * @inheritDoc
     */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // skip if not updated and already imported
        if ($this->lastUpdatedFilter->greaterThan(Date::createFromTimestamp($data['last_updated']))){ // fixme: consider tz
            if (in_array($data['aw_product_id'], $this->products)){
                Log::debug("ProductsImport: Skip row #{$row->getIndex()}");
                return;
            }
        }

        /** @var Feed|null $feed */
        $feed = $this->feeds->whereStrict('feed_id', $data['data_feed_id'])->first();
        if (is_null($feed)){
            Log::warning("Products row not valid. data_feed_id: {$data['data_feed_id']}, aw_product_id: {$data['aw_product_id']}");
            return;
        }
        $data['feed_id'] = $feed->id;
        $data['product_id'] = $data['aw_product_id'];
        $data['title'] = $data['product_name'];
        $data['image_url'] = $data['merchant_image_url'];
        $data['price'] = $data['search_price'];

        Product::query()->updateOrCreate(Arr::only($data, ['feed_id', 'product_id']), $data);
    }

    /**
     * @inheritDoc
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
