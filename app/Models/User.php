<?php

namespace App\Models;

use cuenta;
use App\Models\producto;
use App\Models\solicitud;
use App\Models\permisosforuser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable;
    use SoftDeletes;
    use HasRoles;
    use HasFactory;

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password', 'status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token','id'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    protected $dates = [
        'deleted_at'
    ];

    //protected $attributes = [ 
    //    'menuroles' => 'user',
    //];

    protected $guard_name = 'api';
    public function empresa()
    {
        return $this->belongsToMany(empresa::class);
    }

    public function cuentas()
    {
        return $this->belongsToMany(cuenta::class, 'cuenta_bancaria_has_user', 'user_id', 'cuenta_bancaria_id');
    }
    public function solicitudsend()
    {
        ///relacion de enviados
        return $this->belongsToMany(solicitud::class, 'solicitud_user', 'id_send', 'solicitud_id')
            ->withPivot('id_send', 'id_in', 'solicitud_id', 'id');
    }
    public function producto(){return $this->belongsToMany(producto::class,'producto_user');}

    public function solicitudin()
    {
        ///relacion de recibidos
        return $this->belongsToMany(solicitud::class, 'solicitud_user', 'id_in', 'solicitud_id')
            ->withPivot('id_send', 'id_in', 'solicitud_id');
    }
    public function myusers()
    {
        //relacion de usuarios creados
        return $this->belongsToMany(User::class, 'user_user', 'id_padre', 'id_hijo')
            ->withPivot('id_padre', 'id_hijo','activo','bloquea');



    }
    public function usuariosquemeaceptaron()/////funcion para hacer la contra a la funcion de arriba
    {
        //relacion de usuarios creados
        return $this->belongsToMany(User::class, 'user_user', 'id_hijo', 'id_padre')
            ->withPivot('id_padre', 'id_hijo','activo','bloquea');
    }
    public function myroles()
    {
        //relacion de usuarios creados
        return $this->belongsToMany(Role::class, 'roles_users', 'user_id', 'role_id');
    }

    public function permissions_received()
    {
        //relacion de usuarios y permisos delegados a usuarios
        return $this->belongsToMany(User::class, 'permissions_user_user', 'hijo_id', 'padre_id')
            ->withPivot('padre_id', 'hijo_id','permiso_id');
    }

    public function myrequestfriend()
    {
        //relacion de usuarios y permisos delegados a usuarios
        return $this->belongsToMany(User::class, 'requestfriend', 'id_send', 'id_in')
            ->withPivot('id_send', 'id_in','response','id');
    }
    public function myrequestfriendin()
    {
        return $this->belongsToMany(User::class, 'requestfriend', 'id_in', 'id_send')
        ->withPivot('id_send', 'id_in','response','id');
    }
    public function sistema(){

        return $this->belongsTo(sistema::class);
    }
}
