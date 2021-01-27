<?php

namespace App\Models;


use App\Models\cuenta;
use App\Models\permisosforuser;
use Illuminate\Database\Eloquent\Model;

class empresa extends Model
{
   protected $table="empresa";


    public function user()
    {
        return $this->belongsToMany(User::class);
    }

    public function productos()
    {
        return $this->belongsToMany(producto::class);
    }
    public function cuentas()
    {
        return $this->belongsToMany(cuenta::class,'cuenta_empresa','empresa_id','cuenta_id');
    }
    public function permisos()
    {
        return $this->belongsToMany(User::class,'permissions_user_user','id_model','cuenta_id');
    }


}

