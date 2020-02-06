<?php


namespace SoluzioneSoftware\LaravelAffiliate\Networks;


use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Models\Product;
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

    public function getTransactions(array $params = [])
    {
        // https://wiki.awin.com/index.php/API_get_transactions_list

        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    public function searchProducts(?string $query = null, $languages = null, ?int $limit = null)
    {
        // https://wiki.awin.com/index.php/Product_Feeds_for_Publishers

        $queryBuilder = Product::query();

        if (!is_null($query)){
            $queryBuilder
                ->where(function (Builder $queryBuilder) use ($query) {
                    $queryBuilder
                        ->where('title', 'like', "%$query%")
                        ->orWhere('description', 'like', "%$query%");
                });
        }

        if (!is_null($languages)){
            $queryBuilder
                ->whereExists(function (\Illuminate\Database\Query\Builder $query) use ($languages) {
                    $feedsTable = Config::get('affiliate.db.tables.feeds');
                    $productsTable = Config::get('affiliate.db.tables.products');
                    $query
                        ->select(DB::raw(1))
                        ->from($feedsTable)
                        ->whereRaw("$productsTable.feed_id = $feedsTable.id")
                        ->whereIn('language', Arr::wrap($languages));
                });
        }

        if (!is_null($limit)){
            $queryBuilder->take($limit);
        }

        $products = $queryBuilder->get();

        return $products->map(function (Product $product){
            return $this->productFromJson($product->toArray());
        });
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

    protected function productFromJson(array $product)
    {
        return new \SoluzioneSoftware\LaravelAffiliate\Objects\Product(
            $product['id'],
            $product['title'],
            $product['description'],
            $product['image_url'],
            floatval($product['price']),
            $product['currency'],
            $product
        );
    }
}
