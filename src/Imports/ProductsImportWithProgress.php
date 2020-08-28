<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Console\OutputStyle;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class ProductsImportWithProgress extends ProductsImport
{
    /**
     * @var OutputStyle
     */
    protected $output;

    /**
     * @param  Feed  $feed
     * @param  OutputStyle  $output
     */
    public function __construct(Feed $feed, OutputStyle $output)
    {
        parent::__construct($feed);
        $this->output = $output;
    }

    public function onChunkRead(array $rows)
    {
        parent::onChunkRead($rows);

        $this->output->progressAdvance(count($rows));
    }

    public function beforeImport()
    {
        parent::beforeImport();

        $this->output->progressStart();
    }

    public function afterImport()
    {
        parent::afterImport();

        $this->output->progressFinish();
    }
}
