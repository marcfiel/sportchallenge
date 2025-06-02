<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use App\Models\Reto;

class HomeController extends Controller
{
    public function index()
    {
        // Noticias desde archivo
        if (!Storage::exists('noticias.json')) {
            $noticiasSeleccionadas = [];
        } else {
            $json = Storage::get('noticias.json');
            $noticias = json_decode($json, true);

            if (!is_array($noticias) || empty($noticias)) {
                $noticiasSeleccionadas = [];
            } else {
                shuffle($noticias);
                $noticiasSeleccionadas = array_slice($noticias, 0, 6);
            }
        }

        // Retos activos (no completados ni abandonados)
        $usuarioId = Auth::id();

        $retosActivos = Reto::whereHas('usuarios', function ($query) use ($usuarioId) {
            $query->where('usuario_id', $usuarioId)
                ->where('completado', 0)
                ->where('abandonado', 0);
        })
            ->where('fecha_fin', '>=', now())
            ->orderBy('fecha_fin', 'asc')
            ->get();

        return view('home', compact('noticiasSeleccionadas', 'retosActivos'));
    }
}
