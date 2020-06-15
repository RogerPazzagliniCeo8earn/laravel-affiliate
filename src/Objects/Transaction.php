<?php


namespace SoluzioneSoftware\LaravelAffiliate\Objects;


use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;

class Transaction implements Arrayable
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

    public function toArray()
    {
        return [
            'program_id' => $this->programId,
            'id' => $this->id,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'date_time' => $this->dateTime,
            'tracking_code' => $this->trackingCode,
            'original' => $this->original,
        ];
    }
}
