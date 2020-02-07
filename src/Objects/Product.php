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
    public $name;

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
     * @var string|null
     */
    public $details_link;

    /**
     * @var string|null
     */
    public $tracking_link;

    /**
     * @var array
     */
    public $original;

    public function __construct(string $id, string $name, ?string $description, ?string $image, float $price, string $currency, ?string $link, array $original)
    {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->image = $image;
        $this->price = $price;
        $this->currency = $currency;
        $this->details_link = $details_link;
        $this->tracking_link = $tracking_link;
        $this->original = $original;
    }
}
