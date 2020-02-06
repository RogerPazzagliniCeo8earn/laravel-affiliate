<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class ProductsImport implements WithHeadingRow, OnEachRow, WithChunkReading
{
    /**
     * @var Feed
     */
    private $feed;

    /**
     * @param Feed $feed
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
    }

    /**
     * @inheritDoc
     */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $data['feed_id'] = $this->feed->id;
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
