<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Exception;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Validator;
use SoluzioneSoftware\LaravelAffiliate\CsvImporter;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;

class ProductsImport
{
    /**
     * @var Feed
     */
    protected $feed;

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @param  Feed  $feed
     */
    public function __construct(Feed $feed)
    {
        $this->feed = $feed;
        $this->connection = (new Product())->getConnection();
    }

    /**
     * @param  string  $path
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
    }

    public function afterImport()
    {
    }

    public function mapRow(array $row): ?array
    {
        $validator = Validator::make($row, [
            'product_name' => "nullable|url|max:".Builder::$defaultStringLength,
            'merchant_image_url' => 'nullable|url',
        ]);

        if ($validator->fails()) {
            return null;
        }

        return [
            'product_id' => $row['aw_product_id'],
            'title' => $row['product_name'],
            'description' => $row['description'],
            'image_url' => $row['merchant_image_url'],
            'details_link' => $row['merchant_deep_link'],
            'price' => $row['search_price'],
            'currency' => $row['currency'],
            'last_updated_at' => $row['last_updated'] ?: null,
        ];
    }

    /**
     * @param  array  $rows
     * @throws Exception
     */
    public function onChunkRead(array $rows)
    {
        $mappedRows = [];

        foreach ($rows as $row) {
            $mappedRow = $this->mapRow($row);
            if ($mappedRow) {
                $mappedRows[] = array_merge($mappedRow, ['feed_id' => $this->feed->id]);
            }
        }

        if (!count($mappedRows)) {
            return;
        }

        $columns = array_keys($mappedRows[0]);
        $columnNames = implode(',', $columns);

        $updateValues = array_map(function (string $column) {
            return "$column=VALUES($column)";
        }, $columns);
        $updateValues = implode(',', $updateValues);

        $bindings = [];
        $values = array_map(function (array $row) use (&$bindings) {
            foreach ($row as $value) {
                $bindings[] = $value;
            }
            $values = array_merge(array_fill(0, count($row), '?'), ['NOW()', 'NOW()']);
            return '('.implode(',', $values).')';
        }, $mappedRows);
        $values = implode(',', $values);

        /** @noinspection SqlNoDataSourceInspection */
        $q = "INSERT INTO affiliate_products ($columnNames,created_at,updated_at) VALUES $values ON DUPLICATE KEY UPDATE $updateValues,updated_at=NOW()";

        if (!$this->connection->insert($q, $bindings)) {
            throw new Exception('Batch was not inserted');
        }
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
