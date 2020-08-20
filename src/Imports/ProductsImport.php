<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;

class ProductsImport implements WithHeadingRow, OnEachRow, WithChunkReading, ToCollection
{
    /**
     * @var Feed
     */
    private $feed;

    /**
     * @var array
     */
    private $products;

    /**
     * @param  Feed  $feed
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
        $this->products = Product::query()->pluck('product_id')->toArray();
    }

    /**
     * @inheritDoc
     */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        // skip if not updated and already imported
        if (
            $this->feed->products_updated_at
            && $this->feed->products_updated_at->greaterThanOrEqualTo(Date::createFromTimestamp($data['last_updated'])) // fixme: consider tz
            && in_array($data['aw_product_id'], $this->products)
        ){
            return;
        }

        $data['feed_id'] = $this->feed->id;
        $data['product_id'] = $data['aw_product_id'];
        $data['title'] = $data['product_name'];
        $data['image_url'] = $data['merchant_image_url'];
        $data['price'] = $data['search_price'];
        $data['details_link'] = $data['merchant_deep_link'];

        Product::query()->updateOrCreate(Arr::only($data, ['feed_id', 'product_id']), $data);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function collection(Collection $rows)
    {
        $diff = array_diff($this->products, $rows->pluck('aw_product_id')->toArray());

        Product::query()
            ->where('feed_id', $this->feed->id)
            ->whereNotIn('product_id', $diff)
            ->delete();
    }

    /**
     * @inheritDoc
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
