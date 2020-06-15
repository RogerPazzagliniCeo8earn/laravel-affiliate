<?php /** @noinspection PhpUndefinedClassInspection */


namespace SoluzioneSoftware\LaravelAffiliate;


use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Collection;
use Psr\Http\Message\ResponseInterface;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;

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
     * @var Client
     */
    protected $client;

    /**
     * @var string|null
     */
    protected $trackingCode;

    public function __construct()
    {
        $this->client = new Client();
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
    public static function getProduct(string $id, ?string $trackingCode = null): ?Product
    {
        return (new static())->executeGetProduct($id, $trackingCode);
    }

    /**
     * @inheritDoc
     */
    public function transactions()
    {
        return new NetworkTransactionsRequestBuilder($this);
    }

    /**
     * @inheritDoc
     */
    abstract public function executeGetProduct(string $id, ?string $trackingCode = null): ?Product;

    /**
     * @inheritDoc
     */
    abstract public function executeTransactionsRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null,
        int $page = 1,
        int $perPage = 10
    ): Collection;

    /**
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function callApi()
    {
        $uri = $this->baseUrl . $this->getEndPoint();

        $options = [
            'query' => $this->getQueryParams(),
            'headers' => $this->getHeaders(),
        ];

        return $this->client->request('GET', $uri, $options);
    }

    protected function getHeaders()
    {
        return ['Accept' => 'application/json'];
    }

    protected function getQueryParams()
    {
        return $this->queryParams;
    }

    protected function getEndPoint()
    {
        return $this->requestEndPoint;
    }

    /**
     * @param array $transaction
     * @return Transaction
     */
    abstract protected function transactionFromJson(array $transaction);

    /**
     * @param array $program
     * @return Program|null
     */
    abstract protected function programFromJson(array $program);

    /**
     * @param array $product
     * @return Product
     */
    abstract protected function productFromJson(array $product);

    /**
     * @param array $product
     * @return string|null
     */
    abstract protected function getDetailsLink(array $product);

    /**
     * @param array $product
     * @return string|null
     */
    abstract protected function getTrackingLink(array $product);

}
