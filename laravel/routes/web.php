<?php

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\StravaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\RetosController;
use App\Http\Controllers\EntrenamientoController;
use App\Http\Controllers\NoticiasController;
use App\Http\Controllers\ActividadController;
use App\Http\Controllers\PerfilController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Http\Controllers\PremioController;

Route::post('/logout', function () {
    $accessToken = Session::get('access_token');

    if ($accessToken) {
        Http::asForm()->post('https://www.strava.com/oauth/deauthorize', [
            'access_token' => $accessToken,
        ]);
    }

    Session::flush();
    Auth::logout();

    return redirect('/');
})->name('logout');

// Rutas para el login con Strava
Route::get('/auth/strava', [StravaController::class, 'redirectToStrava'])->name('auth.strava');
Route::get('/auth/strava/callback', [StravaController::class, 'handleStravaCallback']);

// Login (página pública)
Route::get('/', function () {
    return view('login');
})->name('login');

// Grupo de rutas protegidas por auth
Route::middleware('auth')->group(function () {

    // Home
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // Retos
    Route::get('/retos', [RetosController::class, 'index'])->name('retos.index');

    // Acción para unirse a un reto específico
    Route::post('/retos/{id}/unirse', [RetosController::class, 'unirse'])->name('retos.unirse');

    // Página para ver tu progreso en un reto
    Route::get('/retos/{id}/progreso', [RetosController::class, 'progreso'])->name('retos.progreso');

    // Página para crear un reto personalizado
    Route::get('/retos/crear', [RetosController::class, 'crear'])->name('retos.crear');

    // Página de Mis Retos
    Route::get('/retos/mis-retos', [RetosController::class, 'misRetos'])->name('retos.misRetos');

    // Página para explorar y unirse a retos disponibles
    Route::get('/retos/unirse', [RetosController::class, 'unirseListado'])->name('retos.unirse.listado');

    // Mostrar detalles reto oficial
    Route::get('/retos/{id}', [RetosController::class, 'mostrar'])->name('retos.mostrar');

    Route::post('/retos', [RetosController::class, 'store'])->name('retos.store');


    // Ruta para abandonar un reto
    Route::post('/retos/{id}/abandonar', [RetosController::class, 'abandonar'])->name('retos.abandonar');

    // Ruta para eliminar un reto
    Route::delete('/retos/{id}', [RetosController::class, 'eliminar'])->name('retos.eliminar');

    // Ruta para reiniciar los logros
    Route::post('/actividad/{usuario}/reiniciar-logros', [ActividadController::class, 'reiniciarLogros'])->name('actividad.reiniciarLogros');




    // Entrenamiento
    Route::get('/entrenamiento', [EntrenamientoController::class, 'index'])->name('entrenamiento');

    // Actividad
    Route::get('/actividad', [ActividadController::class, 'index'])->name('actividad');

    // Premio
    Route::get('/premios', [PremioController::class, 'index'])->name('premios.index');

    Route::get('/premios/{id}', [PremioController::class, 'mostrar'])->name('premios.mostrar');

    Route::post('/premios/{id}/canjear', [PremioController::class, 'canjear'])->name('premios.canjear');



    // Perfil
    Route::get('/perfil', [PerfilController::class, 'mostrarPerfil'])->name('perfil');

    Route::post('/perfil/cambiar-rol', [PerfilController::class, 'cambiarRol'])->name('perfil.cambiarRol');


    // Noticias
    Route::get('/noticia/{id}', [NoticiasController::class, 'mostrarNoticia'])->name('noticia.mostrar');

    // Legal
    Route::view('/legal/aviso-legal', 'legal.aviso-legal')->name('legal.aviso');
    Route::view('/legal/politica-privacidad', 'legal.politica-privacidad')->name('legal.privacidad');
    Route::view('/legal/terminos-uso', 'legal.terminos-uso')->name('legal.terminos');
});
Route::get('/prueba', function () {
    return '✅ Laravel funciona en Hostinger';
});

