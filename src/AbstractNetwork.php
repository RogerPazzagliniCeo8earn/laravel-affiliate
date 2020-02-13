<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use SoluzioneSoftware\LaravelAffiliate\Objects\Product;
use SoluzioneSoftware\LaravelAffiliate\Objects\Program;
use SoluzioneSoftware\LaravelAffiliate\Objects\Transaction;

abstract class AbstractNetwork
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @var array
     */
    protected $requestParams = [];

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
     * @return ResponseInterface
     * @throws GuzzleException
     */
    protected function callApi()
    {
        $uri = $this->baseUrl . $this->getEndPoint();
        foreach ($this->getRequest() as $rp){
            $uri.=$rp.$this->getEndPoint();
        }

        $options = [
            'query' => $this->getParams(),
            'headers' => $this->getHeaders(),
        ];

        return $this->client->request('GET', $uri, $options);
    }

    protected function getHeaders()
    {
        return ['Accept' => 'application/json'];
    }

    protected function getParams()
    {
        return $this->queryParams;
    }

    protected function getEndPoint()
    {
        return $this->requestEndPoint;
    }

    protected function getRequest()
    {
        return $this->requestParams;
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
