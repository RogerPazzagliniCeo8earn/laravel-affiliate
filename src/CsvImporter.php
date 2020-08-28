<?php

namespace SoluzioneSoftware\LaravelAffiliate;

use Exception;

/**
 * taken from https://www.php.net/manual/en/function.fgetcsv.php
 */
class CsvImporter
{
    private $fp;
    private $parse_header;
    private $header;

    /**
     * @var array
     */
    private $options;

    /**
     * @param $fileName
     * @param  bool  $parseHeader
     * @param  mixed  ...$options
     * @throws Exception
     */
    function __construct($fileName, $parseHeader = true, ...$options)
    {
        if (!$handler = fopen($fileName, "r")){
            throw new Exception("Unable to open file: $fileName");
        }

        $this->fp = $handler;
        $this->parse_header = $parseHeader;
        $this->options = $options;

        if ($this->parse_header) {
            $this->header = fgetcsv($this->fp, ...$this->options);
        }
    }

    function __destruct()
    {
        fclose($this->fp);
    }

    //--------------------------------------------------------------------
    function get($max_lines = 0)
    {
        //if $max_lines is set to 0, then get all the data

        $data = array();

        if ($max_lines > 0) {
            $line_count = 0;
        } else {
            $line_count = -1;
        } // so loop limit is ignored

        while ($line_count < $max_lines && ($row = fgetcsv($this->fp, ...$this->options)) !== false) {
            if ($this->parse_header) {
                foreach ($this->header as $i => $heading_i) {
                    $row_new[$heading_i] = $row[$i];
                }
                $data[] = $row_new;
            } else {
                $data[] = $row;
            }

            if ($max_lines > 0) {
                $line_count++;
            }
        }
        return $data;
    }
}
