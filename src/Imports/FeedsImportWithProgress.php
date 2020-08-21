<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Console\OutputStyle;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithProgressBar;

class FeedsImportWithProgress extends FeedsImport implements WithProgressBar
{
    use Importable;

    /**
     * @var array
     */
    private $products;

    /**
     * @param  OutputStyle  $output
     */
    public function __construct(OutputStyle $output)
    {
        $this->withOutput($output);
    }
}
