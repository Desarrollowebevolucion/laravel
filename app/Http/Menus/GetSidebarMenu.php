<?php
/*
*   07.11.2019
*   MenusMenu.php
*/
namespace App\Http\Menus;

use App\MenuBuilder\MenuBuilder;
use Illuminate\Support\Facades\DB;
use App\Models\Menus;
use App\MenuBuilder\RenderFromDatabaseData;

class GetSidebarMenu implements MenuInterface{

    private $mb; //menu builder
    private $menu;

    public function __construct(){
        $this->mb = new MenuBuilder();
    }

    private function getMenuFromDB($menuName, $role, $locale){
        $this->menu = Menus::join('menu_role', 'menus.id', '=', 'menu_role.menus_id')
            ->join('menus_lang', 'menus.id', '=', 'menus_lang.menus_id')
            ->join('menulist', 'menulist.id', '=', 'menus.menu_id')
            ->select('menus.*', 'menus_lang.name as name')
            ->where('menulist.name', '=', $menuName)
           ->where('menu_role.role_name', '=', $role)
            ->where('menus_lang.lang', '=', $locale)
            ->orderBy('menus.sequence', 'asc')->get();       
    }

    // private function getGuestMenu($locale, $menuName){
    //     $this->getMenuFromDB($menuName, 'guest', $locale);
    // }

    // private function getUserMenu($locale, $menuName){
    //     $this->getMenuFromDB($menuName, 'user', $locale);
    // }

    // private function getAdminMenu($locale, $menuName){
    //     $this->getMenuFromDB($menuName, 'admin', $locale);
    // }

    public function get($role, $locale, $menuName = 'sidebar menu'){
        //$roles = explode(',', $roles);
       // if(empty($roles)){
        //  //   $roles='guest';
        //     $this->getGuestMenu($locale, $menuName);
        // }elseif(in_array('admin', $roles)){
        //     $this->getAdminMenu($locale, $menuName);
        // }elseif(in_array('user', $roles)){
        //     $this->getUserMenu($locale, $menuName);
        // }else{
        //     $this->getGuestMenu($locale, $menuName);
        // }

        if($role==""){$role="guest";}

        $this->getMenuFromDB($menuName,$role,$locale);
        $rfd = new RenderFromDatabaseData;
      return $rfd->render($this->menu);
    //  return $this->menu;
    }

    public function getAll($locale, $menuId = 1 ){
        $this->menu = Menus::join('menus_lang', 'menus.id', '=', 'menus_lang.menus_id')
            ->select('menus.*', 'menus_lang.name as name')
            ->where('menus.menu_id', '=', $menuId)
            ->where('menus_lang.lang', '=', $locale)
            ->orderBy('menus.sequence', 'asc')->get();   
        $rfd = new RenderFromDatabaseData;
        return $rfd->render($this->menu);
    }
}