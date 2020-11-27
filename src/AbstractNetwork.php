<?php /** @noinspection PhpUndefinedClassInspection */

namespace SoluzioneSoftware\LaravelAffiliate;

use DateTime;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Objects\CommissionRate;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkProductsRequestBuilder;
use SoluzioneSoftware\LaravelAffiliate\Requests\NetworkTransactionsRequestBuilder;

abstract class AbstractNetwork implements Network
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $queryParams = [];

    /**
     * @var string
     */
    protected $requestEndPoint = '/';

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string|null
     */
    protected $trackingCode;

    /**
     * @throws BindingResolutionException
     */
    public function __construct()
    {
        $this->client = Container::getInstance()->make('affiliate.client');
    }

    /**
     * @inheritDoc
     */
    public static function products(): NetworkProductsRequestBuilder
    {
        return new NetworkProductsRequestBuilder(new static());
    }

    /**
     * @inheritDoc
     */
    abstract public static function getMaxPerPage(): ?int;

    /**
     * @inheritDoc
     */
    public static function getProduct(string $id, ?string $trackingCode = null): ?Product
    {
        return (new static())->executeGetProduct($id, $trackingCode);
    }

    /**
     * @inheritDoc
     */
    abstract public function executeGetProduct(string $id, ?string $trackingCode = null): ?Product;

    /**
     * @inheritDoc
     */
    public static function transactions()
    {
        return new NetworkTransactionsRequestBuilder(new static());
    }

    /**
     * @inheritDoc
     */
    abstract public function executeProductsRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null,
        ?string $trackingCode = null,
        int $page = 1,
        int $perPage = 10
    ): Collection;

    /**
     * @inheritDoc
     */
    abstract public function executeTransactionsRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null,
        int $page = 1,
        ?int $perPage = null
    ): Collection;

    /**
     * @inheritDoc
     */
    abstract public function executeTransactionsCountRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null
    ): int;

    /**
     * @inheritDoc
     */
    abstract public function executeCommissionRatesCountRequest(string $programId): int;

    /**
     * @inheritDoc
     */
    abstract public function executeCommissionRatesRequest(
        string $programId,
        int $page = 1,
        int $perPage = 100
    ): Collection;

    /**
     * @param  array  $transaction
     * @return Transaction
     */
    abstract public function transactionFromJson(array $transaction);

    /**
     * @param  array  $program
     * @return Program|null
     */
    abstract public function programFromJson(array $program);

    /**
     * @param  array  $product
     * @return Product
     */
    abstract public function productFromJson(array $product);

    abstract public function commissionRateFromJson(string $programId, array $commissionRate): CommissionRate;

    /**
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function callApi()
    {
        $uri = $this->baseUrl.$this->getEndPoint();

        $options = [
            'query' => $this->getQueryParams(),
            'headers' => $this->getHeaders(),
        ];

        return $this->client->request('GET', $uri, $options);
    }

    protected function getEndPoint()
    {
        return $this->requestEndPoint;
    }

    protected function getQueryParams()
    {
        return $this->queryParams;
    }

    protected function getHeaders()
    {
        return ['Accept' => 'application/json'];
    }

    /**
     * @param  array  $product
     * @return string|null
     */
    abstract protected function getDetailsLink(array $product);

    /**
     * @param  array  $product
     * @return string|null
     */
    abstract protected function getTrackingLink(array $product);

}
