@extends('layout')

@section('content')
<div class="mt-5 text-xs sm:text-sm md:text-base">

    {{-- BLOQUE UNIFICADO: RANGO + PROGRESO + PUNTOS --}}
    @php
    $rango = $usuario->rango;
    $puntosActuales = $usuario->puntos;
    $puntosSiguiente = $rango['siguiente'] ?? null;

    // Determinar el rango base
    $rangoBase = match ($rango['nombre']) {
    'Novato' => 0,
    'Constante' => 15000,
    'Proactivo' => 30000,
    default => 60000, // Leyenda o cualquier otro
    };

    // Evitar divisiones por cero o nulas
    if ($puntosSiguiente && $puntosSiguiente > $rangoBase) {
    $puntosParaSubir = max(0, $puntosSiguiente - $puntosActuales);

    // Calcula el porcentaje de progreso para el sigueinte rango
    $progreso = min(100, intval((($puntosActuales - $rangoBase) / ($puntosSiguiente - $rangoBase)) * 100));
    } else {
    $puntosParaSubir = null;
    $progreso = 100;
    }

    // Color
    $colorTexto = $rango['color'] ?? 'text-gray-400';
    $coloresBarra = [
    'Novato' => 'bg-green-600',
    'Constante' => 'bg-blue-600',
    'Proactivo' => 'bg-purple-600',
    'Leyenda' => 'bg-yellow-600',
    ];

    // Asigna un color visual a la barra según el rango del usuario
    $colorBarra = $coloresBarra[$rango['nombre']] ?? 'bg-gray-400';

    // Asigna una clase de ancho a la barra según el progreso
    if ($progreso >= 100) $claseAncho = 'w-full';
    elseif ($progreso >= 90) $claseAncho = 'w-11/12';
    elseif ($progreso >= 80) $claseAncho = 'w-10/12';
    elseif ($progreso >= 70) $claseAncho = 'w-9/12';
    elseif ($progreso >= 60) $claseAncho = 'w-8/12';
    elseif ($progreso >= 50) $claseAncho = 'w-7/12';
    elseif ($progreso >= 40) $claseAncho = 'w-6/12';
    elseif ($progreso >= 30) $claseAncho = 'w-5/12';
    elseif ($progreso >= 20) $claseAncho = 'w-4/12';
    elseif ($progreso >= 10) $claseAncho = 'w-3/12';
    elseif ($progreso >= 5) $claseAncho = 'w-2/12';
    elseif ($progreso > 0) $claseAncho = 'w-1/12';
    else $claseAncho = 'w-0';
    @endphp



    <div class="bg-gray-100 p-6 rounded-xl mb-8 flex flex-col md:flex-row md:justify-between md:items-center gap-6 shadow-sm">
        {{-- Izquierda: Rango y progreso --}}
        <div class="flex-1">
            <p class="text-gray-700 font-medium">Rango actual:</p>
            <p class="text-lg font-bold {{ $rango['color'] }}">{{ $rango['nombre'] }}</p>

            @if ($puntosParaSubir !== null)
            <p class="text-sm text-gray-500">
                Siguiente rango en <strong>{{ number_format($puntosParaSubir, 0, ',', '.') }}</strong> puntos
            </p>

            {{-- Contenedor con ancho limitado --}}
            <div class="w-full max-w-4xl bg-gray-300 rounded-full h-3 mt-2 overflow-hidden">
                <div class="h-3 rounded-full transition-all duration-300 {{ $claseAncho }} {{ $colorBarra }}"></div>
            </div>
            <p class="text-[10px] sm:text-xs md:text-sm text-gray-500 mt-4">
                Tu rango y progreso actual se basan en los puntos que tienes disponibles. Canjear premios puede hacerte descender de nivel.
            </p>
            @else
            <p class="text-sm text-gray-500 mt-1">¡Has alcanzado el rango máximo! 🏆</p>
            @endif
        </div>

        {{-- Derecha: Puntos --}}
        <div class="text-center md:text-right mt-6 md:mt-0">
            <div class="text-xl sm:text-2xl font-bold text-gray-800 mb-1">Puntos Totales</div>
            <div class="text-xl font-bold text-green-600">{{ number_format($puntosActuales, 0, ',', '.') }} pts</div>
        </div>
    </div>



    {{-- SECCIÓN PREMIOS DESTACADOS --}}
    <div class="bg-white p-6 rounded-lg shadow mb-10">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold text-gray-800">Premios destacados</h2>
            <a href="{{ route('premios.index') }}" class="text-sm text-green-600 hover:underline flex items-center gap-1">
                Ver todos los premios
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            @forelse ($premiosDestacados as $premio)
            <a href="{{ route('premios.mostrar', ['id' => $premio->id, 'from' => 'actividad']) }}" class="border rounded-lg p-2 bg-gray-50 hover:shadow-lg transition cursor-pointer block">
                <img src="{{ asset($premio->imagen) }}" alt="{{ $premio->nombre }}" class="rounded w-full h-32 object-cover">
                <h3 class="font-medium mt-2 text-gray-800 text-sm">{{ $premio->nombre }}</h3>
                <p class="text-xs text-green-700 mt-1">{{ number_format($premio->puntos_necesarios, 0, ',', '.') }} puntos</p>
            </a>
            @empty
            <p class="text-gray-600 text-sm">No hay premios disponibles por ahora.</p>
            @endforelse
        </div>
    </div>


    {{-- SECCIÓN COMPLETA: MIS ESTADÍSTICAS --}}
    <div class="bg-gray-100 p-6 rounded-lg mb-10">

        {{-- Título + Filtro --}}
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">Mis estadísticas</h2>

            <div class="flex gap-4 mb-8 mt-8">
                {{-- Define las opciones de filtro de estadísticas por tipo de deporte--}}
                @php $deportes = ['correr' => '🏃‍♂️', 'caminar' => '🚶‍♂️', 'bicicleta' => '🚴‍♂️']; @endphp

                @foreach ($deportes as $deporte => $icono)
                @php
                $activo = $deporteElegido === $deporte;
                $clases = $activo
                ? 'bg-black text-white'
                : 'bg-gray-200 text-black hover:bg-green-200 hover:text-green-800';
                @endphp
                <button
                    type="button"
                    class="filtro-deporte px-4 py-2 rounded cursor-pointer {{ $clases }}"
                    data-deporte="{{ $deporte }}">
                    {{ $icono }} {{ ucfirst($deporte) }}
                </button>
                @endforeach
            </div>
        </div>


        {{-- Contenido: Últimas 4 semanas + Totales en columnas --}}
        <div class="grid md:grid-cols-2 gap-6">

            {{-- Últimas 4 semanas --}}
            <div>
                <h3 class="text-lg font-semibold mb-3 text-gray-700">Últimas 4 semanas</h3>
                <div class="space-y-3">
                    <div class="flex justify-between bg-white p-3 rounded shadow-sm">
                        <span class="text-gray-600">Actividades / Semana</span>
                        <span class="font-medium">{{ $estadisticas4Semanas['actividades'] }}</span>
                    </div>
                    <div class="flex justify-between bg-white p-3 rounded shadow-sm">
                        <span class="text-gray-600">Distancia (promedio/semana)</span>
                        <span class="font-medium">{{ $estadisticas4Semanas['distancia'] }} km</span>
                    </div>
                    <div class="flex justify-between bg-white p-3 rounded shadow-sm">
                        <span class="text-gray-600">Tiempo (promedio/semana)</span>
                        <span class="font-medium">{{ $estadisticas4Semanas['tiempo'] }}</span>
                    </div>
                    <div class="flex justify-between bg-white p-3 rounded shadow-sm">
                        <span class="text-gray-600">Desnivel positivo / Semana</span>
                        <span class="font-medium">{{ number_format($estadisticas4Semanas['desnivel'], 2) }} m</span>
                    </div>
                </div>
            </div>

            {{-- Totales --}}
            <div class="bg-green-200 p-4 rounded-lg shadow-md">
                <h3 class="text-lg font-bold mb-3 text-green-900">Totales</h3>
                <div class="space-y-2">
                    <div class="flex justify-between bg-white p-3 rounded">
                        <span class="font-semibold text-gray-700">Actividades</span>
                        <span class="font-bold text-green-900">{{ $totales['actividades'] }}</span>
                    </div>
                    <div class="flex justify-between bg-white p-3 rounded">
                        <span class="font-semibold text-gray-700">Distancia</span>
                        <span class="font-bold text-green-900">
                            {{ is_numeric($totales['distancia']) ? number_format($totales['distancia'], 2) : $totales['distancia'] }} km
                        </span>
                    </div>
                    <div class="flex justify-between bg-white p-3 rounded">
                        <span class="font-semibold text-gray-700">Tiempo</span>
                        <span class="font-bold text-green-900">{{ $totales['tiempo'] }}</span>
                    </div>
                    <div class="flex justify-between bg-white p-3 rounded">
                        <span class="font-semibold text-gray-700">Desnivel positivo</span>
                        <span class="font-bold text-green-900">
                            {{ is_numeric($totales['desnivel']) ? number_format($totales['desnivel'], 2) : $totales['desnivel'] }} m
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- LOGROS --}}
    <div x-data="{ abiertoLogros: true }" class="mt-6">

        <div class="group flex items-center justify-between mb-4 cursor-pointer select-none"
            @click="abiertoLogros = !abiertoLogros">
            <h2 class="text-2xl font-bold">🏅 Logros</h2>
            <svg :class="{ 'rotate-180': abiertoLogros }"
                class="w-6 h-6 transition-transform duration-300 text-gray-700 group-hover:text-emerald-600"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div x-show="abiertoLogros" x-transition>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                @foreach ($logros as $logro)
                <div class="text-center bg-white p-3 rounded-lg shadow cursor-pointer" title="{{ $logro->descripcion }}">
                    <img
                        src="{{ asset('img/logros/' . $logro->imagen) }}"
                        alt="{{ $logro->nombre }}"
                        class="w-16 h-16 mx-auto transition duration-300 
                       {{ $logro->conseguido ? '' : 'grayscale opacity-30' }}">

                    <p class="mt-2 font-semibold 
                  {{ $logro->conseguido ? 'text-green-700' : 'text-gray-400' }}">
                        {{ $logro->nombre }}
                    </p>

                    @if ($logro->conseguido)
                    <p class="text-sm text-gray-500">
                        Conseguido: {{ \Carbon\Carbon::parse($logro->fecha_conseguido)->format('d/m/Y') }}
                    </p>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- Botón de reiniciar logros para administradores --}}
            @if(Auth::user()->role === 'admin')
            <button id="btn-reiniciar-logros" class="bg-red-600 text-white px-4 py-2 rounded hover:bg-red-700 mt-6 cursor-pointer">
                Reiniciar logros
            </button>

            <form id="form-reiniciar-logros" method="POST" action="{{ route('actividad.reiniciarLogros', $usuario->id) }}" class="hidden">
                @csrf
            </form>
            @endif
        </div>

    </div>


    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('btn-reiniciar-logros');
            const form = document.getElementById('form-reiniciar-logros');

            if (btn && form) {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();

                    // Lanza una alerta de tipo confirmación antes de reiniciar todos los logros (solo admin)
                    Swal.fire({
                        title: '¿Reiniciar todos los logros?',
                        text: 'Esta acción eliminará todos los logros conseguidos. ¿Estás seguro?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Sí, reiniciar',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            }
        });
    </script>



    <script>
        // Permite filtrar las estadísticas por deporte y actualiza la página con el nuevo valor
        document.querySelectorAll('.filtro-deporte').forEach(button => {
            button.addEventListener('click', function() {
                const deporte = this.getAttribute('data-deporte');
                const url = new URL(window.location.href);
                url.searchParams.set('deporte', deporte);
                history.pushState({}, '', url);
                location.reload();
            });
        });
    </script>


    @endsection