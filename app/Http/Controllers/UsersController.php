<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\cuenta;
use App\Models\empresa;
use App\Models\producto;
use App\Models\solicitud;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\MailWelcome;

use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;
use PhpParser\Node\Expr\AssignOp\Concat;
use App\Repositories\UserTables\UserTableInterface as UserTableInterface;

class UsersController extends Controller
{
    use Notifiable;
    use HasRoles;
    private $tableuser;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserTableInterface $usertable)
    {
        $this->middleware('auth:api');  
        $this->tableuser=$usertable;
    }
   
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $you = auth()->user()->id;
        $users = DB::table('users')
        ->select('users.id', 'users.name', 'users.email', 'users.menuroles as roles', 'users.status', 'users.email_verified_at as registered')
        ->whereNull('deleted_at')
        ->get();
        return response()->json( compact('users', 'you') );
    }

    public function getRoles(Request $request){

        return response()->json([
            'code' => 200,
            'data'=> auth()->user()->roles
        ]);

    }


    public function changeRole(Request $request){
        $user = User::find(auth()->user()->id);
        
        for($a=0;$a<count($user->roles);$a++){
            if($user->roles[$a]->name==$request->option){
                $user->menuroles=$request->option;
                $user->save();
                return response()->json([
                    'code' => 200
                ]);
            }
        }
        return response()->json([
            'code' => 401
        ]);
    }
        public function setLang(Request $request){
            $user = User::find(auth()->user()->id);
            $user->lang=$request->option;
            $user->save();
            return response()->json([
                'code' => 200
            ]);

    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = DB::table('users')
        ->select('users.id', 'users.name', 'users.email', 'users.menuroles as roles', 'users.status', 'users.email_verified_at as registered')
        ->where('users.id', '=', $id)
        ->first();
        return response()->json( $user );
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = DB::table('users')
        ->select('users.id', 'users.name', 'users.email', 'users.menuroles as roles', 'users.status')
        ->where('users.id', '=', $id)
        ->first();
        return response()->json( $user );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function pruebaimagen(Request $file)
    {
        $userin = User::findorfail(auth()->user()->id);
        $ruta = auth()->user()->id . '/profile';
        if ($userin->photo == "" || $userin->photo == null) {
            $namecreate = \Storage::disk('s3')->put($ruta, $file->file('file'), 'public');
            if ($namecreate) {
                $userin->photo = $namecreate;
                $userin->update=1;
                $userin->save();
                return app('prefix_aws') . $namecreate;
            }
        } else {
            $deletefoto = $userin->photo;
            \Storage::disk('s3')->delete($deletefoto);
            $namecreate = \Storage::disk('s3')->put($ruta, $file->file('file'), 'public');
            $userin->photo = $namecreate;
            $userin->update=1;

            $userin->save();
            return app('prefix_aws') . $namecreate;
        }
    }
    public function refreshpass(Request $request)
    {
        $userin = User::findorfail(auth()->user()->id);
        $userin->password = bcrypt($request->password1);
        $userin->save();

        return response()->json(['data' => $userin, 'code' => 200]);
    }
    public function SetMetodo(Request $request){
        $userin = User::findorfail(auth()->user()->id);
        $userin->metodo =$request->metodo;
        $userin->save();
        return response()->json(['data' => $userin, 'code' => 200]);
    }

    
   public function aceptrequest(Request $request){
    $user=User::findorfail(auth()->user()->id);
    if(DB::table('user_user')
    ->where([['id_padre',"=",auth()->user()->id],['id_hijo','=',$request->id]])
    ->orWhere([['id_hijo','=',auth()->user()->id],['id_padre','=',$request->id]])
 ->update(['activo' => 1,'bloquea'=> 0])){
 }else{
    $user->myusers()->attach($request->id);//// agregas a tus amigo en la tabla
 }
   $user->myrequestfriendin()->detach($request->id);//// elimino la relacion de la tabla
   return response()->json([
                'code' => 200,
 ]);
}
public function createorrequest(Request $request){
    $user = User::where('email', '=', $request->email)->first();
    $userlog = User::findorfail( auth()->user()->id);
    if ($user === null) {
        $numbre= rand(6, 15);
          $claveinicial =substr($request->key, $numbre, 8);
          $data=['name'=>$request->name,'password'=>$claveinicial];
          $send=$request->email;
          try{
          Mail::to($send)->send(new MailWelcome($data));
          $userin = new User;
         $userin->email=$request->email;
         $userin->name=$request->name;
         $userin->menuroles='user';
         $userin->status='Active';
        $userin->password = bcrypt($claveinicial);
         $userin->assignRole(2);
        $userin->save();
          $userlog->myrequestfriend()->attach($userin->id);
         return response()->json([
            'code'=>200,
            ]);

          }catch(Exception $e){return $e;}

    }else{//// si existe el usuario 
           $responseall=$this->allusersonly();

         if($this->buscainarray($userlog->myrequestfriend,$request->email)||
           $this->buscainarray($userlog->myrequestfriendin,$request->email)||
           $this->buscainarray($responseall[0],$request->email)||
           $this->buscainarray($responseall[1],$request->email)||
           $this->buscainarray($responseall[2],$request->email)){
            $code=2001;
         $this->buscainarray($userlog->myrequestfriend,$request->email)? $code=195 : '' ;
        $this->buscainarray($userlog->myrequestfriendin,$request->email)?$code=196: '';
         $this->buscainarray($responseall[1],$request->email)?$code=197: '';///user bloqueado por ti
         $this->buscainarray($responseall[2],$request->email)?$code=403: ''; ///user te tiene bloqueado
         $this->buscainarray($responseall[0],$request->email)?$code=408: ''; ///tus usuarios

            return response()->json([
                'code'=>$code
                ]);

           }else{
            $user->myrequestfriendin()->attach($userlog->id);
            return response()->json([
                'code'=>200,
                ]);

           }
           
  



    }



}

public function buscainarray($requestin,$mail){
    for($m=0;$m<count($requestin);$m++){
        if($requestin[$m]->email==$mail){
            return true;
        }
    }

    return false;

}


public function unlockuser(Request $request){

   $userlog = User::findorfail(auth()->user()->id);
    $userlog->myusers()->detach($request->id);
    $userlog->usuariosquemeaceptaron()->detach($request->id);
    return response()->json([
        'code' => 200,
]);

}
   public function yourrequest(Request $request){
    $requestsend=User::where('id','=',auth()->user()->id)->with('myrequestfriend')->with('myrequestfriendin')->first();
    $responseall=$this->allusersonly();
       return response()->json([
        'requestsend'=>$requestsend['myrequestfriend'],
        'requestin'=>$requestsend['myrequestfriendin'],
        'total'=>count($responseall[0]),
        'delete'=>$responseall[1],
        'losquemeborraron'=>$responseall[2],
        'code' => 200,
    ]);
   }
   public function cancelrequest(Request $request){


    $user=User::findorfail(auth()->user()->id);
    $user->myrequestfriend()->detach($request->id);
    return response()->json([
        'code' => 200,
       ]);

}
   public function lockuserrequest(Request $request){


    $user=User::findorfail(auth()->user()->id);
    $user->myrequestfriendin()->detach($request->id);//// eliminamos la soliciutud

   $user->myusers()->attach($request->id);//// ingresamos la relacion de amistad para despues dejar bloqueada esa relacion

    DB::table('user_user')
 ->where([['id_padre',"=",auth()->user()->id],['id_hijo','=',$request->id]])
 ->orWhere([['id_hijo','=',auth()->user()->id],['id_padre','=',$request->id]])
 ->update(['activo'=>2,'bloquea'=>auth()->user()->id]);

 return response()->json([
    'code' => 200,
   ]);
}
   public function lockuser(Request $request){
    DB::table('user_user')
 ->where([['id_padre',"=",auth()->user()->id],['id_hijo','=',$request->id]])
 ->orWhere([['id_hijo','=',auth()->user()->id],['id_padre','=',$request->id]])
 ->update(['activo'=>2,'bloquea'=>auth()->user()->id]);
 $this->destroypermisos($request->id);///// eliminamos todos los PERMISOS OTORGADOS A ESE USUARIO DE TODOS LOS MODELOS

 return response()->json([
 'code' => 200,
]);
//return $this->allusers();

}
public function destroypermisos($request){
    DB::table('permissions_user_user')
      ->where([['padre_id',"=",auth()->user()->id],['hijo_id','=',$request]])
    ->orWhere([['hijo_id','=',auth()->user()->id],['padre_id','=',$request]])->delete();
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

   
   public function cancelrequestin(Request $request){


    $user=User::findorfail(auth()->user()->id);
    $user->myrequestfriendin()->detach($request->id);
    return response()->json([
        'code' => 200
    ]);


}

    public function update(Request $request)
    {

        $validatedData = $request->validate([
            'name'       => 'required|min:6|max:256',
            'email'      => 'required|email|max:256'
        ]);
        User::where('id',auth()->user()->id)
        ->update($request
        ->only(['name','email','telefono','f_nacimiento','referencias','n_ext','n_int','estado','municipo','colonia','cp','calle','nickname']));
        $userin = User::findorfail(auth()->user()->id);
        return response()->json([
            'data' => $userin,
            'code' => 200
        ]);
        
        }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    
    public function allusersonly(){ /////regresar solo usuarios activos, evitando solicitudes enviadas recibvidas etc
        $query=User::where('id','=',auth()->user()->id)->with('myusers')->with('usuariosquemeaceptaron')->first();
           $array=[$query->myusers,$query->usuariosquemeaceptaron];
           $userscomplete=$this->depura($array);
           return $userscomplete;
    }
    public function allusersonlypost(){ /////regresar solo usuarios activos, evitando solicitudes enviadas recibvidas etc
     return response()->json(['code' => 200,'data'=>$this->tableuser->allusersquery()->get()]);
    }
    public function destroy($id)
    {
        $user = User::find($id);
        if($user){
            $user->delete();
        }
        return response()->json( ['status' => 'success'] );
    }


    ///////// con interface desde el  backend
    function interfaceuser(Request $request){
        $sorter         = $request->input('sorter');
        $tableFilter    = $request->input('tableFilter');
        $columnFilter   = $request->input('columnFilter');
        $itemsLimit     = $request->input('itemsLimit');
        $pagecurrent    =$request->input('currentpage');
        $users = $this->tableuser->getall( $sorter, $tableFilter, $columnFilter, $itemsLimit,$pagecurrent);
        return response()->json(['data'=>$users[0],'count'=>$users[1]]);
    }
}
