<?php


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
use SoluzioneSoftware\LaravelAffiliate\Objects\Response;
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
    public function getTransactions(?DateTime $startDate = null, ?DateTime $endDate = null)
    {
        try{
            return new Response(true, null, $this->executeTransactionsRequest(null, $startDate, $endDate));
        }catch (Exception|GuzzleException $e){
            return new Response(false, $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     * @see https://wiki.awin.com/index.php/Product_Feeds_for_Publishers
     */
    public function searchProducts(?string $query = null, ?array $advertisers = null, ?array $languages = null, ?int $limit = null, ?string $trackingCode = null)
    {
        $this->trackingCode = $trackingCode;

        $queryBuilder = Product::with('feed');

        if (!is_null($query)){
            $queryBuilder
                ->where(function (Builder $queryBuilder) use ($query) {
                    $queryBuilder
                        ->where('title', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                });
        }

        if (!is_null($advertisers)){
            $queryBuilder
                ->whereExists(function (\Illuminate\Database\Query\Builder $query) use ($advertisers) {
                    $query
                        ->select(DB::raw(1))
                        ->from($this->getFeedsTable())
                        ->whereRaw("{$this->getProductsTable()}.feed_id = {$this->getFeedsTable()}.id")
                        ->whereIn('advertiser_id', $advertisers);
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

        if (!is_null($limit)){
            $queryBuilder->take($limit);
        }

        $products = $queryBuilder->get();

        $collection = $products->map(function (Product $product){
            return $this->productFromJson($product->toArray());
        });

        return new Response(true, null, $collection);
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
    public function executeTransactionsRequest(?array $programs = null, ?DateTime $fromDateTime = null, ?DateTime $toDateTime = null)
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

    private function getFeedsTable()
    {
        return Config::get('affiliate.db.tables.feeds');
    }

    private function getProductsTable()
    {
        return Config::get('affiliate.db.tables.products');
    }
}
