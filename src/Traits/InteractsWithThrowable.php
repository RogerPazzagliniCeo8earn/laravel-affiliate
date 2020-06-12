<?php


namespace SoluzioneSoftware\LaravelAffiliate\Traits;


use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Debug\ExceptionHandler;

trait InteractsWithThrowable
{
    /**
     * @param  Exception  $e
     * @throws BindingResolutionException
     */
    protected function reportException(Exception $e)
    {
        /** @var ExceptionHandler $handler */
        $handler = Container::getInstance()->make(ExceptionHandler::class);
        $handler->report($e);
    }

    /**
     * @param  callable  $callback
     * @param  mixed  $default
     * @param  bool  $report
     * @return mixed
     * @throws BindingResolutionException
     */
    protected function attempt(callable $callback, $default = null, $report = true)
    {
        try {
            return $callback();
        } catch (Exception $e) {
            if ($report) {
                $this->reportException($e);
            }

            return value($default);
        }
    }
}
