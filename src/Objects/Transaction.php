<?php


namespace SoluzioneSoftware\LaravelAffiliate\Objects;


use Carbon\Carbon;

class Transaction
{
    /**
     * @var string|null
     */
    public $programId;

    /**
     * @var string
     */
    public $id;

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
     * @var string|null
     */
    public $trackingCode;

    /**
     * @var array
     */
    public $original;

    public function __construct(?string $programId,
                                string $id,
                                string $status,
                                float $amount,
                                string $currency,
                                Carbon $dateTime,
                                ?string $trackingCode,
                                array $original)
    {
        $this->programId = $programId;
        $this->id = $id;
        $this->status = $status;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->dateTime = $dateTime;
        $this->trackingCode = $trackingCode;
        $this->original = $original;
    }
}
