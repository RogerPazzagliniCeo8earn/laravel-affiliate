<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithProgressBar;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class ProductsImportWithProgress extends ProductsImport implements WithProgressBar
{
    use Importable;

    /**
     * @var array
     */
    private $products;

    /**
     * @param  Feed  $feed
     * @param  OutputStyle  $output
     */
    public function __construct(Feed $feed, OutputStyle $output)
    {
        parent::__construct($feed);
        $this->withOutput($output);
    }
}
