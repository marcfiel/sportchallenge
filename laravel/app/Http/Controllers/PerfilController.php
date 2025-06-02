<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Auth\StravaController;

class PerfilController extends Controller
{
    public function mostrarPerfil()
{
    $usuario = User::findOrFail(Auth::id()); // Esto asegura una instancia Eloquent

    $accessToken = StravaController::getValidAccessToken($usuario->id);

    if ($accessToken) {
        $stravaUser = Http::withToken($accessToken)
            ->get('https://www.strava.com/api/v3/athlete')
            ->json();

        $nuevaImagen = $stravaUser['profile'] ?? null;

        if (str_starts_with($nuevaImagen, 'http')) {
            $usuario->profile_picture = $nuevaImagen;
            $usuario->save(); // Ahora sí funciona
        }
    }

    return view('perfil', ['usuario' => $usuario]);
}

    public function cambiarRol(Request $request)
{
    $usuario = User::findOrFail(Auth::id());

    if ($usuario->role === 'admin') {
        $usuario->role = 'user';
        $usuario->save();
        return back()->with('status', 'Has cambiado a modo usuario.');
    } else {
        $clave = $request->input('clave_admin');
        $claveCorrecta = env('CLAVE_MAESTRA_ADMIN', 'admin123');

        if ($clave === $claveCorrecta) {
            $usuario->role = 'admin';
            $usuario->save();
            return back()->with('status', 'Ahora eres administrador.');
        } else {
            return back()->with('error', 'Contraseña incorrecta para ser administrador.');
        }
    }
}

}
