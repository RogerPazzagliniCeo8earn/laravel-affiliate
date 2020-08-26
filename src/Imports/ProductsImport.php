<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;
use Maatwebsite\Excel\Row;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;

class ProductsImport implements WithHeadingRow, OnEachRow, WithChunkReading,WithEvents
{
    use RegistersEventListeners;

    /**
     * @var Feed
     */
    public $feed;

    /**
     * @var array
     */
    private $products;

    /**
     * @var array
     */
    public $processedProductIds = [];

    /**
     * @param  Feed  $feed
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
        $this->products = Product::query()->where('feed_id', $feed->id)->pluck('product_id')->toArray();
    }

    /**
     * @param  AfterImport  $afterImport
     * @throws Exception
     */
    public static function afterImport(AfterImport $afterImport)
    {
        /** @var self $importable */
        $importable = $afterImport->getConcernable();
        Product::query()
            ->whereNotIn('product_id', $importable->processedProductIds)
            ->get()
            ->each(function (Product $product) {
                $product->delete();
            });

        $importable->feed->update(['products_updated_at' => Date::now()]);
    }

    /**
     * @inheritDoc
     */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $this->processedProductIds[] = $data['aw_product_id'];

        // skip if not updated and already imported
        if (
            $this->feed->products_updated_at
            && $data['last_updated']
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
     */
    public function chunkSize(): int
    {
        return 1000;
    }
}
