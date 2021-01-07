<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Container\BindingResolutionException;
use SoluzioneSoftware\LaravelAffiliate\Contracts\Feed;

class ProductsImportWithProgress extends ProductsImport
{
    /**
     * @var OutputStyle
     */
    protected $output;

    /**
     * @param  Feed  $feed
     * @param  OutputStyle  $output
     * @throws BindingResolutionException
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

        $this->output->progressStart($this->feed->getProductsCount());
    }

    public function afterImport()
    {
        parent::afterImport();

        $this->output->progressFinish();
    }
}
