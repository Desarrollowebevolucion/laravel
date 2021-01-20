<?php

namespace App\Http\Controllers;

use App;
use Illuminate\Http\Request;
use App\Http\Menus\GetSidebarMenu;
use Tymon\JWTAuth\Contracts\Providers\Auth;

class MenuController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        App::setLocale($request->locale);
        try {
           $user = auth()->user();//get user auth
            if($user && !empty($user)){
                $roles =  $user->menuroles;
            }else{
                $roles = '';
            }
        } catch (Exception $e) {
            $roles = '';
        }  
        if($request->has('menu')){/// pregunta si tiene un parametro la peticion request para mandar a traer el menu, no la tiene
            $menuName = $request->input('menu');
        }else{
            $menuName = 'sidebar menu';
        } 
        $menus = new GetSidebarMenu();
       return response()->json( $menus->get( $roles, App::getLocale(), $menuName));
    // return $menuName;
    }

}

