<?php


namespace SoluzioneSoftware\LaravelAffiliate\Objects;


class Product
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $title;

    /**
     * @var string|null
     */
    public $description;

    /**
     * @var string|null
     */
    public $image;

    /**
     * @var float
     */
    public $price;

    /**
     * @var string
     */
    public $currency;

    /**
     * @var array
     */
    public $original;


    public function __construct(string $id, string $title, ?string $description, ?string $image, float $price, string $currency, array $original)
    {
        $this->id = $id;
        $this->title = $title;
        $this->description = $description;
        $this->image = $image;
        $this->price = $price;
        $this->currency = $currency;
        $this->original = $original;
    }
}
