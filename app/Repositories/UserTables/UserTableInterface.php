<?php
namespace App\Repositories\UserTables;

interface UserTableInterface {
    public function get( $sorter, $tableFilter, $columnFilter, $itemsLimit );
    public function getall( $sorter, $tableFilter, $columnFilter, $itemsLimit,$acutal);
    public function allusersquery();
}