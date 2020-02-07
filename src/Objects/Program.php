<?php


namespace SoluzioneSoftware\LaravelAffiliate\Objects;


class Program
{
    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;


    public function __construct(string $id, string $name)
    {
        $this->id = $id;
        $this->name = $name;
    }

    public function toArray()
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
