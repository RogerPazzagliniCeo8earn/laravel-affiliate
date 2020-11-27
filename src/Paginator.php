<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use Illuminate\Pagination\Paginator as IlluminatePaginator;
use Illuminate\Support\Collection;

class Paginator extends IlluminatePaginator
{
    /**
     * @inheritDoc
     *
     * @return int|null
     */
    public function lastItem()
    {
        return null;
    }

    /**
     * @inheritDoc
     *
     * @return int|null
     */
    public function firstItem()
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    protected function setItems($items)
    {
        $this->items = $items instanceof Collection ? $items : Collection::make($items);

        $this->hasMore = false;
        $this->items->each(function (Collection $networkItems) {
            $this->hasMore = $networkItems->count() > $this->perPage;
            return !$this->hasMore;
        });

        $this->items = $this->items->mapWithKeys(function (Collection $networkItems, string $network) {
            return [$network => $networkItems->slice(0, $this->perPage)];
        });
    }
}
