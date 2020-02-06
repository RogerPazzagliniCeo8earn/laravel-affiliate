<?php


namespace SoluzioneSoftware\LaravelAffiliate\Networks;


use Exception;
use Illuminate\Support\Facades\Config;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;

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

    public function getTransactions(array $params = [])
    {
        // https://developer.zanox.com/web/guest/publisher-api-2011/get-leads-date

        throw new Exception('Not implemented');
    }

    public function searchProducts(?string $query = null, $languages = null)
    {
        // https://developer.zanox.com/web/guest/publisher-api-2011/get-products

        throw new Exception('Not implemented');
    }

    protected function transactionFromJson(array $transaction)
    {
        throw new Exception('Not implemented');
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
