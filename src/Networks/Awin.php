<?php


namespace SoluzioneSoftware\LaravelAffiliate\Networks;


use Carbon\Carbon;
use DateTime;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;
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
        $status=false;
        $message="";
        $transactions = new Collection();
        try{
            $this->requestParams = ['publishers',$this->publisherId,'transactions'];
            $this->queryParams = [
                'startDate'=>$startDate->format('Y-m-d\TH:i:s'),
                'endDate'=>$endDate->format('Y-m-d\TH:i:s'),
                //   'timezone'=>'UTC',
                //   'status'=>'pending',
            ];

            $response=$this->callApi();
            if ($response->getStatusCode()==200){
                $status = true;
                foreach (json_decode($response->getBody()) as $transaction) {
                    $transactions->push($this->transactionFromJson($transaction));
                }
            }
        }catch (Exception $e){
            $message=$e->getMessage();
        }
        return new Response($status,$message,$transactions);
    }

    /**
     * @inheritDoc
     * @see https://wiki.awin.com/index.php/Product_Feeds_for_Publishers
     */
    public function searchProducts(?string $query = null, ?array $advertisers = null, ?array $languages = null, ?int $limit = null, ?string $trackingCode = null)
    {
        $this->trackingCode = $trackingCode;

        $queryBuilder = Product::query();

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

        $product = Product::query()->where('product_id', $id)->first();
        if (is_null($product)){
            return null;
        }

        return $this->productFromJson($product->toArray());
    }

    protected function transactionFromJson(array $transaction)
    {
        return new Transaction(
            $transaction['id'],
            $transaction['advertiserId'],
            $transaction['commissionStatus'],
            floatval($transaction['commissionAmount']['amount']),
            $transaction['commissionAmount']['currency'], Carbon::parse($transaction['transactionDate']),
            $transaction
        );
    }

    protected function programFromJson(array $program)
    {
        return new Program(
            $program['advertiser_id'],
            $program['advertiser_name']
        );
    }

    protected function productFromJson(array $product)
    {
        return new \SoluzioneSoftware\LaravelAffiliate\Objects\Product(
            $product['id'],
            $product['title'],
            $product['description'],
            $product['image_url'],
            floatval($product['price']),
            $product['currency'],
            $this->getTrackingLink($product),
            $product
        );
    }

    private function getTrackingLink(array $product)
    {
        return 'https://www.awin1.com/pclick.php'
            . "?p={$product['product_id']}"
            . "&a={$this->publisherId}"
            . "&m={$this->getAdvertiserId($product)}"
            . ($this->trackingCode ? '&pref1=' . $this->trackingCode : '');
    }

    /**
     * @param array $product
     * @return int|null
     */
    private function getAdvertiserId(array $product)
    {
        return Feed::query()->where('id', $product['feed_id'])->value('advertiser_id');
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
