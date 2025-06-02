@extends('layout')

@section('content')

@php
$from = session('breadcrumb_from');
$tab = session('breadcrumb_tab');
$breadcrumbLabel = match($from) {
'home' => 'Inicio',
'mis-retos' => 'Mis Retos',
default => 'Retos'
};

$breadcrumbRoute = match($from) {
'home' => route('home'),
'mis-retos' => route('retos.misRetos', $tab ? ['tab' => $tab] : []),
default => route('retos.index')
};

// Limitar el nombre largo del reto a 40 caracteres
$nombreRetoCorto = \Illuminate\Support\Str::limit($reto->nombre, 40);
@endphp

{{-- Breadcrumb centrado debajo del logo --}}
<div class="mt-5">
    <div class="text-sm text-gray-600 flex items-center space-x-2 overflow-hidden">
        <a href="{{ $breadcrumbRoute }}" class="text-green-600 hover:underline font-semibold shrink-0">
            {{ $breadcrumbLabel }}
        </a>
        <span class="text-gray-400 shrink-0">/</span>
        <span class="text-gray-500 truncate block max-w-[220px]">
            {{ $nombreRetoCorto }}
        </span>
    </div>
</div>


@php
$hoy = \Carbon\Carbon::today();
$fechaFin = \Carbon\Carbon::parse($reto->fecha_fin);
$noSuperado = $yaUnido && !$completado && !$abandonado && $fechaFin->isPast();

$esDiario = $reto->tipo === 'diario';

$bgIcon = $esDiario ? 'bg-black' : match($reto->deporte) {
'correr' => 'bg-orange-200',
'caminar' => 'bg-emerald-200',
'bicicleta' => 'bg-blue-200',
'ejercicio' => 'bg-purple-200',
default => 'bg-gray-300'
};

$bgTitulo = $esDiario ? 'bg-black text-white' : 'bg-emerald-400 text-black';
$icono = $esDiario ? 'reto_diario.svg' : "{$reto->deporte}_reto.svg";
$invertirIcono = $esDiario ? 'invert' : 'filter brightness-0';

$bgEstado = $completado ? 'bg-green-50 border border-green-300' : ($noSuperado ? 'bg-red-50 border border-red-300' : 'bg-white');
@endphp
<div class="max-w-4xl mx-auto mt-5 shadow-md rounded-lg overflow-hidden flex">
    {{-- Línea lateral (más ancha y sin romper el diseño) --}}
    <div class="{{ $bgIcon }} w-4 rounded-l-lg"></div>

    {{-- Contenido principal --}}
    <div class="flex-1 {{ $bgEstado }}">
        {{-- Título con icono --}}
        <div class="{{ $bgTitulo }} px-6 py-5 flex items-center space-x-4 mb-8">
            <img src="/img/icons/{{ $icono }}" alt="icono" class="w-12 h-12 {{ $invertirIcono }}">
            <h1 class="text-2xl font-bold truncate">{{ $reto->nombre }}</h1>
        </div>


        {{-- Mensaje de estado --}}
        @if(session('status') && preg_match('/Progreso actual: ([0-9.]+) de ([0-9.]+)/', session('status'), $matches) && count($matches) === 3)
        @php
        $progreso = floatval($matches[1]);
        $objetivo = floatval($matches[2]);
        $unidad = match($reto->objetivo_tipo) {
        'distancia' => 'km',
        'tiempo' => $objetivo == 1 ? 'hora' : 'horas',
        'sesiones' => $objetivo == 1 ? 'día' : 'días',
        default => ''
        };
        $porcentaje = min(100, round(($progreso / $objetivo) * 100));
        $claseAncho = match (true) {
        $porcentaje >= 100 => 'w-full',
        $porcentaje >= 90 => 'w-11/12',
        $porcentaje >= 80 => 'w-10/12',
        $porcentaje >= 70 => 'w-9/12',
        $porcentaje >= 60 => 'w-8/12',
        $porcentaje >= 50 => 'w-7/12',
        $porcentaje >= 40 => 'w-6/12',
        $porcentaje >= 30 => 'w-5/12',
        $porcentaje >= 20 => 'w-4/12',
        $porcentaje >= 10 => 'w-3/12',
        $porcentaje >= 5 => 'w-2/12',
        $porcentaje > 0 => 'w-1/12',
        default => 'w-0',
        };
        @endphp

        <div class="bg-gray-100 text-black rounded-md px-10 py-4 mb-8 mx-10">
            <p class="font-semibold text-sm mb-2">
                Progreso actual: <span class="font-normal">{{ $progreso }} de {{ $objetivo }} {{ $unidad }}</span>
            </p>
            <div class="w-full bg-gray-300 rounded overflow-hidden h-5">
                <div class="bg-black h-full text-xs font-bold text-white text-center {{ $claseAncho }}">
                    {{ $porcentaje }}%
                </div>
            </div>
        </div>
        @endif





        {{-- Botones lateral --}}
        <div class="relative mb-6">
            <div class="absolute top-10 right-10 flex flex-col items-end space-y-2">
                {{-- RETO COMPLETADO --}}
                @if($yaUnido && $completado)
                <span class="bg-green-100 text-green-800 px-4 py-2 text-lg font-semibold rounded flex items-center space-x-2">
                    <img src="/img/icons/reto_superado.svg" class="w-10 h-10" alt="Meta">
                    <span>Has completado este reto</span>
                </span>

                {{-- RETO NO SUPERADO --}}
                @elseif($yaUnido && $noSuperado)
                <span class="bg-red-100 text-red-800 px-4 py-2 text-lg font-semibold rounded inline-flex items-center space-x-2">
                    <img src="/img/icons/stop.svg" alt="Icono stop" class="w-10 h-10">
                    <span>No superado a tiempo</span>
                </span>


                {{-- RETO EN CURSO --}}
                @elseif($yaUnido)
                {{-- Botón ACTUALIZAR PROGRESO --}}
                @if(\Carbon\Carbon::parse($reto->fecha_fin)->isFuture())
                <a href="{{ route('retos.progreso', $reto->id) }}"
                    class="bg-blue-100 text-blue-800 px-4 py-2 rounded hover:bg-blue-200 text-sm font-semibold transition">
                    Actualizar progreso
                </a>
                @endif

                {{-- Botón ABANDONAR --}}
                @if($reto->tipo !== 'diario')
                <form method="POST" action="{{ route('retos.abandonar', $reto->id) }}">
                    @csrf
                    <button type="submit"
                        class="bg-white text-black border border-black px-4 py-2 rounded hover:bg-gray-100 transition text-sm font-semibold transition cursor-pointer">
                        Abandonar reto
                    </button>
                </form>
                @endif

                {{-- UNIRSE AL RETO --}}
                @else
                @php
                $esHoy = \Carbon\Carbon::parse($reto->fecha_inicio)->toDateString() === \Carbon\Carbon::today()->toDateString();
                @endphp
                @if($reto->tipo !== 'diario' || $esHoy)
                <form method="POST" action="{{ route('retos.unirse', $reto->id) }}">
                    @csrf
                    <button type="submit"
                        class="bg-black text-white px-6 py-2 text-lg font-bold rounded hover:bg-gray-800 transition cursor-pointer">
                        Unirse al reto
                    </button>
                </form>
                @endif
                @endif
            </div>



            {{-- Info principal --}}
            <div class="space-y-4 px-10 mt-10 mb-10">
                <div class="flex items-center space-x-3">
                    <img src="/img/icons/fecha.svg" alt="Icono fecha" class="w-6 h-6">
                    <p class="text-lg">
                        {{ \Carbon\Carbon::parse($reto->fecha_inicio)->format('d/m/Y') }} -
                        {{ \Carbon\Carbon::parse($reto->fecha_fin)->format('d/m/Y') }}
                    </p>
                </div>

                @php
                $valor = $reto->objetivo_valor;
                $unidad = match($reto->objetivo_tipo) {
                'distancia' => 'km',
                'tiempo' => $valor == 1 ? 'hora' : 'horas',
                'sesiones' => $valor == 1 ? 'día' : 'días',
                };
                @endphp

                <div class="flex items-center space-x-3">
                    <img src="/img/icons/objetivo.svg" alt="Icono objetivo" class="w-6 h-6">
                    <p class="text-lg">
                        {{ $valor }} {{ $unidad }}
                    </p>
                </div>

                <div class="flex items-center space-x-3">
                    <img src="/img/icons/trofeo.svg" alt="Icono trofeo" class="w-6 h-6">

                    @php
                    // Asignamos clases según el multiplicador entero
                    $coloresMultiplicador = [
                    2 => 'bg-blue-100 text-blue-800',
                    3 => 'bg-orange-100 text-orange-800',
                    5 => 'bg-red-100 text-red-800',
                    ];

                    $colorClase = $coloresMultiplicador[$reto->multiplicador] ?? 'bg-gray-100 text-gray-800';
                    $colorTexto = str_replace('bg-', 'text-', explode(' ', $colorClase)[0]);

                    $puntosFinales = $reto->puntos_apuesta * $reto->multiplicador;
                    @endphp

                    @if($reto->tipo === 'usuario')
                    <div class="flex items-center space-x-2">
                        @if($reto->multiplicador > 1)
                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $colorClase }}">
                            ×{{ $reto->multiplicador }}
                        </span>
                        @endif

                        <span class="text-lg font-semibold {{ $colorTexto }}">
                            {{ number_format($puntosFinales, 0, ',', '.') }} pts
                        </span>
                    </div>
                    @else
                    <p class="text-lg text-green-700 font-semibold">
                        {{ number_format($reto->puntos_recompensa, 0, ',', '.') }} pts
                    </p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Descripción --}}
        @if($reto->descripcion)
        <div class="mb-6 px-10">
            <h2 class="text-xl font-semibold mb-2">Descripción</h2>
            <p class="text-gray-700 break-words">{{ $reto->descripcion }}</p>
        </div>
        @endif

        {{-- Actividades válidas --}}
        <div class="mb-6 px-10">
            <div class="text-sm text-green-600 leading-6">
                <strong class="text-gray-700">Actividades válidas:</strong>
                @switch($reto->deporte)
                @case('correr') Carrera, Carrera de montaña, Silla de ruedas @break
                @case('caminar') Caminata, Raquetas de nieve, Senderismo, Silla de ruedas @break
                @case('bicicleta') Bicicleta, Bicicleta de montaña, Handbike @break
                @case('ejercicio') Entrenamiento, Pesas, Yoga, Crossfit, Pilates, Elíptica, Escaleras, HIIT, Natación, Remo, Handbike, Silla de ruedas, Caminar, Correr, Bicicleta @break
                @default No definido
                @endswitch
            </div>
        </div>

        {{-- Eliminar --}}
        @if(Auth::user()->id === $reto->creador_id && $reto->tipo === 'usuario' || Auth::user()->role === 'admin')
        <div class="px-10 pb-6">
            <form id="form-eliminar-reto" action="{{ route('retos.eliminar', $reto->id) }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="button" id="btn-eliminar-reto" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 mt-2 cursor-pointer">
                    Eliminar reto
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btnEliminar = document.getElementById('btn-eliminar-reto');
        const formEliminar = document.getElementById('form-eliminar-reto');

        if (btnEliminar && formEliminar) {
            btnEliminar.addEventListener('click', function(e) {
                e.preventDefault();
                Swal.fire({
                    title: '¿Eliminar reto?',
                    text: 'Esta acción no se puede deshacer.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#aaa',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        formEliminar.submit();
                    }
                });
            });
        }
    });
</script>

@if(session('success'))
<script>
    Toastify({
        text: `
        <div style="display:flex;align-items:center;">
            <svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
        </div>`,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#10b981",
        stopOnFocus: true,
        escapeMarkup: false
    }).showToast();
</script>
@endif


@if(session('status') && !preg_match('/Progreso actual: ([0-9.]+) de ([0-9.]+)/', session('status')))
<script>
    Toastify({
        text: `
        <div style="display:flex;align-items:center;">
            <svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('status') }}</span>
        </div>`,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#10b981",
        stopOnFocus: true,
        escapeMarkup: false
    }).showToast();
</script>
@endif

@if(session('error'))
<script>
    Toastify({
        text: `
        <div style="display:flex;align-items:center;">
            <svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" stroke-width="2"
                 viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H5.25A2.25 2.25 0 003 5.25v13.5A2.25 2.25 0 005.25 21H13.5a2.25 2.25 0 002.25-2.25V15" />
              <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m0 0l3-3m-3 3l3 3" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>`,
        duration: 3000,
        gravity: "top",
        position: "right",
        backgroundColor: "#ef4444",
        stopOnFocus: true,
        escapeMarkup: false
    }).showToast();
</script>
@endif



@endsection