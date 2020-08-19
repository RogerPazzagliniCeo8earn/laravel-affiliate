<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use Matriphe\ISO639\ISO639;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class FeedsImport implements WithHeadingRow, OnEachRow,  ToCollection
{
    /**
     * @inheritDoc
     */
    public function onRow(Row $row)
    {
        $data = $row->toArray();

        $data['joined'] = $data['membership_status'] === 'active';
        $data['region'] = $data['primary_region'];
        $data['language'] = (new ISO639)->code1ByLanguage($data['language']);
        $data['imported_at'] = $data['last_imported']; // fixme: consider timezone
        $data['products_count'] = $data['no_of_products'];

        Feed::query()->updateOrCreate(Arr::only($data, 'feed_id'), $data);
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function collection(Collection $rows)
    {
        Feed::all()
            ->each(function (Feed $feed) use ($rows) {
                $isEmpty = $rows
                    ->where('feed_id', $feed->feed_id)
                    ->isEmpty();

                if ($isEmpty){
                    $feed->delete();
                }
            });
    }
}
