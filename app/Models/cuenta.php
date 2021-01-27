<?php

namespace App;
use App\User;
use App\empresa;
use Illuminate\Database\Eloquent\Model;

class cuenta extends Model
{
    protected $table="cuenta_bancaria";
    public function users()
    {
        return $this->belongsToMany(User::class,'cuenta_bancaria_has_user','cuenta_bancaria_id','user_id');
    }
    public function empresas()
    {
        return $this->belongsToMany(empresa::class,'cuenta_empresa','cuenta_id','empresa_id');
    }
}

