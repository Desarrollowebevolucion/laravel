<?php

namespace App;
use App\Models\photo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class solicitud extends Model
{
    protected $guarded  = ['id'];


    public function photo(){
        ///relacion de recibidos
return $this->belongsToMany(photo::class,'solicitud_photo','id_solicitud','id_photo');
}
public function usersin(){
    ///relacion de recibidos
return $this->belongsToMany(User::class,'solicitud_user','solicitud_id','id_in')->withPivot('id','id_in','solicitud_id','id_send');;

}

}
