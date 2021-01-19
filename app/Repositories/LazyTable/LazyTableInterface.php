<?php
namespace App\Repositories\LazyTable;

interface LazyTableInterface {
    public function get( $sorter, $tableFilter, $columnFilter, $itemsLimit );
}