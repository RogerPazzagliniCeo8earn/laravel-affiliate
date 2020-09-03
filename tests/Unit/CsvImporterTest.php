<?php

namespace Tests\Unit;

use SoluzioneSoftware\LaravelAffiliate\CsvImporter;
use Tests\TestCase;

class CsvImporterTest extends TestCase
{
    /**
     * @test
     */
    public function invalid_rows_are_skipped()
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $importer = new CsvImporter(__DIR__.'/../Fixtures/CsvImporter/1.csv');
        $rows = $importer->get();
        self::assertCount(1, $rows);
    }
}
