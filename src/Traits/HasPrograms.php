<?php


namespace SoluzioneSoftware\LaravelAffiliate\Traits;


trait HasPrograms
{
    /**
     * @var string[]|null
     */
    protected $programs = null;

    /**
     * @param string[] $programs an array of program ids
     * @return $this
     */
    public function programs(array $programs)
    {
        $this->programs = $programs;

        return $this;
    }
}
