<?php


namespace SoluzioneSoftware\LaravelAffiliate\Objects;


use Carbon\Carbon;

class Transaction
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $advertiserId;

    /**
     * @var string
     */
    public $status; // fixme: use enums

    /**
     * @var float
     */
    public $amount;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var Carbon
     */
    public $dateTime;

    /**
     * @var array
     */
    public $original;

    public function __construct(string $id, string $advertiserId, string $status, float $amount, string $currency, Carbon $dateTime, array $original)
    {
        $this->id = $id;
        $this->advertiserId = $advertiserId;
        $this->status = $status;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->dateTime = $dateTime;
        $this->original = $original;
    }
}
