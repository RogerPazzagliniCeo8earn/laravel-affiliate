<?php


namespace SoluzioneSoftware\LaravelAffiliate;


use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
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
     * @var string
     */
    protected $requestEndPoint = '/';

    /**
     * @var Client
     */
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @return ResponseInterface
     */
    protected function callApi()
    {
        $uri = $this->baseUrl . $this->getEndPoint();

        $options = [
            'query' => $this->getParams(),
            'headers' => $this->getHeaders(),
        ];

        return $this->client->get($uri, $options);
    }

    protected function getHeaders()
    {
        return ['Accept' => 'application/json'];
    }

    protected function getParams()
    {
        return $this->requestParams;
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

}
