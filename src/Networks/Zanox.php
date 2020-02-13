<?php


namespace SoluzioneSoftware\LaravelAffiliate\Networks;


use DateTime;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Response;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;
use Carbon\Carbon;

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

    public function __construct()
    {
        parent::__construct();

        $this->connectId = Config::get('affiliate.credentials.zanox.connect_id');
        $this->secretKey = Config::get('affiliate.credentials.zanox.secret_key');
    }

    protected function getHeaders()
    {
        $time       = $this->assignTimestamp();
        $nonce      = $this->assignNonce();
        $signature  = $this->getSignature($time, $nonce);

        return array_merge(parent::getHeaders(), [
            'Authorization' => sprintf('ZXWS %s:%s', $this->connectId, $signature),
            'Date' => $time,
            'nonce' => $nonce,
        ]);
    }

    /**
     * @inheritDoc
     * @see https://developer.zanox.com/web/guest/publisher-api-2011/get-leads-date
     */
    public function getTransactions(?DateTime $startDate = null, ?DateTime $endDate = null)
    {
        $status=false;
        $message="";
        $transactions = new Collection();
        if ($endDate < $startDate) return new Response($status,'Date End can\'t be less than Date Start' ,$transactions);

        try{
            while ($startDate<=$endDate){
                $this->requestParams = ['reports','sales','date',$startDate->format('Y-m-d')];
                $response=$this->callApi();
                if ($response->getStatusCode()==200){
                    $status = true;
                    $json=json_decode($response->getBody());
                    if ($json->items>0){
                        foreach ($json->saleItems as $saleItem) { //todo: attenzione, c'è anche il nodo saleitems: testare
                            $transactions->push($this->transactionFromJson($saleItem));
                        }

                    }
                }
                $startDate->addDay();
            }
        }catch (Exception $e){
            $message=$e->getMessage();
        }
        return new Response($status,$message,$transactions);
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

    protected function transactionFromJson(array $transaction)
    {
        return new Transaction(
            $transaction['id'],
            $transaction['program']['id'],
            $transaction['reviewState'],
            floatval($transaction['commission']),
            $transaction['currency'],
            Carbon::parse($transaction['trackingDate']),
            $transaction
        );
    }

    protected function programFromJson(array $program)
    {
        return new Program($program['@id'], $program['$']);
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
        return $link ? $link . ($this->trackingCode ? '&zpar0=' . $this->trackingCode : '') : null;
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
