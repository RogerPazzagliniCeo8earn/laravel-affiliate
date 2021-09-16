<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Row;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
use SoluzioneSoftware\LaravelAffiliate\Traits\ResolvesBindings;

class FeedsImport extends AbstractImport implements WithHeadingRow, OnEachRow, ToCollection
{
    use ResolvesBindings;

    /**
     * @inheritDoc
     * @throws BindingResolutionException
     */
    public function onRow(Row $row)
    {
        $row = $row->toArray();
        $data = array_merge(
            $this->mapRow($row),
            [
                'original_data' => $row,
            ]
        );

        static::resolveFeedModelBinding()::query()
            ->updateOrCreate(
                [
                    'network' => $this->network->getKey(),
                    'feed_id' => $data['feed_id'],
                ],
                $data
            );
    }

    public function mapRow(array $row): array
    {
        return $this->network->mapProductFeedRow($row);
    }

    /**
     * @inheritDoc
     * @throws BindingResolutionException
     * @throws Exception
     */
    public function collection(Collection $collection)
    {
        static::resolveFeedModelBinding()::query()
            ->where('network', $this->network->getKey())
            ->get()
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
