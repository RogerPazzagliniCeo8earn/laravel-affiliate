<?php


namespace SoluzioneSoftware\LaravelAffiliate\Objects;


use Illuminate\Contracts\Support\Arrayable;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

class Program implements Arrayable
{
    /**
     * @var Network
     */
    public $network;

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    public function __construct(Network $network, string $id, string $name)
    {
        $this->network = $network;
        $this->id = $id;
        $this->name = $name;
    }

    public function toArray()
    {
        return [
            'network' => get_class($this->network),
            'id' => $this->id,
            'name' => $this->name,
        ];
    }
}
