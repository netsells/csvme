<?php

namespace Netsells\Csvme;

use Closure;
use League\Csv\Writer;
use SplTempFileObject;

class Csvme
{
    /**
     * @var Writer
     */
    protected $csv;

    /**
     * @var Closure
     */
    protected $layout;

    protected $items = [];
    protected $headers = [];
    protected $name;

    /**
     * CSVMe constructor.
     */
    public function __construct()
    {
        $this->csv = Writer::createFromFileObject(new SplTempFileObject());
    }

    /**
     * @param array $headers
     * @return $this
     */
    public function withHeader(array $headers)
    {
        $this->headers = $headers;

        return $this;
    }

    /**
     * @param array $items
     * @return $this
     */
    public function withItems(array $items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @param Closure $layout
     * @return $this
     */
    public function withLayout(Closure $layout)
    {
        $this->layout = $layout;

        return $this;
    }

    /**
     * Output the CSV to the browser
     *
     * @param CsvComposer|null $composer
     */
    public function output(CsvComposer $composer = null, bool $addCorsHeader = null)
    {
        if ($composer) {
            $composer->compose($this);
        }

        // Cors header is required when requesting export from a different server
        if($addCorsHeader) {
            header('Access-Control-Allow-Origin: *');
        }

        // Process the provided headers and items before outputting
        $this->process();

        // Output the CSV, this method will also set the appropriate content-type headers
        $this->getCsv()->output($this->fileName());

        // To be extra safe, we'll exit.
        exit();
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return Writer
     */
    public function getCsv()
    {
        return $this->csv;
    }

    /**
     * @param mixed $csv
     * @return $this
     */
    public function setCsv($csv)
    {
        $this->csv = $csv;

        return $this;
    }

    /**
     * @return string
     */
    private function fileName()
    {
        if (is_null($this->name)) {
            $this->name = $this->discoverName();
        }

        return $this->name . '-' . date('Y-m-d_H:i:s') . '.csv';
    }

    /**
     * Attempt to name the CSV
     *
     * @param string $fallbackName
     * @return string
     */
    private function discoverName($fallbackName = 'data-export')
    {
        // We'll try and get this from the data
        if (count($this->items)) {

            // Grab the first item
            reset($this->items);
            $firstItem = current($this->items);

            if (is_object($firstItem)) {
                // We have an object, grab the class name
                return strtolower(basename(str_replace('\\', '/', get_class($firstItem))));
            }
        }

        return $fallbackName;
    }

    /**
     * Add the headers and items to the CSV writer object
     */
    public function process()
    {
        if (count($this->headers)) {
            $this->getCsv()->insertOne($this->headers);
        }

        // Have to pop this in a temp variable else PHP thinks
        // we are trying to call an instance method
        $layoutClosure = $this->layout;

        // For each item, we'll pass into the layout closure for formatting
        foreach ($this->items as $item) {
            $this->getCsv()->insertOne($layoutClosure($item));
        }
    }
}
