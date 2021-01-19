<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\LazyTable\LazyTableInterface as LazyTableInterface;


class LazyTableController extends Controller
{  
    private $lazyTable;

    public function __construct(LazyTableInterface $lazyTable)
    {
        $this->lazyTable = $lazyTable;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $sorter         = $request->input('sorter');
        $tableFilter    = $request->input('tableFilter');
        $columnFilter   = $request->input('columnFilter');
        $itemsLimit     = $request->input('itemsLimit');
        $notes = $this->lazyTable->get( $sorter, $tableFilter, $columnFilter, $itemsLimit );
        return response()->json( $notes );
    }
}
