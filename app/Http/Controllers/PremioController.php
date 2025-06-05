<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Premio;
use App\Models\Canje;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class PremioController extends Controller
{
    public function index()
    {
        $usuario = Auth::user();
        // Se obtienen todos los premios disponibles y actuales (por fechas)
        $premios = Premio::where('disponible', 1)
            ->where(function ($query) {
                $now = now();
                $query->whereNull('fecha_inicio')->orWhere('fecha_inicio', '<=', $now);
            })
            ->where(function ($query) {
                $now = now();
                $query->whereNull('fecha_fin')->orWhere('fecha_fin', '>=', $now);
            })
            ->orderBy('puntos_necesarios', 'asc')
            ->get();

        // Se marcan y ordenan premios según si ya han sido canjeados o no
        $premiosOrdenados = $premios->map(function ($premio) use ($usuario) {
            $yaCanjeado = false;

            if ($usuario) {
                $yaCanjeado = Canje::where('user_id', $usuario->id)
                    ->where('recompensa_id', $premio->id)
                    ->exists();
            }

            $premio->yaCanjeado = $yaCanjeado;
            return $premio;
        })->sortBy('yaCanjeado')->values();

        return view('premios.index', compact('premiosOrdenados'));
    }

    public function mostrar($id, Request $request)
    {
        if ($request->has('from')) {
            session(['breadcrumb_from' => $request->query('from')]);
        }
        // Se busca el premio y se verifica si el usuario ya lo ha canjeado
        $premio = Premio::findOrFail($id);
        $usuario = Auth::user();

        $yaCanjeado = false;

        if ($usuario) {
            $yaCanjeado = Canje::where('user_id', $usuario->id)
                ->where('recompensa_id', $premio->id)
                ->exists();
        }

        return view('premios.detalle', compact('premio', 'yaCanjeado'));
    }

    public function canjear($id)
    {
        $user = User::findOrFail(Auth::id());
        $premio = Premio::findOrFail($id);

        $yaCanjeado = Canje::where('user_id', $user->id)
            ->where('recompensa_id', $premio->id)
            ->exists();

        if ($yaCanjeado) {
            return redirect()->back()->with('error', 'Ya has canjeado este premio.');
        }

        if ($user->puntos < $premio->puntos_necesarios) {
            return redirect()->back()->with('error', 'No tienes suficientes puntos para canjear este premio.');
        }

        $user->puntos -= $premio->puntos_necesarios;
        $user->save();

        Canje::create([
            'user_id' => $user->id,
            'recompensa_id' => $premio->id,
            'fecha_canje' => now(),
        ]);

        // Se asignan logros segun el numero de premios canjeados
        $canjes = DB::table('canjes')->where('user_id', $user->id)->count();

        if ($canjes >= 1) $this->asignarLogro($user->id, 10); // Primer canjeo
        if ($canjes >= 5) $this->asignarLogro($user->id, 11); // Fan de recompensas
        if ($canjes >= 10) $this->asignarLogro($user->id, 12); // Coleccionista

        return redirect()->route('premios.index')->with('success', 'Premio canjeado correctamente.');
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
}
