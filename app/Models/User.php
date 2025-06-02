<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'strava_id',
        'username',
        'firstname',
        'lastname',
        'city',
        'country',
        'sex',
        'profile_picture',
        'role',
        'puntos',
    ];

    public $timestamps = false;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function getRangoAttribute()
    {
        $puntos = $this->puntos;

        if ($puntos >= 60000) {
            return [
                'nombre' => 'Leyenda',
                'color' => 'text-yellow-600',
                'siguiente' => null,
            ];
        } elseif ($puntos >= 30000) {
            return [
                'nombre' => 'Proactivo',
                'color' => 'text-purple-600',
                'siguiente' => 60000,
            ];
        } elseif ($puntos >= 15000) {
            return [
                'nombre' => 'Constante',
                'color' => 'text-blue-600',
                'siguiente' => 30000,
            ];
        } else {
            return [
                'nombre' => 'Novato',
                'color' => 'text-green-600',
                'siguiente' => 15000,
            ];
        }
    }


    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
