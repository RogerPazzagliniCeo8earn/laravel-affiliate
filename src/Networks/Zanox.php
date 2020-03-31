<?php


namespace SoluzioneSoftware\LaravelAffiliate\Networks;


use Carbon\Carbon;
use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Date;
use RuntimeException;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Response;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;

class Zanox extends AbstractNetwork implements Network
{
    /**
     * @var string
     */
    protected $baseUrl = 'https://api.zanox.com/json/2011-03-01';

    /**
     * @var string
     */
    private $connectId;

    /**
     * @var string
     */
    private $secretKey;

    const TRACKING_CODE_PARAM = 'zpar0';

    public function __construct()
    {
        parent::__construct();

        $this->connectId = Config::get('affiliate.credentials.zanox.connect_id');
        $this->secretKey = Config::get('affiliate.credentials.zanox.secret_key');
    }

    /**
     * @inheritDoc
     * @see https://developer.zanox.com/web/guest/publisher-api-2011/get-leads-date
     */
    public function getTransactions(?DateTime $startDate = null, ?DateTime $endDate = null)
    {
        if ($endDate < $startDate){
            return new Response(false, 'Date End can\'t be less than Date Start');
        }

        try{
            return new Response(true, null, $this->executeTransactionsRequest(null, $startDate, $endDate));
        }catch (GuzzleException|Exception $e){
            return new Response(false, $e->getMessage());
        }
    }

    /**
     * @inheritDoc
     * @throws GuzzleException
     * @throws Exception
     * @throws RuntimeException
     * @see https://developer.zanox.com/web/guest/publisher-api-2011/get-leads-date
     */
    public function executeTransactionsRequest(?array $programs = null, ?DateTime $fromDateTime = null, ?DateTime $toDateTime = null)
    {
        $leads = $this->executeReportsRequest('leads', $programs, $fromDateTime, $toDateTime);
        $sales = $this->executeReportsRequest('sales', $programs, $fromDateTime, $toDateTime);
        return $leads->merge($sales);
    }

    /**
     * @param string $type
     * @param array|null $programs
     * @param DateTime|null $fromDateTime
     * @param DateTime|null $toDateTime
     * @return Collection
     * @throws GuzzleException
     * @throws Exception
     * @see https://developer.zanox.com/web/guest/publisher-api-2011/get-leads-date
     * @see https://developer.zanox.com/web/guest/publisher-api-2011/get-sales-date
     */
    public function executeReportsRequest(string $type, ?array $programs = null, ?DateTime $fromDateTime = null, ?DateTime $toDateTime = null)
    {
        $fromDateTime = (is_null($fromDateTime) ? Date::now() : new Carbon($fromDateTime))->startOfDay();
        $toDateTime = (is_null($toDateTime) ? Date::now() : new Carbon($toDateTime))->startOfDay();

        $this->queryParams = [];
        if (!is_null($programs)){
            $this->queryParams['programs'] = implode(',', $programs);
        }

        $transactions = new Collection();
        while ($fromDateTime->lessThanOrEqualTo($toDateTime)){
            $this->requestEndPoint = "/reports/{$type}/date/{$fromDateTime->format('Y-m-d')}";
            $response = $this->callApi();
            $statusCode = $response->getStatusCode();
            if ($statusCode !== 200){
                throw new RuntimeException("Expected response status code 200. Got $statusCode.");
            }

            $json = json_decode($response->getBody(), true);
            foreach ($json["{$type}Items"] as $item) {
                $transactions->push($this->transactionFromJson($item));
            }

            $fromDateTime->addDay();
        }
        return $transactions;
    }

    /**
     * @inheritDoc
     * @see https://developer.zanox.com/web/guest/publisher-api-2011/get-products
     */
    public function searchProducts(?string $query = null, ?array $advertisers = null, ?array $languages = null, ?int $limit = null, ?string $trackingCode = null)
    {
        // fixme: consider $languages(region for zanox??)

        $this->trackingCode = $trackingCode;

        $this->requestParams = ['products'];
        if (!is_null($query)) $this->queryParams['q'] = $query;
        if (!is_null($limit)) $this->queryParams['items'] = $limit;
        if (!is_null($advertisers)) $this->queryParams['programs'] = implode(',', $advertisers);

        try{
            $response = $this->callApi();
        }
        catch (GuzzleException $e){
            return new Response(false, $e->getMessage());
        }

        if ($response->getStatusCode() !== 200){
            return new Response(false, $response->getReasonPhrase());
        }

        $json = json_decode($response->getBody(), true);
        $products = new Collection();
        if ($json['items'] > 0){
            foreach ($json['productItems']['productItem'] as $productItem) {
                $products->push($this->productFromJson($productItem));
            }
        }

        return new Response(true, null, $products);
    }

    /**
     * @inheritDoc
     * @see https://developer.zanox.com/web/guest/publisher-api-2011/get-products-product
     */
    public function getProduct(string $id, ?string $trackingCode = null)
    {
        $this->trackingCode = $trackingCode;

        throw new Exception('Not implemented');
    }

    /**
     * @inheritDoc
     */
    protected function transactionFromJson(array $transaction)
    {
        return new Transaction(
            $transaction['program']['id'],
            $transaction['id'],
            $transaction['reviewState'],
            floatval($transaction['commission']),
            $transaction['currency'],
            Carbon::parse($transaction['trackingDate']),
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
        $trackingCode = null;
        array_map(
            function($value) use (&$trackingCode) {
                if ($value['@id'] === static::TRACKING_CODE_PARAM){
                    $trackingCode = $value['$'];
                }
            },
            (array)Arr::get($transaction, 'gpps', [])
        );

       return $trackingCode;
    }

    protected function programFromJson(array $program)
    {
        return new Program($this, $program['@id'], $program['$']);
    }

    protected function productFromJson(array $product)
    {
        return new Product(
            $this->programFromJson($product['program']),
            $product['@id'],
            $product['name'],
            $product['description'],
            Arr::get($product, 'image.large'),
            floatval($product['price']),
            $product['currency'],
            $this->getDetailsLink($product),
            $this->getTrackingLink($product),
            $product
        );
    }

    protected function getDetailsLink(array $product)
    {
        return Arr::get($product, 'trackingLinks.trackingLink.0.ppc');
    }

    protected function getTrackingLink(array $product)
    {
        $link = $this->getDetailsLink($product);
        return $link ? $link . ($this->trackingCode ? '&' . static::TRACKING_CODE_PARAM . '=' . $this->trackingCode : '') : null;
    }

    protected function getHeaders()
    {
        $time      = $this->assignTimestamp();
        $nonce     = $this->assignNonce();
        $signature = $this->getSignature($time, $nonce);

        return array_merge(parent::getHeaders(), [
            'Authorization' => sprintf('ZXWS %s:%s', $this->connectId, $signature),
            'Date' => $time,
            'nonce' => $nonce,
        ]);
    }

    /**
     * Returns a HMAC based signature
     *
     * @param string $timestamp Timestamp - in GMT, format "EEE, dd MMM yyyy HH:mm:ss"
     * @param string $nonce unique random string, generated at the time of request, valid once, 20 or more
     * @return string
     */
    private function getSignature($timestamp, $nonce)
    {
        $sign = 'GET' . $this->requestEndPoint . $timestamp . $nonce; // fixme:
        return $this->hmac($sign);
    }

    private function hmac($string)
    {
        $hmac = hash_hmac('sha1', utf8_encode($string), $this->secretKey);
        return $this->encodeBase64($hmac);
    }

    private function encodeBase64( $string )
    {
        $encode = '';

        for ($i=0; $i < strlen($string); $i+=2)
        {
            $encode .= chr(hexdec(substr($string, $i, 2)));
        }

        return base64_encode($encode);
    }

    private function assignTimestamp()
    {
        return gmdate('D, d M Y H:i:s T');
    }

    private function assignNonce()
    {
        return md5(microtime() . mt_rand());
    }
}
