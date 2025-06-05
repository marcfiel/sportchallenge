<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuariosReto extends Model
{
    protected $table = 'usuarios_retos';
    public $timestamps = false;

    protected $fillable = [
        'usuario_id',
        'reto_id',
        'fecha_union',
        'completado',
        'puntos_obtenidos',
    ];
}
