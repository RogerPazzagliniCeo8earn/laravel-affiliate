<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use Exception;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Networks\Amazon;
use SoluzioneSoftware\LaravelAffiliate\Networks\Awin;
use SoluzioneSoftware\LaravelAffiliate\Networks\Zanox;
use SoluzioneSoftware\LaravelAffiliate\Traits\InteractsWithThrowable;

abstract class AbstractRequestBuilder
{
    use InteractsWithThrowable;

    /**
     * @var string[]|null
     */
    protected $networks = null;

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
     * @throws Exception
     */
    public function paginate(int $page = 1, int $perPage = 10): Paginator
    {
        $networks = [];

        foreach ($this->getNetworks() as $network) {
            $networks[get_class($network)] = $this->executeCountForNetwork($network);
        }

        $combined = $this->combine($networks);

        $slice = array_slice($combined, ($page - 1) * $perPage, $perPage);

        // determine from-to range for each network
        $fromTo = [];
        foreach ($networks as $network => $count) {
            $networkSlice = Arr::where($slice, function (array $item) use ($network) {
                return $item['network'] === $network;
            });
            if (count($networkSlice)){
                $indexes = Arr::pluck($networkSlice, 'index');
                $fromTo[$network] = [
                    'from' => min($indexes),
                    'to' => max($indexes),
                ];
            }
        }

        //

        throw new Exception('Not implemented');
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
    abstract protected function executeGet(int $page, int $perPage);

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
    abstract protected function executeCount(): int;

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
