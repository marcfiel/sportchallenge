<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Request;
use App\Models\Canje;
use Carbon\Carbon;
use App\Models\Premio;

class ActividadController extends Controller
{
    public function index(Request $request)
    {
        $usuario = Auth::user();
        $puntosActuales = $usuario->puntos ?? 0;

        $deporteElegido = $request->query('deporte', 'correr');

        // Determina el tipo de actividad en Strava según el deporte que se ha elegido
        $tipoStrava = match ($deporteElegido) {
            'caminar' => 'walk',
            'bicicleta' => 'ride',
            default => 'run'
        };

        $isWalk = $tipoStrava === 'walk';

        $tokenData = DB::table('user_tokens')->where('strava_user_id', $usuario->strava_id)->first();
        if (!$tokenData) return redirect()->route('auth.strava');

        if (time() >= $tokenData->expires_at) {
            $refresh = Http::asForm()->post('https://www.strava.com/oauth/token', [
                'client_id' => env('STRAVA_CLIENT_ID'),
                'client_secret' => env('STRAVA_CLIENT_SECRET'),
                'grant_type' => 'refresh_token',
                'refresh_token' => $tokenData->refresh_token,
            ]);

            $newToken = $refresh->json();

            DB::table('user_tokens')->where('strava_user_id', $usuario->strava_id)->update([
                'access_token' => $newToken['access_token'],
                'refresh_token' => $newToken['refresh_token'],
                'expires_at' => $newToken['expires_at'],
            ]);

            $accessToken = $newToken['access_token'];
        } else {
            $accessToken = $tokenData->access_token;
        }

        $athlete = Http::withToken($accessToken)->get('https://www.strava.com/api/v3/athlete')->json();
        $athleteId = $athlete['id'];

        if ($isWalk) {
            $desde = Carbon::now()->subDays(28)->timestamp;

            $response = Http::withToken($accessToken)->get('https://www.strava.com/api/v3/athlete/activities', [
                'per_page' => 200
            ]);

            $actividades = $response->json();

            $recientes = ['count' => 0, 'distance' => 0, 'moving_time' => 0, 'elevation_gain' => 0];
            $totalesCalc = ['count' => 0, 'distance' => 0, 'moving_time' => 0, 'elevation_gain' => 0];

            foreach ($actividades as $actividad) {
                if ($actividad['type'] === 'Walk') {
                    $totalesCalc['count']++;
                    $totalesCalc['distance'] += $actividad['distance'];
                    $totalesCalc['moving_time'] += $actividad['moving_time'];
                    $totalesCalc['elevation_gain'] += $actividad['total_elevation_gain'] ?? 0;

                    if (strtotime($actividad['start_date']) >= $desde) {
                        $recientes['count']++;
                        $recientes['distance'] += $actividad['distance'];
                        $recientes['moving_time'] += $actividad['moving_time'];
                        $recientes['elevation_gain'] += $actividad['total_elevation_gain'] ?? 0;
                    }
                }
            }

            $estadisticas4Semanas = [
                'actividades' => $recientes['count'],
                'distancia' => round($recientes['distance'] / 1000, 2),
                'tiempo' => gmdate("H\h i\m", $recientes['moving_time']),
                'desnivel' => round($recientes['elevation_gain'], 2),
            ];

            $totales = [
                'actividades' => $totalesCalc['count'],
                'distancia' => round($totalesCalc['distance'] / 1000, 2),
                'tiempo' => gmdate("H\h i\m", $totalesCalc['moving_time']),
                'desnivel' => round($totalesCalc['elevation_gain'], 2),
            ];
        } else {
            $stats = Http::withToken($accessToken)->get("https://www.strava.com/api/v3/athletes/{$athleteId}/stats")->json();

            $all = $stats["all_{$tipoStrava}_totals"] ?? ['count' => 0, 'distance' => 0, 'moving_time' => 0, 'elevation_gain' => 0];
            $recent = $stats["recent_{$tipoStrava}_totals"] ?? ['count' => 0, 'distance' => 0, 'moving_time' => 0, 'elevation_gain' => 0];

            $estadisticas4Semanas = [
                'actividades' => $recent['count'],
                'distancia' => round($recent['distance'] / 1000, 2),
                'tiempo' => gmdate("H\h i\m", $recent['moving_time']),
                'desnivel' => round($recent['elevation_gain'], 2),
            ];

            $totales = [
                'actividades' => $all['count'],
                'distancia' => round($all['distance'] / 1000, 2),
                'tiempo' => gmdate("H\h i\m", $all['moving_time']),
                'desnivel' => round($all['elevation_gain'], 2),
            ];
        }

        // Se obtiene un array con los premios que ya han sido canjeados por el usuario
        $premiosCanjeados = Canje::where('user_id', $usuario->id)
            ->pluck('recompensa_id')
            ->toArray();

        $premiosDestacados = Premio::where('disponible', 1)
            ->whereNotIn('id', $premiosCanjeados)
            ->where(function ($query) {
                $now = now();
                $query->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $now);
            })
            ->where(function ($query) {
                $now = now();
                $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $now);
            })
            ->orderBy('puntos_necesarios', 'asc')
            ->limit(4)
            ->get();

        // Obtener todos los logros y cuáles tiene el usuario
        $logros = DB::table('logros')
            ->leftJoin('usuarios_logros', function ($join) use ($usuario) {
                $join->on('logros.id', '=', 'usuarios_logros.logro_id')
                    ->where('usuarios_logros.usuario_id', '=', $usuario->id);
            })
            ->select(
                'logros.id',
                'logros.nombre',
                'logros.descripcion',
                'logros.imagen',
                'logros.puntos_otorgados',
                'usuarios_logros.id as conseguido',
                'usuarios_logros.fecha_conseguido'
            )
            ->get();

        return view('actividad', compact(
            'usuario',
            'puntosActuales',
            'deporteElegido',
            'estadisticas4Semanas',
            'totales',
            'premiosDestacados',
            'logros'
        ));
    }

    public function reiniciarLogros($usuarioId)
    {
        // Solo admins pueden acceder
        if (Auth::user()->role !== 'admin') {
            abort(403, 'No autorizado');
        }

        // Borrar todos los logros del usuario
        DB::table('usuarios_logros')->where('usuario_id', $usuarioId)->delete();

        return back()->with('success', 'Logros reiniciados correctamente.');
    }
}
