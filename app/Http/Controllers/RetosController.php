<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use App\Models\UsuariosReto;
use App\Models\User;
use App\Models\Reto;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RetosController extends Controller
{
    public function index(Request $request)
    {
        $hoy = now()->toDateString();
        $filtros = $request->query('filtros', []);

        // 1. Reto diario
        $retoDiario = Reto::where('tipo', 'diario')
            ->whereDate('fecha_inicio', $hoy)
            ->whereDate('fecha_fin', $hoy)
            ->first();

        // Detectar si el usuario ha completado el reto diario
        $completadoRetoDiario = false;

        if ($retoDiario && Auth::check()) {
            $usuarioId = Auth::id();

            $union = UsuariosReto::where('usuario_id', $usuarioId)
                ->where('reto_id', $retoDiario->id)
                ->first();

            $completadoRetoDiario = $union && $union->completado;
        }

        // 2. Recomendados para ti (guardar en sesión para persistencia)
        if (!session()->has('retos_recomendados_ids')) {
            $ids = Reto::whereIn('tipo', ['usuario', 'oficial'])
                ->whereDate('fecha_fin', '>=', $hoy)
                ->inRandomOrder()
                ->take(3)
                ->pluck('id')
                ->toArray();

            session(['retos_recomendados_ids' => $ids]);
        }

        $idsRecomendados = session('retos_recomendados_ids');
        $retosRecomendados = Reto::whereIn('id', $idsRecomendados)->get();

        // 3. Retos por categoría (sin excluir los recomendados)
        $deportes = ['correr', 'caminar', 'bicicleta', 'ejercicio'];
        $retosPorCategoria = [];

        foreach ($deportes as $deporte) {
            if (empty($filtros) || in_array($deporte, $filtros)) {
                $retosPorCategoria[$deporte] = Reto::where('deporte', $deporte)
                    ->whereIn('tipo', ['usuario', 'oficial'])
                    ->whereDate('fecha_fin', '>=', $hoy)
                    ->get();
            }
        }

        // Añadir información de unión a los retos si hay usuario autenticado
        if (Auth::check()) {
            $usuarioId = Auth::id();

            foreach ($retosRecomendados as $reto) {
                $reto->ya_unido = UsuariosReto::where('usuario_id', $usuarioId)
                    ->where('reto_id', $reto->id)
                    ->where('abandonado', 0)
                    ->exists();
            }

            foreach ($retosPorCategoria as $deporte => $retos) {
                foreach ($retos as $reto) {
                    $reto->ya_unido = UsuariosReto::where('usuario_id', $usuarioId)
                        ->where('reto_id', $reto->id)
                        ->where('abandonado', 0)
                        ->exists();
                }
            }
        }

        return view('retos.index', compact(
            'retoDiario',
            'retosRecomendados',
            'retosPorCategoria',
            'filtros',
            'completadoRetoDiario'
        ));
    }



    public function mostrar($id, Request $request)
    {
        $reto = Reto::findOrFail($id);
        $usuarioId = Auth::id();

        $retoUsuario = UsuariosReto::where('usuario_id', $usuarioId)
            ->where('reto_id', $id)
            ->first();

        $yaUnido = $retoUsuario !== null && $retoUsuario->abandonado == 0;
        $completado = $retoUsuario && $retoUsuario->completado;
        $abandonado = $retoUsuario && $retoUsuario->abandonado;

        // Guardamos el origen y pestaña (si aplica) en la sesión
        session([
            'breadcrumb_from' => $request->query('from'),
            'breadcrumb_tab' => $request->query('tab')
        ]);

        return view('retos.mostrar', compact('reto', 'yaUnido', 'completado', 'abandonado', 'retoUsuario'));
    }



    public function unirse($id)
    {
        $userId = Auth::id();

        $registro = DB::table('usuarios_retos')
            ->where('usuario_id', $userId)
            ->where('reto_id', $id)
            ->first();

        if ($registro) {
            if ($registro->abandonado == 1) {
                // Reactivar el reto si fue abandonado
                DB::table('usuarios_retos')
                    ->where('usuario_id', $userId)
                    ->where('reto_id', $id)
                    ->update([
                        'abandonado' => 0,
                        'completado' => 0,
                        'puntos_obtenidos' => 0,
                        'fecha_union' => now()
                    ]);

                return request()->expectsJson()
                    ? response()->json(['message' => 'Te has unido nuevamente al reto.'], 200)
                    : back()->with('status', 'Te has unido nuevamente al reto. Tu progreso ha sido reiniciado.');
            } else {
                return request()->expectsJson()
                    ? response()->json(['message' => 'Ya estás unido al reto'], 409)
                    : back()->with('status', 'Ya estás unido a este reto.');
            }
        }

        DB::table('usuarios_retos')->insert([
            'usuario_id' => $userId,
            'reto_id' => $id,
            'fecha_union' => now(),
            'completado' => 0,
            'abandonado' => 0,
            'puntos_obtenidos' => 0,
        ]);

        return request()->expectsJson()
            ? response()->json(['message' => 'Te has unido al reto'], 200)
            : back()->with('status', 'Te has unido al reto correctamente.');
    }



    public function progreso($id)
    {
        $usuario = Auth::user();

        if (!$usuario || !$usuario->strava_id) {
            return back()->with('status', 'Usuario no autenticado o sin ID de Strava.');
        }

        $tokens = DB::table('user_tokens')->where('strava_user_id', $usuario->strava_id)->first();
        if (!$tokens) {
            return back()->with('status', 'No se encontró token de Strava.');
        }

        $reto = Reto::findOrFail($id);

        if ($reto->fecha_inicio > now()) {
            return back()->with('status', 'Este reto aún no ha comenzado.');
        }

        $union = DB::table('usuarios_retos')
            ->where('usuario_id', $usuario->id)
            ->where('reto_id', $id)
            ->first();

        if (!$union) {
            return back()->with('status', 'No estás unido a este reto.');
        }

        $inicio = max(
            \Carbon\Carbon::parse($reto->fecha_inicio)->timestamp,
            \Carbon\Carbon::parse($union->fecha_union)->timestamp
        );

        $fin = \Carbon\Carbon::parse($reto->fecha_fin)->timestamp;

        $response = Http::withToken($tokens->access_token)
            ->get("https://www.strava.com/api/v3/athlete/activities", [
                'after' => $inicio,
                'before' => $fin,
                'per_page' => 100
            ]);

        if ($response->failed()) {
            return back()->with('status', 'Error al conectar con Strava.');
        }

        $actividades = $response->json();
        $total = 0;

        foreach ($actividades as $actividad) {
            switch ($reto->deporte) {
                case 'correr':
                    if (!in_array($actividad['type'], ['Run', 'TrailRun', 'Wheelchair'])) continue 2;
                    break;
                case 'caminar':
                    if (!in_array($actividad['type'], ['Walk', 'Snowshoe', 'Hike', 'Wheelchair'])) continue 2;
                    break;
                case 'bicicleta':
                    if (!in_array($actividad['type'], ['Ride', 'MountainBikeRide', 'Handcycle'])) continue 2;
                    break;
                case 'ejercicio':
                    $validos = [
                        'Workout',
                        'WeightTraining',
                        'Yoga',
                        'Crossfit',
                        'Pilates',
                        'Elliptical',
                        'StairStepper',
                        'HIIT',
                        'Swim',
                        'Rowing',
                        'Handcycle',
                        'Wheelchair',
                        'Walk',
                        'Run',
                        'Ride'
                    ];
                    if (!in_array($actividad['type'], $validos)) continue 2;
                    break;
            }

            switch ($reto->objetivo_tipo) {
                case 'distancia':
                    $total += $actividad['distance'] / 1000;
                    break;
                case 'tiempo':
                    $total += $actividad['moving_time'] / 3600;
                    break;
                case 'sesiones':
                    $total++;
                    break;
            }
        }

        $progreso = round($total, 2);

        if ($progreso >= $reto->objetivo_valor && !$union->completado) {
            // Calcular puntos ganados
            $puntosGanados = 0;

            if ($reto->tipo === 'usuario') {
                $puntosGanados = ($reto->puntos_apuesta ?? 0) * ($reto->multiplicador ?? 1);
            } else {
                $puntosGanados = $reto->puntos_recompensa ?? 0;
            }

            // Actualizar relación
            UsuariosReto::where('usuario_id', $usuario->id)
                ->where('reto_id', $id)
                ->update([
                    'completado' => 1,
                    'puntos_obtenidos' => $puntosGanados
                ]);

            // Asignar logros por retos completados
            $usuarioId = $usuario->id;

            // Total de retos completados
            $count = DB::table('usuarios_retos')
                ->where('usuario_id', $usuarioId)
                ->where('completado', 1)
                ->count();

            if ($count >= 1) $this->asignarLogro($usuarioId, 15);   // Primer reto completado
            if ($count >= 10) $this->asignarLogro($usuarioId, 16);
            if ($count >= 50) $this->asignarLogro($usuarioId, 17);
            if ($count >= 100) $this->asignarLogro($usuarioId, 18);

            if ($count == 1 || $count == 10 || $count == 50 || $count == 100) {
                return redirect()->route('retos.mostrar', ['id' => $id])
                    ->with('status', "Progreso actual: $progreso de {$reto->objetivo_valor}");
            }

            // Total de retos DIARIOS completados
            $countDiarios = DB::table('usuarios_retos')
                ->join('retos', 'usuarios_retos.reto_id', '=', 'retos.id')
                ->where('usuarios_retos.usuario_id', $usuarioId)
                ->where('usuarios_retos.completado', 1)
                ->where('retos.tipo', 'diario')
                ->count();

            if ($countDiarios >= 10) $this->asignarLogro($usuarioId, 19); // 10 diarios
            if ($countDiarios >= 25) $this->asignarLogro($usuarioId, 20); // 25 diarios


            // NUEVO: Guardar rango anterior
            $oldRango = $usuario->rango['nombre'] ?? null;

            // Sumar puntos al usuario
            $usuario->puntos += $puntosGanados;
            /** @var \App\Models\User $usuario */
            $usuario->save();

            // NUEVO: Evaluar cambio de rango
            $newRango = $usuario->rango['nombre'] ?? null;

            if ($oldRango && $newRango && $oldRango !== $newRango) {
                $this->asignarLogro($usuario->id, 13); // Cambio de rango
            }

            if ($newRango === 'Leyenda') {
                $this->asignarLogro($usuario->id, 14); // Rango legendario
            }
        }

        return back()->with('status', "Progreso actual: $progreso de {$reto->objetivo_valor}");
    }



    public function abandonar($id)
    {
        $userId = Auth::id();

        DB::table('usuarios_retos')
            ->where('usuario_id', $userId)
            ->where('reto_id', $id)
            ->update(['abandonado' => 1]);

        return request()->expectsJson()
            ? response()->json(['message' => 'Has abandonado el reto'], 200)
            : back()->with('error', 'Has abandonado el reto.');
    }




    public function crear()
    {
        $fechasOcupadas = \App\Models\Reto::where('tipo', 'diario')
            ->whereDate('fecha_inicio', '>=', now()->toDateString())
            ->orderBy('fecha_inicio')
            ->pluck('fecha_inicio')
            ->map(fn($fecha) => \Carbon\Carbon::parse($fecha)->format('d/m/Y'))
            ->toArray();

        return view('retos.crear', compact('fechasOcupadas'));
    }

    public function misRetos()
    {
        $usuario = Auth::user();

        // Retos creados por el usuario
        $retos = Reto::where('tipo', 'usuario')
            ->where('creador_id', $usuario->id)
            ->where('fecha_fin', '>=', now()) // Incluye retos activos hasta la hora actual
            ->orderBy('fecha_fin', 'asc')
            ->get();


        // Retos activos en los que participa (no completados ni abandonados)
        $retosActivos = DB::table('usuarios_retos')
            ->join('retos', 'usuarios_retos.reto_id', '=', 'retos.id')
            ->where('usuarios_retos.usuario_id', $usuario->id)
            ->where('usuarios_retos.abandonado', false)
            ->where('usuarios_retos.completado', false)
            ->whereDate('retos.fecha_fin', '>=', now())
            ->orderBy('retos.fecha_fin', 'asc')
            ->select('retos.*')
            ->get();


        // Historial de retos (completados o abandonados)
        $retosHistorial = DB::table('usuarios_retos')
            ->join('retos', 'usuarios_retos.reto_id', '=', 'retos.id')
            ->where('usuarios_retos.usuario_id', $usuario->id)
            ->where(function ($query) {
                $query->where('usuarios_retos.completado', true)
                    ->orWhere('usuarios_retos.abandonado', true)
                    ->orWhere(function ($q) {
                        $q->where('usuarios_retos.completado', false)
                            ->where('usuarios_retos.abandonado', false)
                            ->where('retos.fecha_fin', '<', now());
                    });
            })
            ->select(
                'usuarios_retos.*',
                'retos.nombre as nombre_reto',
                'retos.tipo as tipo_reto',
                'retos.fecha_inicio',
                'retos.fecha_fin',
                'retos.deporte',
                'retos.id as reto_id',
                'retos.objetivo_tipo',
                'retos.objetivo_valor',
                DB::raw("
            CASE 
                WHEN retos.objetivo_tipo = 'distancia' THEN CONCAT(retos.objetivo_valor, ' km')
                WHEN retos.objetivo_tipo = 'tiempo' THEN CONCAT(retos.objetivo_valor, ' ', IF(retos.objetivo_valor = 1, 'hora', 'horas'))
                WHEN retos.objetivo_tipo = 'sesiones' THEN CONCAT(retos.objetivo_valor, ' ', IF(retos.objetivo_valor = 1, 'día', 'días'))
                ELSE ''
            END as objetivo
        ")
            )
            ->orderBy('usuarios_retos.fecha_union', 'desc')
            ->limit(30)
            ->get();



        return view('retos.mis-retos', compact('retos', 'retosActivos', 'retosHistorial'));
    }

    public function store(Request $request)
    {
        $usuario = Auth::user();

        $request->merge([
            'puntos_apuesta' => ltrim($request->input('puntos_apuesta'), '0') ?: 0,
            'puntos_recompensa' => ltrim($request->input('puntos_recompensa'), '0') ?: 0,
        ]);

        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'required|string',
            'deporte' => 'required|in:correr,caminar,bicicleta,ejercicio',
            'tipo' => 'required|in:usuario,oficial,diario',
            'objetivo_tipo' => 'required|in:tiempo,distancia,sesiones',
            'objetivo_valor' => 'required|numeric|min:0.1|max:5000',
            'fecha_inicio' => 'required|date|after_or_equal:today',
            'fecha_fin' => 'required|date',
            'puntos_apuesta' => 'nullable|integer|min:0',
            'puntos_recompensa' => 'nullable|integer|min:0',
        ]);

        // Definir fechas
        $fechaInicio = Carbon::parse($validated['fecha_inicio']);
        $fechaFin = Carbon::parse($validated['fecha_fin'])->endOfDay();

        if ($validated['tipo'] === 'usuario' && $fechaInicio->isToday()) {
            $fechaInicio = now();
        } else {
            $fechaInicio = $fechaInicio->startOfDay();
        }

        if ($fechaFin->lt($fechaInicio)) {
            return back()->withInput()->with('error', 'La fecha de fin debe ser posterior o igual a la de inicio.');
        }

        // Duración real usada para validar tiempo y sesiones (basada en now() si empieza hoy)
        $duracionHoras = $fechaInicio->diffInHours($fechaFin);
        $duracionDias = $fechaInicio->diffInDays($fechaFin) + 1;

        // Para el cálculo del multiplicador usamos días completos (redondeados)
        $fechaInicioRedondeado = Carbon::parse($validated['fecha_inicio'])->startOfDay();
        $fechaFinRedondeado = Carbon::parse($validated['fecha_fin'])->endOfDay();
        $duracionDiasParaMultiplicador = $fechaInicioRedondeado->diffInDays($fechaFinRedondeado) + 1;


        switch ($validated['objetivo_tipo']) {
            case 'tiempo':
                if ($validated['objetivo_valor'] > $duracionHoras) {
                    return back()->withInput()->with('error', 'El objetivo de horas es mayor que la duración total del reto.');
                }
                break;
            case 'sesiones':
                if ($validated['objetivo_valor'] > $duracionDias) {
                    return back()->withInput()->with('error', 'El reto no puede durar más días de los que hay entre la fecha de inicio y fin.');
                }
                break;
        }

        if ($validated['tipo'] === 'diario') {
            $yaExiste = Reto::where('tipo', 'diario')
                ->whereDate('fecha_inicio', $fechaInicio)
                ->exists();

            if ($yaExiste) {
                return back()->withInput()->with('error', 'Ya existe un reto diario para ese día.');
            }
        }

        if ($usuario->role !== 'admin') {
            $validated['tipo'] = 'usuario';
        }

        $validated['puntos_apuesta'] = $validated['puntos_apuesta'] ?? 0;
        $validated['puntos_recompensa'] = $validated['puntos_recompensa'] ?? 0;

        // Validación específica para retos de tipo "usuario"
        if ($validated['tipo'] === 'usuario') {
            if ($validated['puntos_apuesta'] > 0) {
                $rango = $usuario->rango['nombre'];
                $limite = match ($rango) {
                    'Novato' => 50,
                    'Constante' => 100,
                    'Proactivo' => 150,
                    'Leyenda' => 200,
                    default => 50,
                };

                if ($validated['puntos_apuesta'] > $limite) {
                    return back()->withInput()->with('error', "Como $rango solo puedes apostar hasta $limite puntos.");
                }

                if ($usuario->puntos < $validated['puntos_apuesta']) {
                    return back()->withInput()->with('error', 'No tienes suficientes puntos para crear este reto.');
                }

                $usuario->puntos -= $validated['puntos_apuesta'];
                /** @var \App\Models\User $usuario */
                $usuario->save();
            }

            $validated['puntos_recompensa'] = 0;
        }

        if (in_array($validated['tipo'], ['oficial', 'diario'])) {
            $validated['puntos_apuesta'] = 0;
        }

        $validated['fecha_inicio'] = $fechaInicio;
        $validated['fecha_fin'] = $fechaFin;
        $validated['creador_id'] = $usuario->id;
        $validated['multiplicador'] = $this->calcularMultiplicador(
            $validated['objetivo_tipo'],
            $validated['objetivo_valor'],
            $duracionDiasParaMultiplicador
        );

        $retoDuplicado = Reto::where('nombre', $validated['nombre'])
            ->where('deporte', $validated['deporte'])
            ->exists();

        if ($retoDuplicado) {
            return back()->withInput()->with('error', 'Ya existe un reto con ese nombre para ese deporte.');
        }

        $reto = Reto::create($validated);

        // Luego de guardar el reto, asignar logros
        // Evaluar logros tras crear reto
        $usuarioId = $usuario->id;

        // Logros por número total de retos creados
        $totalRetos = DB::table('retos')->where('creador_id', $usuarioId)->count();

        if ($totalRetos >= 1) $this->asignarLogro($usuarioId, 1);
        if ($totalRetos >= 10) $this->asignarLogro($usuarioId, 2);
        if ($totalRetos >= 50) $this->asignarLogro($usuarioId, 3);
        if ($totalRetos >= 100) $this->asignarLogro($usuarioId, 4);

        // Logros por tipo de reto creado (por deporte)
        $deportes = ['correr', 'bicicleta', 'caminar', 'ejercicio'];

        foreach ($deportes as $i => $deporte) {
            $count = DB::table('retos')
                ->where('creador_id', $usuarioId)
                ->where('deporte', $deporte)
                ->count();

            if ($count >= 10) $this->asignarLogro($usuarioId, 5 + $i); // logros 5, 6, 7, 8
        }

        //Logro "Retador completo" (20 de cada tipo)
        $completo = collect($deportes)->every(function ($dep) use ($usuarioId) {
            return DB::table('retos')
                ->where('creador_id', $usuarioId)
                ->where('deporte', $dep)
                ->count() >= 20;
        });

        if ($completo) $this->asignarLogro($usuarioId, 9);



        return redirect()->route('retos.mostrar', $reto->id)
            ->with('success', 'Reto creado correctamente.');
    }


    private function asignarLogro($usuarioId, $logroId)
    {
        $yaLoTiene = DB::table('usuarios_logros')
            ->where('usuario_id', $usuarioId)
            ->where('logro_id', $logroId)
            ->exists();

        if (!$yaLoTiene) {
            DB::table('usuarios_logros')->insert([
                'usuario_id' => $usuarioId,
                'logro_id' => $logroId,
                'fecha_conseguido' => now()
            ]);
        }
    }


    private function calcularMultiplicador($tipo, $valor, $diasDisponibles)
    {
        $valorPorDia = $valor / $diasDisponibles;

        switch ($tipo) {
            case 'distancia':
                if ($valorPorDia >= 18) return 5;
                if ($valorPorDia >= 12) return 3;
                if ($valorPorDia >= 6) return 2;
                return 1;

            case 'tiempo':
                if ($valorPorDia >= 5) return 5;
                if ($valorPorDia >= 3) return 3;
                if ($valorPorDia >= 2) return 2;
                return 1;

            case 'sesiones':
                $porcentaje = $valor / $diasDisponibles;

                if ($diasDisponibles >= 7 && $porcentaje >= 0.80) return 5;
                if ($diasDisponibles >= 6 && $porcentaje >= 0.50) return 3;
                if ($diasDisponibles >= 5 && $porcentaje >= 0.30) return 2;
                return 1;

            default:
                return 1;
        }
    }




    public function eliminar($id)
    {
        $usuario = Auth::user();
        $reto = Reto::findOrFail($id);

        // Admin puede eliminar cualquier reto
        if ($usuario->role === 'admin') {
            $reto->delete();
            return redirect()->route('retos.index')->with('success', 'Reto eliminado correctamente.');
        }

        if ($reto->creador_id === $usuario->id && $reto->tipo === 'usuario') {
            $reto->delete();
            return redirect()->route('retos.index')->with('success', 'Reto eliminado correctamente.');
        }

        return redirect()->back()->with('error', 'No tienes permiso para eliminar este reto.');
    }
}
