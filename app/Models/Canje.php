<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Canje extends Model
{
    protected $table = 'canjes';

    protected $fillable = [
        'user_id',
        'recompensa_id',
        'fecha_canje',
    ];

    public $timestamps = false;
}
