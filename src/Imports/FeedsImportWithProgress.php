<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use SoluzioneSoftware\LaravelAffiliate\Contracts\NetworkWithProductFeeds;

class FeedsImportWithProgress extends FeedsImport implements WithProgressBar
{
    use Importable;

    /**
     * @var array
     */
    private $products;

    /**
     * @param  NetworkWithProductFeeds  $network
     * @param  OutputStyle  $output
     */
    public function __construct(NetworkWithProductFeeds $network, OutputStyle $output)
    {
        parent::__construct($network);

        $this->withOutput($output);
    }
}
