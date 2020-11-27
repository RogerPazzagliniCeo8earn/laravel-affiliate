<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Networks\Awin;
use SoluzioneSoftware\LaravelAffiliate\Networks\Zanox;
use SoluzioneSoftware\LaravelAffiliate\Paginator;
use SoluzioneSoftware\LaravelAffiliate\Traits\InteractsWithThrowable;

abstract class AbstractRequestBuilder
{
    use InteractsWithThrowable;

    /**
     * @var string[]|null
     */
    protected $networks = null;

    /**
     * @var array
     */
    protected $programs = [];

    /**
     * @var bool
     */
    protected $catchErrors = false;

    /**
     * @param  string[]|null  $networks
     */
    public function __construct(?array $networks = null)
    {
        $this->networks = $networks;
    }

    /**
     * @param  string[]  $networks
     * @return $this
     */
    public function networks(array $networks)
    {
        // fixme: validate $networks param
        $this->networks = $networks;
        return $this;
    }

    /**
     * @param  array  $programs
     * @return $this
     */
    public function programs(array $programs)
    {
        $this->programs = $programs;
        return $this;
    }

    /**
     * @param  bool  $catch
     * @return $this
     */
    public function catchErrors(bool $catch = true)
    {
        $this->catchErrors = $catch;
        return $this;
    }

    /**
     * @param  int  $page
     * @param  int|null  $perPage
     * @return Collection
     * @throws BindingResolutionException
     */
    public function get(int $page = 1, int $perPage = 10)
    {
        if ($this->catchErrors) {
            return $this->attempt(function () use ($page, $perPage) {
                return $this->executeGet($page, $perPage);
            }, collect());
        } else {
            return $this->executeGet($page, $perPage);
        }
    }

    /**
     * @param  int  $page
     * @param  int  $perPage
     * @return Collection
     */
    protected function executeGet(int $page, int $perPage): Collection
    {
        $collection = collect();
        foreach ($this->getNetworks() as $network) {
            $collection = $collection->merge([
                get_class($network) => $this->executeGetForNetwork($network, $page, $perPage)
            ]);
        }
        return $collection;
    }

    /**
     * @return Network[]
     */
    protected function getNetworks()
    {
        $networks = $this->networks ?: $this->getAvailableNetworks();

        return array_map(
            function (string $network) {
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
            Awin::class,
            Zanox::class,
        ];
    }

    /**
     * @param  Network  $network
     * @param  int  $page
     * @param  int  $perPage
     * @return Collection
     */
    abstract protected function executeGetForNetwork(Network $network, int $page, int $perPage);

    /**
     * @param  int  $currentPage  default = 1
     * @param  int  $perPage  default = 10
     * @param  array  $options  (path, query, fragment, pageName) <br>
     * see {@see \Illuminate\Pagination\Paginator::__construct}
     * @return Paginator
     */
    public function paginate(int $currentPage = 1, int $perPage = 10, array $options = []): Paginator
    {
        $collection = collect();

        foreach ($this->getNetworks() as $network) {
            $collection = $collection->merge([
                get_class($network) => $this->executeGetForNetwork($network, $currentPage, $perPage + 1)
            ]);
        }

        return new Paginator($collection, $perPage, $currentPage, $options);
    }

    /**
     * @param  Network  $network
     * @return int
     */
    abstract protected function executeCountForNetwork(Network $network): int;

    /**
     * @param  Network  $network
     * @return array|null
     */
    protected function getPrograms(Network $network): ?array
    {
        return Arr::get($this->programs, get_class($network), null);
    }
}
