<?php

namespace SoluzioneSoftware\LaravelAffiliate\Requests;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Networks\Amazon;
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
        if ($this->catchErrors){
            return $this->attempt(function () use ($page, $perPage) {return $this->executeGet($page, $perPage);}, collect());
        }
        else{
            return $this->executeGet($page, $perPage);
        }
    }

    /**
     * @param  int  $page
     * @param  int  $perPage
     * @return Paginator
     * @throws BindingResolutionException
     */
    public function paginate(int $page = 1, int $perPage = 10): Paginator
    {
        $networks = [];

        foreach ($this->getNetworks() as $network) {
            $networks[get_class($network)] = $network;
        }

        $networksCounts = [];
        foreach ($networks as $networkClass => $network) {
            if ($this->catchErrors){
                $count = $this
                    ->attempt(function () use ($network, &$networksCounts) {
                        return $this->executeCountForNetwork($network);
                    }, 0);
            }
            else{
                $count = $this->executeCountForNetwork($network);
            }
            $networksCounts[$networkClass] = $count;
        }

        $combined = $this->combine($networksCounts);

        $slice = array_slice($combined, ($page - 1) * $perPage, $perPage);

        // determine from-to range for each network
        $fromToRanges = [];
        foreach ($networksCounts as $networkClass => $count) {
            $networkSlice = Arr::where($slice, function (array $item) use ($networkClass) {
                return $item['network'] === $networkClass;
            });
            if (count($networkSlice)){
                $indexes = Arr::pluck($networkSlice, 'index');
                $fromToRanges[$networkClass] = [
                    'from' => min($indexes),
                    'to' => max($indexes),
                ];
            }
        }

        $chunks = [];

        /** @var Network|string $networkClass */
        foreach ($fromToRanges as $networkClass => $fromTo) {
            $chunks[$networkClass] = static::chunk($fromTo['from'], $fromTo['to'], $networkClass::getMaxPerPage());
        }

        $collection = collect();

        foreach ($chunks as $networkClass => $value) {
            foreach ($value as $chunk) {
                $network = $networks[$networkClass];
                $chunkPage = $chunk['page'];
                $chunkPerPage = $chunk['perPage'];
                if ($this->catchErrors){
                    $this
                        ->attempt(function () use (&$collection, $network, $chunkPage, $chunkPerPage) {
                            $collection = $collection->merge($this->executeGetForNetwork($network, $chunkPage, $chunkPerPage));
                        });
                }
                else{
                    $collection = $collection->merge($this->executeGetForNetwork($network, $chunkPage, $chunkPerPage));
                }
            }
        }

        return new Paginator($collection, count($combined), $page, $perPage);
    }

    protected static function chunk(int $from, int $to, ?int $maxPerPage = null): array
    {
        $chunks = [];

        $processed = $from - 1;

        while ($processed < $to){
            $perPage = 1;
            $i = 1;
            while (
                ($from <= 1 || $i <= $processed)
                && ($to >= $i + $processed)
                && (is_null($maxPerPage) || $i <= $maxPerPage)
            ){
                if ($processed % $i === 0){
                    $perPage = $i;
                }

                $i++;
            }

            $chunks[] = ['page' => $processed / $perPage + 1, 'perPage' => $perPage];
            $processed += $perPage;
        }

        return $chunks;
    }

    protected function combine(array $networks)
    {
        $combined = [];
        $indexes = [];
        foreach ($networks as $network => $count) {
            $indexes[$network] = 1;
        }

        while (count($networks)){
            foreach ($networks as $network => $count) {
                $index = $indexes[$network];
                if ($index > $count){
                    unset($networks[$network]);
                    break;
                }

                $combined[] = [
                    'network' => $network,
                    'index' => $index,
                ];

                $indexes[$network] += 1;
            }
        }

        return $combined;
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
            $collection = $collection->merge($this->executeGetForNetwork($network, $page, $perPage));
        }
        return $collection;
    }

    /**
     * @param  Network  $network
     * @param  int  $page
     * @param  int  $perPage
     * @return Collection
     */
    abstract protected function executeGetForNetwork(Network $network, int $page, int $perPage);

    /**
     * @return int
     * @throws BindingResolutionException
     */
    protected function count(): int
    {
        if ($this->catchErrors){
            return $this->attempt(function (){return $this->executeCount();}, 0);
        }
        else{
            return $this->executeCount();
        }
    }

    /**
     * @param  Network  $network
     * @return int
     * @throws BindingResolutionException
     */
    protected function countForNetwork(Network $network): int
    {
        if ($this->catchErrors){
            return $this->attempt(function () use ($network) {return $this->executeCountForNetwork($network);}, 0);
        }
        else{
            return $this->executeCountForNetwork($network);
        }
    }

    /**
     * @return int
     */
    protected function executeCount(): int
    {
        $count = 0;
        foreach ($this->getNetworks() as $network) {
            $count += $this->executeCountForNetwork($network);
        }
        return $count;
    }

    /**
     * @param  Network  $network
     * @return int
     */
    abstract protected function executeCountForNetwork(Network $network): int;

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
     * @param  Network  $network
     * @return array|null
     */
    protected function getPrograms(Network $network): ?array
    {
        return Arr::get($this->programs, get_class($network), null);
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
