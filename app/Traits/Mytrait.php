<?php
/*
*   01.02.2021
*   my traits.php
*/
namespace App\Traits;
use App\Models\User;
use App\Models\sistema;
use Illuminate\Http\Request;



trait Mytrait{
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
            'tema'=>$request->tema,
            'firstlog'=>0]);
            $user->sistema_id=$sys->id;
            $user->save();

        }

    }

}

