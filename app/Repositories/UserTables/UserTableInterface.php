<?php
namespace App\Repositories\UserTables;

interface UserTableInterface {
    public function get( $sorter, $tableFilter, $columnFilter, $itemsLimit );
    public function getall( $sorter, $tableFilter, $columnFilter, $itemsLimit,$acutal);
    public function getalladmin( $sorter, $tableFilter, $columnFilter, $itemsLimit,$acutal);

    public function getallusers();
    public function create($request);
    public function updateuser($request);
    public function updateuserroles($request);

    public function lockuseradmin($request);
    public function unlockuseradmin($request);

    public function allusersquery();
}