<?php

namespace SoluzioneSoftware\LaravelAffiliate\Imports;

use Illuminate\Console\OutputStyle;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Collection;
use SoluzioneSoftware\LaravelAffiliate\Models\Feed;

class ProductsImportWithProgress extends ProductsImport
{
    /**
     * @var OutputStyle
     */
    protected $output;

    protected $batch = 1;

    protected $totalBatches = 1;

    /**
     * @param  Feed  $feed
     * @param  OutputStyle  $output
     * @throws BindingResolutionException
     */
    public function __construct(Feed $feed, OutputStyle $output)
    {
        parent::__construct($feed);

        $this->output = $output;
        $chunks = $feed->getProductsCount() / $this->chunkSize();
        $this->totalBatches = intval(floor($chunks) === $chunks ? $chunks : $chunks + 1);
    }

    public function import(string $path)
    {
        $this->output->writeln("Importing file $path");

        parent::import($path);
    }

    public function beforeImport()
    {
        parent::beforeImport();
        $this->output->writeln('Found '.$this->dbProducts->count().' products in DB');
    }

    public function onChunkRead(array $rows)
    {
        $this->output->writeln("Processing batch {$this->batch}/{$this->totalBatches}");
        $this->output->progressStart(count($rows));

        parent::onChunkRead($rows);

        $this->output->progressFinish();

        $this->batch += 1;
    }

    protected function processRow(array $row): ?bool
    {
        $res = parent::processRow($row);
        $this->output->progressAdvance();

        return $res;
    }

    protected function insertProducts(array $products): void
    {
        $this->output->newLine();
        $this->output->writeln('Inserting '.count($products).' products...');
        parent::insertProducts($products);
    }

    protected function updateProducts(array $products): void
    {
        $this->output->writeln('Updating '.count($products).' products...');
        parent::updateProducts($products);
    }

    protected function getRowsToDelete(): Collection
    {
        $res = parent::getRowsToDelete();
        $this->output->writeln('Deleting '.$res->count().' products...');
        return $res;
    }
}
