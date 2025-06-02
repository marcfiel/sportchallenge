@extends('layout')

@section('content')
<div class="text-xs sm:text-sm md:text-base mt-5">

    {{-- Encabezado con breadcrumb (escritorio) o icono atrás (móvil), título y puntos --}}
    <div class="w-full grid grid-cols-3 items-center mb-6">

        {{-- IZQUIERDA: Icono atrás solo en móvil --}}
        <div class="text-left">
            <a href="{{ route('actividad') }}"
               class="md:hidden text-green-600 hover:underline font-semibold flex items-center gap-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none"
                     viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M15 19l-7-7 7-7"/>
                </svg>
                Atrás
            </a>

            {{-- Breadcrumb visible solo en escritorio --}}
            <div class="hidden md:flex text-sm text-gray-600 items-center space-x-2 truncate">
                <a href="{{ route('actividad') }}" class="text-green-600 hover:underline font-semibold">
                    Mi Actividad
                </a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-500 truncate max-w-[200px] block">Catálogo de premios</span>
            </div>
        </div>

        {{-- TÍTULO CENTRADO --}}
        <h1 class="text-base sm:text-xl md:text-2xl font-bold text-center text-gray-800">
            Catálogo de premios
        </h1>

        {{-- PUNTOS --}}
        <div class="text-sm sm:text-base font-bold text-green-600 text-right">
            {{ number_format(Auth::user()->puntos ?? 0, 0, ',', '.') }} pts
        </div>
    </div>

    {{-- Premios --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
        @forelse ($premiosOrdenados as $premio)
        <a href="{{ route('premios.mostrar', ['id' => $premio->id, 'from' => 'premios']) }}"
           class="relative bg-white border rounded-lg p-4 shadow hover:shadow-lg transition cursor-pointer block">

            {{-- Badge por encima del contenido --}}
            @if ($premio->yaCanjeado)
            <div class="absolute top-2 right-2 z-20 bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-bold shadow">
                CANJEADO
            </div>
            @endif

            {{-- Contenido con opacidad si está canjeado --}}
            <div class="{{ $premio->yaCanjeado ? 'opacity-30' : '' }}">
                <img src="{{ $premio->imagen }}" alt="{{ $premio->nombre }}"
                     class="w-full h-40 object-cover rounded mb-3">
                <h2 class="text-base sm:text-lg font-semibold text-gray-900">{{ $premio->nombre }}</h2>
                <p class="text-xs sm:text-sm text-gray-600 mt-1">{{ $premio->descripcion }}</p>
                <p class="text-green-700 font-bold mt-2">
                    {{ number_format($premio->puntos_necesarios, 0, ',', '.') }} puntos
                </p>
            </div>
        </a>
        @empty
        <p class="text-gray-600">No hay premios disponibles en este momento.</p>
        @endforelse
    </div>
</div>

<!-- Toastify -->
<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

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
@endsection
