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
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;

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

    const TRACKING_CODE_PARAM = 'pref1';

    public function __construct()
    {
        parent::__construct();

        $this->apiToken = Config::get('affiliate.credentials.awin.api_token');
        $this->publisherId = Config::get('affiliate.credentials.awin.publisher_id');
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
    )
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
    public function getProduct(string $id, ?string $trackingCode = null)
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
     * @throws Exception
     */
    public function executeTransactionsRequest(
        ?array $programs = null,
        ?DateTime $fromDateTime = null,
        ?DateTime $toDateTime = null,
        int $page = 1,
        int $perPage = 10
    ): Collection
    {
        // fixme: consider $page & $perPage parameters

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

        $transactions = new Collection();
        foreach (json_decode($response->getBody()) as $transaction) {
            $transactions->push($this->transactionFromJson($transaction));
        }
        return $transactions;
    }

    /**
     * @inheritDoc
     */
    protected function transactionFromJson(array $transaction)
    {
        return new Transaction(
            $transaction['advertiserId'],
            $transaction['id'],
            $transaction['commissionStatus'],
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
        return Arr::get($transaction, 'clickRefs.' . static::TRACKING_CODE_PARAM);
    }

    protected function programFromJson(array $program)
    {
        return new Program(
            $this,
            $program['advertiser_id'],
            $program['advertiser_name']
        );
    }

    protected function productFromJson(array $product)
    {
        return new \SoluzioneSoftware\LaravelAffiliate\Objects\Product(
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

    protected function getDetailsLink(array $product)
    {
        return $product['details_link'];
    }

    protected function getTrackingLink(array $product)
    {
        return 'https://www.awin1.com/pclick.php'
            . "?p={$product['product_id']}"
            . "&a={$this->publisherId}"
            . "&m={$product['feed']['advertiser_id']}"
            . ($this->trackingCode ? '&' . static::TRACKING_CODE_PARAM . '=' . $this->trackingCode : '');
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
            $queryBuilder
                ->where(function (Builder $queryBuilder) use ($keyword) {
                    $queryBuilder
                        ->where('title', 'like', "%$keyword%")
                        ->orWhere('description', 'like', "%$keyword%");
                });
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
