<?php
namespace App\Repositories\UserTables;


use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use Illuminate\Database\Eloquent\Builder;
use App\Repositories\UserTables\UserTableInterface as UserTableInterface;
use App\Traits\joinobjectsforid;
use App\Traits\Paginador;


class UserTableRepository  extends Controller implements UserTableInterface {
    use joinobjectsforid;
    use Paginador;

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

    public function unlockuseradmin($request){
        User::where('id',$request->id)
        ->update(['status' =>'Active']);
        return User::Userreturn($request->id);

    }
    public function lockuseradmin($request){
        User::where('id',$request->id)
        ->update(['status' =>'Inactive']);
          return User::Userreturn($request->id);

    }
    public function updateuserroles($request){
       
    $user = User::findorfail($request->id);
    $user->syncRoles($request->roles);
    $permisos = [];
    foreach ($request->permisos as $permiso) {
        $permisoin = Permission::where('descripcion', $permiso)->first();
        array_push($permisos, $permisoin->id);
    }

    $user->syncPermissions($permisos);
    return User::Userreturn($request->id);

    }
    public function updateuser($request){
        User::where('id',$request->id)
        ->update(['name' =>  $request->name,
                  'email'=>$request->email,
                  'telefono'=>$request->telefono,
                  'f_nacimiento'=>$request->fechanacimiento,
                  'referencias'=>$request->referencias,
                  'n_ext'=>$request->numero_ext,
                  'n_int'=>$request->numero_int,
                  'estado'=>$request->estado,
                  'municipio'=>$request->municipio,
                  'colonia'=>$request->colonia,
                  'cp'=>$request->cp,
                  'calle'=>$request->calle,
                  'nickname'=>$request->nickname
                  ]);
                  return User::Userreturn($request->id);


    }
    public function create($request)
    {
        $claveinicial = "evolucionweb";

        $userin = new User;
        $userin->name = $request->name;
        $userin->email = $request->email;
        $userin->telefono = $request->telefono;
        $userin->f_nacimiento = $request->fechanacimiento;
        $userin->referencias = $request->referencias;
        $userin->n_ext = $request->numero_ext;
        $userin->n_int = $request->numero_int;
        $userin->estado = $request->estado;
        $userin->municipio = $request->municipio;
        $userin->colonia = $request->colonia;
        $userin->cp = $request->cp;
        $userin->calle = $request->calle;
        $userin->nickname = $request->nickname;
        $userin->password = bcrypt($claveinicial);
        $userin->menuroles="user";
        $userin->status='Active';
        $userin->save();
        $userin->assignRole(2);
 
        return User::Userreturn($request->id);
    }
    public function getallusers(){
        $users=[];
        $users[0]=User::Allusersqueryadmin()->get();
        $users[1]=User::Allusersnotqueryadmin()->get();
        return $users;
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
    public function allusersqueryadmin(){
        return User::Allusersqueryadmin();
    }

    public function orderroles($sorter,$tableFilter,$columnFilter,$itemsLimit,$actual=1){
        $all=[];
      
        $db=Role::UsersComplete();
          //  return $db->get();
        if( !empty($sorter) ){
            if($sorter['asc'] === false){
                $sortCase = 'desc';
            }else{
                $sortCase = 'asc';
            }
        }
        if(isset($columnFilter['name']) ){
            define('searchrole',$columnFilter['name']);
            $db->whereHas('users', function (Builder $query) {
                $query->where('name', 'like', '%' . searchrole . '%');
            });
        }
        if(isset($columnFilter['email']) ){
            define('searchrole',$columnFilter['email']);
            $db->whereHas('users', function (Builder $query) {
                $query->where('email', 'like', '%' . searchrole . '%');
            });
        }   if(isset($columnFilter['nickname']) ){
            define('searchrole',$columnFilter['nickname']);
            $db->whereHas('users', function (Builder $query) {
                $query->where('nickname', 'like', '%' . searchrole . '%');
            });
        }
        if(strlen($tableFilter) > 0 ){
            $db->where(function ($query) use ($tableFilter) {
                define("searchall",$tableFilter);
                $query->whereHas('users', function (Builder $query) {
                    $query->where('email', 'like', '%' . searchall . '%');
                })
                 ->orwhereHas('users', function (Builder $query) {
                        $query->where('nickname', 'like', '%' . searchall . '%');
                    })
                 ->orwhereHas('users', function (Builder $query) {
                        $query->where('name', 'like', '%' . searchall . '%');
                    })
                ->orwhereHas('users', function (Builder $query) {
                        $query->where('nickname', 'like', '%' . searchall . '%');
                    }
                )->orWhere('name', 'like',              '%' . searchall . '%');
                      
                      
            });
        }
       $dbto=$db->orderby('roles.name',$sortCase)->get()->toarray();
        $une=$this->joinarrays($dbto);
        $arraynuevo=$this->depuraobject($une);
        $cuenta=count($arraynuevo);
        $datos=$this->paginate($arraynuevo,$itemsLimit,$actual);
        $all[0]= $datos;
        $all[1]=User::Allusersnotqueryadmin()->get();
        $all[2]=$cuenta;
            return $all;
        
    }
  
    
     
    public function getalladmin($sorter,$tableFilter,$columnFilter,$itemsLimit,$actual=1){
        switch($sorter['column']){
             case 'roles':
            return $this->orderroles($sorter,$tableFilter,$columnFilter,$itemsLimit,$actual=1);       
             }
       $db=$this->allusersqueryadmin();
        $cuenta=count($db->get());
     
        if(isset($columnFilter['roles'])){
            define("search",$columnFilter['roles']);
              $db->whereHas('roles', function (Builder $query) {
                $query->where('name', 'like', '%' . search . '%');
            });

      
        }
    if( isset($columnFilter['name']) ){
        $db->where('name', 'like', '%' . $columnFilter['name'] . '%');

    }
    if( isset($columnFilter['email']) ){
        $db->where('email', 'like', '%' . $columnFilter['email'] . '%');
    }
    if( isset($columnFilter['nickname']) ){
        $db->where('nickname', 'like', '%' . $columnFilter['nickname'] . '%');
    }
    
    if(strlen($tableFilter) > 0 ){
        $db->where(function ($query) use ($tableFilter) {
            define("searchall",$tableFilter);
            $query->where('name', 'like',                 '%' . $tableFilter . '%')
                  ->orWhere('email', 'like',              '%' . $tableFilter . '%')
                  ->orWhere('nickname', 'like',            '%' . $tableFilter . '%')
                  ->orwhereHas('roles', function (Builder $query) {
                    $query->where('name', 'like', '%' . searchall . '%');
                });
                  
                  
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
                $db->orderBy('email',              $sortCase);
            break;
            case 'nickname':
                $db->orderBy('nickname',             $sortCase);
            break;
            case 'name':
                $db->orderBy('name',           $sortCase);
            break;
         
               
           
           
        
        
    }
    $all=[];
    $all[0]=$db->paginate($itemsLimit,['*'],'page',$actual);
    
    $all[1]=User::Allusersnotqueryadmin()->get();
    $all[2]=$cuenta;
    return $all;
}
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