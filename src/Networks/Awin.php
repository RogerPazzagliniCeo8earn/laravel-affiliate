<?php /** @noinspection PhpUndefinedClassInspection */

namespace SoluzioneSoftware\LaravelAffiliate\Networks;

use Carbon\Carbon;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Enums\TransactionStatus;
use SoluzioneSoftware\LaravelAffiliate\Enums\ValueType;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\CommissionRate;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product as ProductObject;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;
use Throwable;

class Awin extends AbstractNetwork implements Network
{
    /**
     * @var string
     */
    protected $baseUrl = 'https://api.awin.com';

    /**
     * @var string
     */
    private $apiToken;

    /**
     * @var string
     */
    private $publisherId;

    /**
     * @var string
     * @link https://wiki.awin.com/index.php/Publisher_Click_Ref
     */
    private $trackingCodeParam;

    const TRANSACTION_STATUS_MAPPING = [
        'approved' => TransactionStatus::CONFIRMED,
        'declined' => TransactionStatus::DECLINED,
        'deleted' => TransactionStatus::DECLINED,
        'pending' => TransactionStatus::PENDING,
    ];

    public function __construct()
    {
        parent::__construct();

        $this->apiToken = Config::get('affiliate.credentials.awin.api_token');
        $this->publisherId = Config::get('affiliate.credentials.awin.publisher_id');
        $this->trackingCodeParam = Config::get('affiliate.networks.awin.tracking_code_param');
    }

    protected function getHeaders()
    {
        return array_merge(parent::getHeaders(), ['Authorization' => 'Bearer ' . $this->apiToken]);
    }

    /**
     * @inheritDoc
     */
    public static function getMaxPerPage(): ?int
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function executeProductsCountRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null
    ): int
    {
        return $this
            ->getProductQueryBuilder($keyword, $programs, $languages)
            ->getQuery() // see: https://stackoverflow.com/a/48624056
            ->getCountForPagination();
    }

    /**
     * @inheritDoc
     */
    public function executeProductsRequest(
        ?array $programs = null,
        ?string $keyword = null,
        ?array $languages = null,
        ?string $trackingCode = null,
        int $page = 1,
        int $perPage = 10
    ): Collection
    {
        $this->trackingCode = $trackingCode;

        $queryBuilder = $this->getProductQueryBuilder($keyword, $programs, $languages)->with('feed');
        if (!is_null($perPage)){
            $queryBuilder->forPage($page, $perPage);
        }

        $products = $queryBuilder->get();

        return $products->map(function (Product $product){
            return $this->productFromJson($product->toArray());
        });
    }

    /**
     * @inheritDoc
     */
    public function executeGetProduct(string $id, ?string $trackingCode = null): ?ProductObject
    {
        $this->trackingCode = $trackingCode;

        $product = Product::with('feed')->where('product_id', $id)->first();
        if (is_null($product)){
            return null;
        }

        return $this->productFromJson($product->toArray());
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     */
    public function executeTransactionsCountRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null
    ): int
    {
        return $this->executeTransactionsRequest($programs, $fromDateTime, $toDateTime)->count();
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     * @throws Exception
     */
    public function executeTransactionsRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null,
        int $page = 1,
        ?int $perPage = null
    ): Collection
    {
        $fromDateTime = is_null($fromDateTime) ? Date::now() : $fromDateTime;
        $toDateTime = is_null($toDateTime) ? Date::now() : $toDateTime;

        $this->requestEndPoint = "/publishers/{$this->publisherId}/transactions/";
        $this->queryParams = [
            'timezone' => 'UTC', // fixme: parametrize it
            'startDate' => $fromDateTime->format('Y-m-d\TH:i:s'),
            'endDate' => $toDateTime->format('Y-m-d\TH:i:s'),
        ];

        if (!is_null($programs)){
            $this->queryParams['advertiserId'] = implode(',', $programs);
        }

        $response = $this->callApi();
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200){
            throw new RuntimeException("Expected response status code 200. Got $statusCode.");
        }

        $transactions = json_decode($response->getBody(), true);
        if ($perPage){
            $chunks = array_chunk($transactions, $perPage);
            $chunk = $chunks[$page - 1] ?? [];
        }
        else{
            $chunk = $transactions;
        }

        return collect($chunk)
            ->map(function (array $transaction) {
                return $this->transactionFromJson($transaction);
            });
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     * @throws Throwable
     */
    public function executeCommissionRatesCountRequest(string $programId): int
    {
        return $this->executeCommissionRatesRequest($programId)->count();
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     * @throws Throwable
     */
    public function executeCommissionRatesRequest(
        string $programId,
        int $page = 1,
        int $perPage = 100
    ): Collection
    {
//        fixme: consider pagination params
        $this->requestEndPoint = "/publishers/{$this->publisherId}/commissiongroups";

        $this->queryParams['advertiserId'] = $programId;

        $response = $this->callApi();
        $statusCode = $response->getStatusCode();
        if ($statusCode !== 200){
            throw new RuntimeException("Expected response status code 200. Got $statusCode.");
        }

        $commissionGroups = new Collection();
        $body = json_decode($response->getBody(), true);
        foreach ((array)$body['commissionGroups'] as $commissionGroup) {
            $commissionGroups->push($this->commissionRateFromJson($programId, $commissionGroup));
        }

        return $commissionGroups;
    }

    /**
     * @inheritDoc
     */
    public function transactionFromJson(array $transaction)
    {
        return new Transaction(
            $transaction['advertiserId'],
            $transaction['id'],
            TransactionStatus::create(static::TRANSACTION_STATUS_MAPPING[$transaction['commissionStatus']]),
            $transaction['paidToPublisher'],
            floatval($transaction['commissionAmount']['amount']),
            $transaction['commissionAmount']['currency'],
            Carbon::parse($transaction['transactionDate']),
            $this->getTrackingCodeFromTransaction($transaction),
            $transaction
        );
    }

    /**
     * @param array $transaction
     * @return string|null
     */
    private function getTrackingCodeFromTransaction(array $transaction)
    {
        return Arr::get($transaction, 'clickRefs.'.$this->trackingCodeParam);
    }

    public function programFromJson(array $program)
    {
        return new Program(
            $this,
            $program['advertiser_id'],
            $program['advertiser_name']
        );
    }

    public function productFromJson(array $product)
    {
        return new ProductObject(
            $this->programFromJson($product['feed']),
            $product['product_id'],
            $product['title'],
            $product['description'],
            $product['image_url'],
            floatval($product['price']),
            $product['currency'],
            $this->getDetailsLink($product),
            $this->getTrackingLink($product),
            $product
        );
    }

    public function commissionRateFromJson(string $programId, array $commissionRate): CommissionRate
    {
        if ($commissionRate['type'] === 'fix'){
            $type = 'fixed';
            $value = $commissionRate['amount'];
        }
        else{
            $type = $commissionRate['type'];
            $value = $commissionRate['percentage'];
        }

        return new CommissionRate(
            $programId,
            $commissionRate['groupId'],
            $commissionRate['groupName'],
            new ValueType($type),
            (float)$value,
            $commissionRate
        );
    }

    protected function getDetailsLink(array $product)
    {
        return $product['details_link'];
    }

    protected function getTrackingLink(array $product)
    {
        return 'https://www.awin1.com/pclick.php'
            ."?p={$product['product_id']}"
            ."&a={$this->publisherId}"
            ."&m={$product['feed']['advertiser_id']}"
            .($this->trackingCode ? '&'.strtolower($this->trackingCodeParam).'='.$this->trackingCode : '');
    }

    /**
     * @param string|null $keyword
     * @param string[]|null $programs
     * @param string[]|null $languages
     * @return Builder
     */
    private function getProductQueryBuilder(?string $keyword = null, ?array $programs = null, ?array $languages = null)
    {
        $queryBuilder = Product::query();

        if (!is_null($keyword)){
            $queryBuilder->whereKey(Product::search($keyword)->keys());
        }

        if (!is_null($programs)){
            $queryBuilder
                ->whereExists(function (\Illuminate\Database\Query\Builder $query) use ($programs) {
                    $query
                        ->select(DB::raw(1))
                        ->from($this->getFeedsTable())
                        ->whereRaw("{$this->getProductsTable()}.feed_id = {$this->getFeedsTable()}.id")
                        ->whereIn('advertiser_id', $programs);
                });
        }

        if (!is_null($languages)){
            $queryBuilder
                ->whereExists(function (\Illuminate\Database\Query\Builder $query) use ($languages) {
                    $query
                        ->select(DB::raw(1))
                        ->from($this->getFeedsTable())
                        ->whereRaw("{$this->getProductsTable()}.feed_id = {$this->getFeedsTable()}.id")
                        ->whereIn('language', $languages);
                });
        }

        return $queryBuilder;
    }

    private function getFeedsTable()
    {
        return Config::get('affiliate.db.tables.feeds');
    }

    private function getProductsTable()
    {
        return Config::get('affiliate.db.tables.products');
    }
}
