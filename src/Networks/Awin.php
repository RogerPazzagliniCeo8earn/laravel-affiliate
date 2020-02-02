<?php


namespace SoluzioneSoftware\LaravelAffiliate\Networks;


use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Config;
use SoluzioneSoftware\LaravelAffiliate\AbstractNetwork;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Network;
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

    public function getProducts(array $params = [])
    {
        // https://wiki.awin.com/index.php/Product_Feeds_for_Publishers

        throw new Exception('Not implemented');
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
}
