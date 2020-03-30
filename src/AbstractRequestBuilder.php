<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Networks\Amazon;
use SoluzioneSoftware\LaravelAffiliate\Networks\Awin;
use SoluzioneSoftware\LaravelAffiliate\Networks\Zanox;

abstract class AbstractRequestBuilder
{
    /**
     * @var string[]|null
     */
    private $networks = null;

    /**
     * @var string[]|null
     */
    protected $programs = null;

    /**
     * @param string[]|null $networks
     */
    public function __construct(?array $networks = null)
    {
        $this->networks = $networks;
    }

    /**
     * @param string[] $networks
     * @return $this
     */
    public function networks(array $networks)
    {
        // fixme: validate $networks param
        $this->networks = $networks;
        return $this;
    }

    /**
     * @param string[] $programs
     * @return $this
     */
    public function programs(array $programs)
    {
        // fixme: validate $programs param
        $this->programs = $programs;
        return $this;
    }

    /**
     * @return Collection
     */
    abstract public function get();

    /**
     * @return Network[]
     */
    protected function getNetworks()
    {
        $networks = $this->networks ?: $this->getAvailableNetworks();

        return array_map(
            function (string $network){
                return new $network;
            },
            $networks
        );
    }

    /**
     * @return string[]
     */
    private function getAvailableNetworks()
    {
        // todo: scan Networks directory
        return [
            Amazon::class,
            Awin::class,
            Zanox::class,
        ];
    }
}
