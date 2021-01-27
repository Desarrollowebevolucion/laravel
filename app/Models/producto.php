<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class producto extends Model
{
    protected $guarded  = ['id'];
    public function cotizacion(){return $this->hasOne(cotizacion::class);}
    public function empresa(){return $this->belongsToMany(empresa::class);}
    public function photo(){return $this->belongsToMany(photo::class,'producto_photo');}
    public function user(){return $this->belongsToMany(User::class,'producto_user');}


}
