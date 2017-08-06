# Csvme

[![Latest Version](https://img.shields.io/github/release/netsells/csvme.svg?style=flat-square)](https://github.com/netsells/csvme/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/netsells/csvme.svg?style=flat-square)](https://packagist.org/packages/netsells/csvme)

Csvme is an opinionated library that utilises the `league/csv` library.

## Install

Install `Csvme` using Composer.

```
$ composer require netsells/csvme
```

## Usage

### Basic Usage
Csvme always expects an array of objects and optionally a layout closure for the header row.

```php
$csv = new Csvme();

$csv->withHeader(['ID', 'Total', 'Number of Items', 'Created At'])
    ->withLayout(function(Order $order) {
        return [
            $order->id,
            $order->total,
            $order->items->count(),
            $order->created_at->format('d-m-Y'),
        ];
    })
    ->withItems($orders)
    ->output();
``` 

### CSV Composers

It is possible to use an external class to offload the layout of the CSV to a dedicated file.

```php
$csv = new Csvme();
$csv->output(new OrderExportComposer($orders));
```


```php
<?php

use Netsells\Csvme\Csvme;
use Netsells\Csvme\CsvComposer;

class OrderExportComposer implements CsvComposer
{
    /**
     * The orders.
     *
     * @var array
     */
    protected $orders;

    /**
     * Create a new csv composer.
     *
     * @param  array  $orders
     * @return void
     */
    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    /**
     * Configure the CSV
     *
     * @param  Csvme  $csv
     * @return void
     */
    public function compose(Csvme $csv)
    {
        $csv->withHeader(['ID', 'Total', 'Number of Items', 'Created At'])
            ->withLayout(function(Order $order) {
                return [
                    $order->id,
                    $order->total,
                    $order->items->count(),
                    $order->created_at->format('d-m-Y'),
                ];
            })
            ->withItems($this->orders);
    }

}
```
