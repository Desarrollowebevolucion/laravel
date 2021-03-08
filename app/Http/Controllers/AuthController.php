<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\empresa;
use App\Traits\Mytrait;

use Illuminate\Support\Collection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Validator;
use App\Repositories\UserTables\UserTableInterface as UserTableInterface;
use App\Traits\Paginador;
use App\Traits\joinobjectsforid;

class AuthController extends Controller
{ 
    
    use Mytrait;
    use joinobjectsforid;
    use Paginador;
    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register','pruebas']]);
    }
  
    /**
     * Register new user.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request){
        $validate = Validator::make($request->all(), [
            'name'      => 'required',
            'email'     => 'required|email|unique:users',
            'password'  => 'required|min:4|confirmed',
        ]);        
        if ($validate->fails()){
            return response()->json([
                'status' => 'error',
                'errors' => $validate->errors()
            ], 422);
        }        
        $user = new User;
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->status = 'Active';
        $user->save();       
        return response()->json(['status' => 'success'], 200);
    } 

    /**
     * Get a JWT via given credentials.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = request(['email', 'password']);

        if (! $token = auth()->attempt($credentials)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token, $request->email);
    }

    /**
     * Log the user out (Invalidate the token).
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pruebas(){
        $ids="";
       $asc="asc";
       $array = array(0 => 'blue', 1 => 'red', 2 => 'gris', 3 => 'red');

        $key = in_array('as', $array); // $key = 2;
            return "todo super ok";
       return User::Userreturn(2);
       $db=Role::usersComplete();
       return $db->get();
       $db=Role::query()
      
       ->with(['users'=>function($query){
         $query->where('id',"!=",1)
         ->with('permissions')
         ->with(['roles'=>function($query){$query->with('permissions');}]);
       }])
        ->orderby('roles.name',$asc)->get()->toarray();

     //  ->paginate(1,['*'],'page',1);
         //$db->users;
        $usrescomplete12=[];
         for($a=0;$a<count($db);$a++){
            
            $usrescomplete12=array_merge($usrescomplete12,$db[$a]['users']);

             
         }
        
            $arraynuevo=$this->depuraobject($usrescomplete12);




        $users = User::where('id',"!=",1)
        ->with(['roles'=>function($query){$query->with('permissions');}])
        ->with('permissions')
        ->get()->toarray();

           // $user22=[2,123,4,345,345,456,567,3];
          //  $complete=array_merge($user22,$users);  
        return $arraynuevo;
        return $this->paginate($users,5,2);
        define("FOO","ADMIN");

     

        $db =User::where('id',"!=",1)
        
        ->whereHas('roles', function (Builder $query) {
            $columnFilter=[];
         $columnFilter['name']=FOO;
            $query->where('name', 'like', '%' . $columnFilter['name'] . '%');
        
        }) 
         ->with(['roles'=>function($query){$query->with('permissions');}])
        ->with('permissions')
      
        
        ->get();
      

           

            return $db;
        
        $arrelgo=[6,7,8,1,2,3];
        $db =User::whereIn('id',$arrelgo)
        ->with(array(
            'roles'=>function($query){
           //$query->where('name','=','admin');
           $query->with('permissions');
        
        }
        
        )
    )
        ->with('permissions');
    
        $buscar= $db->get();
        $conroles=$this->agregaparametro($buscar,'roles','allroles');
       // return $buscar[0]->roles[0]['name'];
         
        
      // $prueba=$this->buscapropiedad($buscar,'roles','admin');
                return $conroles;
        $query=User::where('id','=',1)->with('myusers')->with('usuariosquemeaceptaron')->first();

        return $query->myusers[0];
        $query=User::where('id','=',1)->with('myusers')->with('usuariosquemeaceptaron')->first();
        $array=[$query->myusers,$query->usuariosquemeaceptaron];
        $userscomplete=$this->depura($array);
        return $userscomplete[0][0];
         if($user->sistema){
             if($user->sistema->lang){
                        return $user->sistema->lang;
             }else{
                 return "no tiene lang";
             }
         }else{
             return "no tiene";
         }
        

        $query=User::where('id','=',1)->with(['myusers'=>function($query) {
            $query->with(['roles'=>function($query){$query->with('permissions');}])
            ->with('permissions')
            ->with('empresa')->get();
        }])->get();

           $users=[];
           $delete=[];

           $query1=User::where('id','=',1)->with(['usuariosquemeaceptaron'=>function($query) {
               $query->with(['roles'=>function($query){$query->with('permissions');}])
               ->with('permissions')
               ->with('empresa')->get();
           }])->get();
           for($a=0;$a<count($query1[0]['usuariosquemeaceptaron']);$a++){

               if($query1[0]['usuariosquemeaceptaron'][$a]['status']=='Active'){
                       if($query1[0]['usuariosquemeaceptaron'][$a]['pivot']['activo']==1){
                        array_push($users,$query1[0]['usuariosquemeaceptaron'][$a]);
                       }else{
                           if($query1[0]['usuariosquemeaceptaron'][$a]['pivot']['bloquea']==1){
                               array_push($delete,$query1[0]['usuariosquemeaceptaron'][$a]);
                           }
                       }
                   }
           }
       for($a=0;$a<count($query[0]['myusers']);$a++){

           if($query[0]['myusers'][$a]['status']=='Active'){

                   if($query[0]['myusers'][$a]['pivot']['activo']==1){
                    array_push($users,$query[0]['myusers'][$a]);
                   }else{
                       if($query[0]['myusers'][$a]['pivot']['bloquea']==1){
                           array_push($delete,$query[0]['myusers'][$a]);
                       }
                   }

               }else{
                echo "else";

               }

       }



     $requestsend=User::where('id','=',1)->with('myrequestfriend')->with('myrequestfriendin')->get();

   return response()->json([
       'data' => $users,
       'delete'=>$delete,
       'requestsend'=>$requestsend[0]['myrequestfriend'],
       'requestin'=>$requestsend[0]['myrequestfriendin'],
       'code' => 200,'sys'=>$query[0]['myusers'][0]
   ]);



    }
    private function agregaparametro($array,$param,$propiedad){
            $arraynuevo=[];
        for($a=0;$a<count($array);$a++){
        $array[$a][$propiedad]=[];
        $roles=[];
         for($b=0;$b<count($array[$a]->$param);$b++){
        array_push($roles,$array[$a]->$param[$b]['name']);
           }
            $array[$a][$propiedad]=$roles;
            array_push($arraynuevo,$array[$a]);       

        }
        return $arraynuevo;
    
    }
    
private function buscapropiedad($array,$param,$search){
    ///array es todo el arreglo por ejemplo usuarios con sus permisos y sus roles
    ///param es la relacion a buscar por ejemplo usuarios.roles 
    ///seaarch es que buscamos en ese ejemplo se buscaria admin en usarios.roles puede ser un permiso en userios.permisos
       $nuevoarray=[];
    for($a=0;$a<count($array);$a++){
    
        for($b=0;$b<count($array[$a]->$param);$b++){
            if($array[$a]->$param[$b]['name']==$search){
                array_push($nuevoarray,$array[$a]);
            }
        }
    }
    return $nuevoarray;
}
    private function depura($array){
        $users=[];
        $delete=[];
        $mebloquearon=[];
        for($b=0;$b<count($array);$b++){
         for($a=0;$a<count($array[$b]);$a++){
             if($array[$b][$a]['status']=='Active'){
       if($array[$b][$a]['pivot']['activo']==1||$array[$b][$a]['pivot']['activo']==NULL){
                      array_push($users,$array[$b][$a]);
                     }else{
                         if($array[$b][$a]['pivot']['bloquea']==auth()->user()->id){
                             array_push($delete,$array[$b][$a]);
                         }else{
                             array_push($mebloquearon,$array[$b][$a]);
    
                         }
                     }
                 }
         }     
        }
         $complete=[0=>$users,1=>$delete,2=>$mebloquearon];
        return $complete;
 
    }
    public function logout(Request $request)
    {   
                  
        $this->updatesistema($request);
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken(auth()->refresh());
    }
   

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token, $email)
    {
        $user = User::where('email', '=', $email)->first();
        $active=200;
        $sistema=['lang'=>'en','metodo'=>1,'firstlog'=>0,'tema'=>'false'];
        switch($user->status){
        case 'Pending':
            $active=202;
            break;
         case'Banned':
            $active=403;
            break;
        }
           if($user->sistema){
        $user->sistema->lang?$sistema['lang']=$user->sistema->lang:'';
        $user->sistema->metodo?$sistema['metodo']=$user->sistema->metodo:'';
        $user->sistema->tema?$sistema['tema']=$user->sistema->tema:'';
        $user->sistema->firstlog?$sistema['firstlog']=$user->sistema->firstlog:'';
            }
        return response()->json([
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->factory()->getTTL() * 60,
            'roles' =>$user->roles,
            'user'=>$user,
            'nuevo'=>$sistema['firstlog'],  
            'code'=>$active,
            'sistema'=>$sistema
        ]);
        
    }
}