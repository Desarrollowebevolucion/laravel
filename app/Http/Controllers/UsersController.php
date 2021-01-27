<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\cuenta;
use App\Models\empresa;
use App\Models\sistema;
use App\Models\producto;
use App\Models\solicitud;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

use Tymon\JWTAuth\Contracts\JWTSubject;
use Illuminate\Notifications\Notifiable;

class UsersController extends Controller
{
    use Notifiable;
    use HasRoles;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api');
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

    public function updatesistema(Request $request){

        $user=User::findorfail(auth()->user()->id);
        if($user->sistema){
            $sys=sistema::findorfail($user->sistema->id);
            $sys->lang=$request->locale;
            $sys->metodo=$request->metodo;
            $sys->tema=$request->tema;
            $sys->save();
        }else{
            $sys=sistema::create([
            'lang'=>$request->locale,
           'metodo'=>$request->metodo,
            'tema'=>$request->tema]);
            $user->sistema_id=$sys->id;
        }

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
    public function allusers(){
        $query=User::where('id','=',auth()->user()->id)->with(['myusers'=>function($query) {
            $query->with(['roles'=>function($query){$query->with('permissions');}])
            ->with('permissions')
            ->with('empresa')->get();
        }])->get();
              $query1=User::where('id','=',auth()->user()->id)->with(['usuariosquemeaceptaron'=>function($query) {
               $query->with(['roles'=>function($query){$query->with('permissions');}])
               ->with('permissions')
               ->with('empresa')->get();
           }])->get();
           $conctarrys=[0=>$query1[0]['usuariosquemeaceptaron'],1=>$query[0]['myusers']];
            $depura=$this->depura($conctarrys);
         
      $requestsend=User::where('id','=',auth()->user()->id)->with('myrequestfriend')->with('myrequestfriendin')->get();
   return response()->json([
       'data' => $depura[0],
       'delete'=>$depura[1],
       'requestsend'=>$requestsend[0]['myrequestfriend'],
       'requestin'=>$requestsend[0]['myrequestfriendin'],
       'code' => 200,
   ]);

   }
   public function depura($array){
       $users=[];
       $delete=[];
       for($b=0;$b<count($array);$b++){
        for($a=0;$a<count($array[$b]);$a++){
            if($array[$b][$a]['status']=='Active'){
      if($array[$b][$a]['pivot']['activo']==1){
                     array_push($users,$array[$b][$a]);
                    }else{
                        if($array[$b][$a]['pivot']['bloquea']==auth()->user()->id){
                            array_push($delete,$array[$b][$a]);
                        }
                    }
                }
        }     

       }
        $complete=[0=>$users,1=>$delete];
       return $complete;

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
    public function destroy($id)
    {
        $user = User::find($id);
        if($user){
            $user->delete();
        }
        return response()->json( ['status' => 'success'] );
    }
}
