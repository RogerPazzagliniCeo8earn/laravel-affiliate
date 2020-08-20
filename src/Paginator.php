<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use ArrayAccess;
use ArrayIterator;
use Countable;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Collection;
use IteratorAggregate;
use JsonSerializable;

class Paginator implements Arrayable, ArrayAccess, Countable, IteratorAggregate, JsonSerializable, Jsonable
{
    /**
     * @var Collection
     */
    protected $items;

    /**
     * @var int
     */
    protected $total;

    /**
     * @var int
     */
    protected $currentPage;

    /**
     * The last available page.
     *
     * @var int
     */
    protected $lastPage;

    /**
     * @var int|null
     */
    protected $perPage = null;

    public function __construct(Collection $items, int $total, int $currentPage, ?int $perPage = null)
    {
        $this->items = $items;
        $this->total = $total;
        $this->currentPage = $currentPage;
        $this->perPage = $perPage;
        $this->lastPage = max((int) ceil($total / $perPage), 1);
    }

    /**
     * @return int
     */
    public function currentPage()
    {
        return $this->currentPage;
    }

    /**
     * @return int
     */
    public function perPage()
    {
        return $this->perPage;
    }

    /**
     * Get the last page.
     *
     * @return int
     */
    public function lastPage()
    {
        return $this->lastPage;
    }

    /**
     * Determine if there are more items in the data source.
     *
     * @return bool
     */
    public function hasMorePages()
    {
        return $this->currentPage() < $this->lastPage();
    }

    /**
     * @return int
     */
    public function total()
    {
        return $this->total;
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        return $this->items;
    }

    /**
     * @param  Collection  $collection
     * @return Paginator
     */
    public function setCollection(Collection $collection): self
    {
        $this->items = $collection;
        return $this;
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'current_page' => $this->currentPage(),
            'per_page' => $this->perPage(),
            'total' => $this->total(),
            'data' => $this->items->toArray(),
        ];
    }

    /**
     * @return array
     */
    public function jsonSerialize()
    {
        return $this->toArray();
    }

    /**
     * @param  int  $options
     * @return string
     */
    public function toJson($options = 0)
    {
        return json_encode($this->jsonSerialize(), $options);
    }

    /**
     * @param  mixed  $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return $this->items->has($offset);
    }

    /**
     * @param  mixed  $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->items->get($offset);
    }

    /**
     * @param  mixed  $offset
     * @param  mixed  $value
     * @return Collection
     */
    public function offsetSet($offset, $value)
    {
        return $this->items->put($offset, $value);
    }

    /**
     * @param  mixed  $offset
     * @return Collection
     */
    public function offsetUnset($offset)
    {
        return $this->items->forget($offset);
    }

    /**
     * @return int
     */
    public function count()
    {
        return $this->items->count();
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator()
    {
        return $this->items->getIterator();
    }
}
