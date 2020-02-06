<?php


namespace SoluzioneSoftware\LaravelAffiliate\Objects;


use Illuminate\Support\Collection;

class Response
{
    public $status;

    public $message;

    /**
     * @var Collection|null
     */
    public $collection;

    public function __construct(bool $status, ?string $message = null, ?Collection $collection = null)
    {
        $this->status = $status;
        $this->message = $message;
        $this->collection = $collection;
    }
}
