<?php
namespace App\Repositories\UserTables;


use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Repositories\UserTables\UserTableInterface as UserTableInterface;


class UserTableRepository  extends Controller implements UserTableInterface {

    public function __construct()
    {
        $this->middleware('auth:api');  
    }
    public function get( $sorter, $tableFilter, $columnFilter, $itemsLimit ){
        $db = DB::table('notes')
        ->join('users', 'users.id', '=', 'notes.users_id')
        ->join('status', 'status.id', '=', 'notes.status_id')
        ->select('notes.*', 'users.name as author', 'status.name as status', 'status.class as status_class');
     
        if( isset($columnFilter['author']) ){
            $db->where('users.name', 'like', '%' . $columnFilter['author'] . '%');
        }
        if( isset($columnFilter['title']) ){
            $db->where('notes.title', 'like', '%' . $columnFilter['title'] . '%');
        }
        if( isset($columnFilter['content']) ){
            $db->where('notes.content', 'like', '%' . $columnFilter['content'] . '%');
        }
        if( isset($columnFilter['applies_to_date']) ){
            $db->where('notes.applies_to_date', 'like', '%' . $columnFilter['applies_to_date'] . '%');
        }
        if( isset($columnFilter['status']) ){
            $db->where('status.name', 'like', '%' . $columnFilter['status'] . '%');
        }
        if( isset($columnFilter['note_type']) ){
            $db->where('notes.note_type', 'like', '%' . $columnFilter['note_type'] . '%');
        }
        if( strlen($tableFilter) > 0 ){
            $db->where(function ($query) use ($tableFilter) {
                $query->where('users.name', 'like',                 '%' . $tableFilter . '%')
                      ->orWhere('notes.title', 'like',              '%' . $tableFilter . '%')
                      ->orWhere('notes.content', 'like',            '%' . $tableFilter . '%')
                      ->orWhere('notes.applies_to_date', 'like',    '%' . $tableFilter . '%')
                      ->orWhere('status.name', 'like',              '%' . $tableFilter . '%')
                      ->orWhere('notes.note_type', 'like',          '%' . $tableFilter . '%');
            });
        }
        if( !empty($sorter) ){
            if($sorter['asc'] === false){
                $sortCase = 'desc';
            }else{
                $sortCase = 'asc';
            }
            switch($sorter['column']){
                case 'author':
                    $db->orderBy('users.name',              $sortCase);
                break;
                case 'title':
                    $db->orderBy('notes.title',             $sortCase);
                break;
                case 'content':
                    $db->orderBy('notes.content',           $sortCase);
                break;
                case 'applies_to_date':
                    $db->orderBy('notes.applies_to_date',   $sortCase);
                break;
                case 'status':
                    $db->orderBy('status.name',             $sortCase);
                break;
                case 'note_type':
                    $db->orderBy('notes.note_type',         $sortCase);
                break;
            }
        }
        return $db->paginate($itemsLimit);
    }

    public function allusersquery(){
        $db = DB::table('user_user as a')
        ->where('a.activo',1)
        ->where('a.id_hijo',auth()->user()->id)
        ->orWhere('a.id_padre',auth()->user()->id)
        ->where('a.activo',1)
        ->join('users as b', function ($join) {
        $join->on('b.id', '=', 'a.id_hijo')
        ->where([['b.id',"!=",auth()->user()->id],["b.status","Active"]])
        ->orOn('b.id', '=', 'a.id_padre')
        ->where([['b.id',"!=",auth()->user()->id],["b.status","Active"]]);
        })->select('b.*');
        return $db;
    }
  public function getall($sorter,$tableFilter,$columnFilter,$itemsLimit,$actual=1){
        
            $db=$this->allusersquery();
            $cuenta=count($db->get());
        if( isset($columnFilter['name']) ){
            $db->where('b.name', 'like', '%' . $columnFilter['name'] . '%');
        }
        if( isset($columnFilter['email']) ){
            $db->where('b.email', 'like', '%' . $columnFilter['email'] . '%');
        }
        if( isset($columnFilter['nickname']) ){
            $db->where('b.nickname', 'like', '%' . $columnFilter['nickname'] . '%');
        }
      
        if(strlen($tableFilter) > 0 ){
            $db->where(function ($query) use ($tableFilter) {
                $query->where('b.name', 'like',                 '%' . $tableFilter . '%')
                      ->orWhere('b.email', 'like',              '%' . $tableFilter . '%')
                      ->orWhere('b.nickname', 'like',            '%' . $tableFilter . '%');
                      
            });
        }
        if( !empty($sorter) ){
            if($sorter['asc'] === false){
                $sortCase = 'desc';
            }else{
                $sortCase = 'asc';
            }
            switch($sorter['column']){
                case 'email':
                    $db->orderBy('b.email',              $sortCase);
                break;
                case 'nickname':
                    $db->orderBy('b.nickname',             $sortCase);
                break;
                case 'name':
                    $db->orderBy('b.name',           $sortCase);
                break;
            
            }
        }
        $all=[];
        $all[0]=$db->paginate($itemsLimit,['*'],'page',$actual);
        $all[1]=$cuenta;
        return $all;
    }

}