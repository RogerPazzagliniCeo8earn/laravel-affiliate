<?php

namespace SoluzioneSoftware\LaravelAffiliate\Traits;

use Illuminate\Console\OutputStyle;
use Symfony\Component\Console\Helper\ProgressBar;

trait InteractsWithConsoleOutput
{
    /**
     * @var OutputStyle|null
     */
    protected $output;

    /**
     * @var ProgressBar|null
     */
    protected $progressBar;

    /**
     * @param  string  $string
     * @param  string|null  $style options: info, comment, question, error, warning, alert
     */
    protected function writeLine(string $string, ?string $style = null)
    {
        if ($this->output){
            $styled = $style ? "<$style>$string</$style>" : $string;

            $this->output->writeln($styled);
        }
    }

    /**
     * @param int $max
     *
     * @return ProgressBar|null
     */
    protected function createProgressBar(int $max = 0)
    {
        return $this->output ? $this->output->createProgressBar($max) : null;
    }

    /**
     * @param int $max
     */
    protected function progressStart(int $max = 0)
    {
        $this->progressBar = $this->createProgressBar($max);
        $this->callMethod('start', $this->progressBar);
    }

    protected function progressFinish()
    {
        $this->callMethod('finish', $this->getProgressBar());
        $this->callMethod('newLine', $this->output, 2);
        $this->progressBar = null;
    }

    protected function getProgressBar(): ?ProgressBar
    {
        return $this->progressBar ?? $this->createProgressBar();
    }

    /**
     * @param  string  $method
     * @param  mixed|null  $object
     * @param  mixed  ...$params
     * @return mixed|null
     */
    protected function callMethod(string $method, $object = null, ...$params)
    {
        return $object ? $object->{$method}(...$params) : null;
    }
}
