<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Arr;
use Carbon\Carbon;

class EntrenamientoController extends Controller
{
    public function index()
    {
        // CONSEJO DEL DÍA
        $pathConsejos = storage_path('app/private/consejos_dia.json');
        $consejos = file_exists($pathConsejos) ? json_decode(file_get_contents($pathConsejos), true) : [];
        $index = (Carbon::now()->dayOfYear - 1) % max(count($consejos), 1);
        $consejo = $consejos[$index] ?? "Mantente activo y cuida tu salud.";

        // EJERCICIOS DEL DÍA
        $pathEjercicios = storage_path('app/private/ejercicios.json');
        $ejerciciosTodos = file_exists($pathEjercicios)
            ? json_decode(file_get_contents($pathEjercicios), true)
            : [];

        // Elegimos 4 ejercicios distintos cada día
        $seed = Carbon::now()->dayOfYear;
        mt_srand($seed); // semilla basada en el día
        $ejerciciosOrdenados = $ejerciciosTodos;
        shuffle($ejerciciosOrdenados); // mezcla reproducible con mt_srand
        mt_srand(); // restaurar aleatoriedad normal

        // Seleccionamos los 4 primeros ejercicios mezclados
        $ejerciciosDia = array_slice($ejerciciosOrdenados, 0, min(4, count($ejerciciosOrdenados)));

        return view('entrenamiento', compact('consejo', 'ejerciciosDia'));
    }
}
