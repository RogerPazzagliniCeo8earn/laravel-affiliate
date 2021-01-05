<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Matriphe\ISO639\ISO639;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Feed;

class FeedsImport implements WithHeadingRow, OnEachRow, ToCollection
{
    public static function getAttributeNames()
    {
        return [
            'advertiser_id',
            'advertiser_name',
            'feed_id',
            'joined',
            'enabled',
            'products_count',
            'imported_at',
            'region',
            'language',
        ];
    }

    /**
     * @inheritDoc
     */
    public function onRow(Row $row)
    {
        $data = static::map($row->toArray());

        Feed::query()->updateOrCreate(Arr::only($data, 'feed_id'), $data);
    }

    public static function map(array $row)
    {
        return [
            'advertiser_id' => (string) $row['advertiser_id'],
            'advertiser_name' => $row['advertiser_name'],
            'feed_id' => $row['feed_id'],
            'joined' => $row['membership_status'] === 'active',
            'products_count' => $row['no_of_products'],
            'imported_at' => $row['last_imported'], // fixme: consider timezone
            'region' => $row['primary_region'],
            'language' => (new ISO639)->code1ByLanguage($row['language']),
            'original_data' => $row,
        ];
    }

    /**
     * @inheritDoc
     */
    public function collection(Collection $collection)
    {
        Feed::all()
            ->each(function (Feed $feed) use ($collection) {
                $isEmpty = $collection
                    ->where('feed_id', $feed->feed_id)
                    ->isEmpty();

                if ($isEmpty) {
                    $feed->products()->delete();
                    $feed->delete();
                }
            });
    }
}
