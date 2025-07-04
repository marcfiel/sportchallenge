<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reto extends Model
{
    protected $table = 'retos';
    public $timestamps = false;

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
    ];

    protected $fillable = [
        'nombre',
        'descripcion',
        'deporte',
        'tipo',
        'creador_id',
        'fecha_inicio',
        'fecha_fin',
        'objetivo_tipo',
        'objetivo_valor',
        'puntos_apuesta',
        'puntos_recompensa',
        'multiplicador',
    ];


    public function usuarios()
    {
        return $this->belongsToMany(\App\Models\User::class, 'usuarios_retos', 'reto_id', 'usuario_id');
    }
    public function usuariosQueSeUnieron()
    {
        return $this->hasMany(\App\Models\UsuariosReto::class, 'reto_id');
    }
}
