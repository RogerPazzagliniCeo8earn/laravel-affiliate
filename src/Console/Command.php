<?php

namespace SoluzioneSoftware\LaravelAffiliate\Console;

abstract class Command extends \Illuminate\Console\Command
{
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }
}
