<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Premio extends Model
{
    protected $table = 'premios';

    protected $fillable = [
        'nombre',
        'descripcion',
        'imagen',
        'puntos_necesarios',
        'disponible',
        'fecha_inicio',
        'fecha_fin',
    ];

    protected $casts = [
        'fecha_inicio' => 'datetime',
        'fecha_fin' => 'datetime',
        'disponible' => 'boolean',
    ];
}
