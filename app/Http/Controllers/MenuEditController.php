<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\EditMenuViewService;
use App\Services\RolesService;
use App\Models\Menurole;
use App\Models\Menulist;
use App\Models\Menus;

class MenuEditController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('admin');
    }

    public function index(){
        return response()->json( array( 'menulist'  => Menulist::all() ) );
    }

    public function store(Request $request){
        $validatedData = $request->validate([
            'name' => 'required|min:1|max:64'
        ]);
        $menulist = new Menulist();
        $menulist->name = $request->input('name');
        $menulist->save();
        return response()->json( array('success' => true) );
    }

    public function edit(Request $request){
        return response()->json( array('menulist'  => Menulist::where('id', '=', $request->input('id'))->first() ) );
    }

    public function update(Request $request){
        $validatedData = $request->validate([
            'id'   => 'required',
            'name' => 'required|min:1|max:64'
        ]);
        $menulist = Menulist::where('id', '=', $request->input('id'))->first();
        $menulist->name = $request->input('name');
        $menulist->save();
        return response()->json( array('success' => true) );
    }

    public function delete(Request $request){
        $menus = Menus::where('menu_id', '=', $request->input('id'))->first();
        if(!empty($menus)){
            return response()->json( array('success' => false) );
        }else{
            Menulist::where('id', '=', $request->input('id'))->delete();
            return response()->json( array('success' => true) );
        }
    }
}
