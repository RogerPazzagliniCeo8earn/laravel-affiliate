<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use DateTime;
use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use InvalidArgumentException;
use SoluzioneSoftware\LaravelAffiliate\Contracts\NetworkWithProductFeeds;
use SoluzioneSoftware\LaravelAffiliate\CsvImporter;
use SoluzioneSoftware\LaravelAffiliate\Events\ProductsDeletedEvent;
use SoluzioneSoftware\LaravelAffiliate\Events\ProductsInsertedEvent;
use SoluzioneSoftware\LaravelAffiliate\Events\ProductsUpdatedEvent;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;
use stdClass;

class ProductsImport extends AbstractImport
{
    use ResolvesBindings;

    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var Collection
     */
    protected $dbProducts;

    /**
     * @var array
     */
    protected $processedProducts = [];

    /**
     * @var int
     */
    protected $chunkSize;

    /**
     * @param  Feed  $feed
     * @throws BindingResolutionException
     */
    public function __construct(Feed $feed)
    {
        $network = $feed->getNetwork();
        if (!($network instanceof NetworkWithProductFeeds)) {
            $class = get_class($network);
            throw new InvalidArgumentException("Class '$class' must implement '".NetworkWithProductFeeds::class."' interface.");
        }

        parent::__construct($network);

        $this->feed = $feed;
        $this->connection = static::resolveProductModelBinding()->getConnection();
        $this->dbProducts = $this->connection
            ->table(static::resolveProductModelBinding()->getTable())
            ->where($feed->getForeignKey(), $feed->getKey())
            ->get(['last_updated_at', 'product_id', 'updated_at']);
        $this->chunkSize = (int) Config::get('affiliate.chunk_size', 1000);
    }

    /**
     * @param  string  $path
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function import(string $path)
    {
        $importer = new CsvImporter($path);

        $this->beforeImport();

        while ($data = $importer->get($this->chunkSize())) {
            $this->onChunkRead($data);
        }

        $this->afterImport();
    }

    public function beforeImport()
    {
        //
    }

    public function chunkSize(): int
    {
        return Config::get('affiliate.product_feeds.import_chunk_size');
    }

    /**
     * @param  array  $rows
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function onChunkRead(array $rows)
    {
        $feedForeignKey = $this->feed->getForeignKey();
        $feedKey = $this->feed->getKey();

        $newProducts = [];
        $updatedProducts = [];

        foreach ($rows as $row) {
            $mappedRow = $this->mapRow($row);
            if (!$mappedRow) {
                continue;
            }
            $product = array_merge($mappedRow, [$feedForeignKey => $feedKey]);
            $res = $this->processRow($product);
            if ($res === true) {
                $newProducts[] = $product;
            } elseif ($res === false) {
                $updatedProducts[] = $product;
            }

            $this->processedProducts[] = $product;
        }

        $this->insertProducts($newProducts);
        $this->updateProducts($updatedProducts);
    }

    public function mapRow(array $row): ?array
    {
        $mappedRow = $this->network->mapProductRow($row);

        $validator = Validator::make($mappedRow, [
            'title' => "nullable|string|max:".Builder::$defaultStringLength,
            'image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            Log::info('Row validation fail: '.json_encode($row));
            return null;
        }

        return $mappedRow;
    }

    /**
     * @param  array  $row
     * @return bool|null true if new, false if updated, null unchanged
     * @throws Exception
     */
    protected function processRow(array $row): ?bool
    {
        /** @var array|null $current */
        $current = $this->getSavedProduct($row);

        if (is_null($current)) {
            return true;
        } elseif ($this->rowWasUpdated($row, $current)) {
            return false;
        }
        return null;
    }

    /**
     * @param  array  $row
     * @return array|null
     */
    protected function getSavedProduct(array $row): ?array
    {
        $position = null;
        /** @var stdClass|null $current */
        $current = $this->dbProducts
            ->first(function (stdClass $dbProduct, $key) use ($row, &$position) {
                if ($dbProduct->product_id === $row['product_id']) {
                    $position = $key;
                    return true;
                }
                return false;
            });

        if ($position) {
            $this->dbProducts->offsetUnset($position);
        }

        return $current ? get_object_vars($current) : null;
    }

    /**
     * @param  array  $new
     * @param  array  $old
     * @return bool
     * @throws Exception
     */
    protected function rowWasUpdated(array $new, array $old): bool
    {
        return (
                empty($new['last_updated_at'])
                || empty($old['last_updated_at'])
                || new DateTime($new['last_updated_at']) > new DateTime($old['last_updated_at'])
            )
            && ( // check if product is already imported
                empty($old['updated_at'])
                || $this->feed->imported_at->greaterThanOrEqualTo(new DateTime($old['updated_at']))
            );
    }

    /**
     * @param  array  $products
     * @throws BindingResolutionException
     */
    protected function insertProducts(array $products): void
    {
        if (count($products) === 0) {
            return;
        }

        $model = static::resolveProductModelBinding();
        $time = $model->freshTimestamp();

        $inserted = $model::query()
            ->insert(
                array_map(
                    function (array $product) use ($time, $model) {
                        return array_merge(
                            [
                                $model->getCreatedAtColumn() => $time,
                                $model->getUpdatedAtColumn() => $time,
                            ],
                            $this->prepareForDB($product)
                        );
                    },
                    $products
                )
            );

        if ($inserted) {
            Event::dispatch(new ProductsInsertedEvent($this->feed, $products));
        } else {
            Log::info("Products weren't inserted");
        }
    }

    protected function prepareForDB(array $product): array
    {
        return Arr::only($product, [
            $this->feed->getForeignKey(),
            'product_id',
            'title',
            'description',
            'image_url',
            'details_link',
            'price',
            'currency',
            'last_updated_at',
        ]);
    }

    /**
     * @param  array  $products
     * @throws BindingResolutionException
     */
    protected function updateProducts(array $products): void
    {
        if (count($products) === 0) {
            return;
        }

        $feedForeignKey = $this->feed->getForeignKey();
        $feedKey = $this->feed->getKey();

        $updatedProducts = [];
        $model = static::resolveProductModelBinding();

        foreach ($products as $product) {
            $updated = $model::query()
                ->where($feedForeignKey, $feedKey)
                ->where('product_id', $product['product_id'])
                ->update(
                    array_merge(
                        [
                            $model->getUpdatedAtColumn() => $model->freshTimestamp(),
                        ],
                        $this->prepareForDB($product)
                    )
                );

            if ($updated > 0) {
                $updatedProducts[] = $product;
            }
        }

        if ($fails = count($products) - count($updatedProducts) !== 0) {
            Log::info("$fails products weren't updated");
        }

        if (count($products) !== 0) {
            Event::dispatch(new ProductsUpdatedEvent($this->feed, $products));
        }
    }

    /**
     * @throws BindingResolutionException
     */
    public function afterImport()
    {
        $this->deleteProducts();
    }

    /**
     * @throws BindingResolutionException
     */
    protected function deleteProducts(): void
    {
        if (count($this->processedProducts) === 0) {
            return;
        }

        $toDelete = $this->getRowsToDelete();

        if ($toDelete->isEmpty()) {
            return;
        }

        $toDelete
            ->chunk($this->chunkSize)
            ->each(function (Collection $products) {
                $toDeleteKeys = $products
                    ->map(function (stdClass $product) {
                        return $product->{static::resolveProductModelBinding()->getKeyName()};
                    });

                $deleted = $this->connection->table(static::resolveProductModelBinding()->getTable())
                    ->whereIn(static::resolveProductModelBinding()->getKeyName(), $toDeleteKeys)
                    ->delete();

                if ($fails = $toDeleteKeys->count() - $deleted !== 0) {
                    Log::info("$fails products weren't deleted");
                }

                if ($deleted !== 0) {
                    $deletedProducts = $products
                        ->map(function (stdClass $product) {
                            return get_object_vars($product);
                        })
                        ->toArray();
                    Event::dispatch(new ProductsDeletedEvent($this->feed, $deletedProducts));
                }
            });
    }

    /**
     * @return Collection
     */
    protected function getRowsToDelete(): Collection
    {
        $processedIds = array_map(
            function (array $product) {
                return $product['product_id'];
            },
            $this->processedProducts
        );

        $toDelete = collect();

        $this->feed
            ->products()
            ->toBase()
            ->latest()
            ->chunk($this->chunkSize, function (Collection $products) use ($toDelete, $processedIds) {
                $products
                    ->each(function (stdClass $product) use ($toDelete, $processedIds) {
                        if (!in_array($product->product_id, $processedIds)) {
                            $toDelete->add($product);
                        }
                    });
            });

        return $toDelete;
    }
}
