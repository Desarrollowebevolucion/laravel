<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\MenuLangList;

class LocaleController extends Controller
{  
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getLangList()
    {
        return response()->json( MenuLangList::all() );
    }
}
