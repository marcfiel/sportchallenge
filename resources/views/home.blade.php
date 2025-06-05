@extends('layout')

@section('content')

@php
use Illuminate\Support\Facades\Auth;
@endphp

<div class="mt-4 sm:mt-6 md:mt-10">
    <h1 class="text-base sm:text-xl font-bold mb-6 hidden sm:block mt-6 text-end truncate">
        Bienvenido, {{ Auth::user()->firstname }}
    </h1>

    <h2 class="text-xl sm:text-2xl font-bold mb-4">Retos Activos</h2>

    @if($retosActivos->count())
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4 gap-6">
        @foreach($retosActivos->take(4) as $reto)
        @php
        if ($reto->tipo === 'diario') {
        $bgIcon = 'bg-black';
        $bgTitle = 'bg-black text-white';
        $iconColor = 'filter invert';
        $textColor = 'text-black';
        $textColor1 = 'text-white';
        $iconText = 'text-white';
        } else {
        $bgIcon = match($reto->deporte) {
        'correr' => 'bg-orange-200',
        'caminar' => 'bg-emerald-200',
        'bicicleta' => 'bg-blue-200',
        'ejercicio' => 'bg-purple-200',
        default => 'bg-gray-300'
        };
        $bgTitle = 'bg-emerald-400 text-black';
        $iconColor = 'filter brightness-0';
        $textColor = 'text-black';
        $textColor1 = 'text-black';
        $iconText = 'text-green-600';
        }

        $valor = $reto->objetivo_valor;
        $unidad = match($reto->objetivo_tipo) {
        'distancia' => 'km',
        'tiempo' => $valor == 1 ? 'hora' : 'horas',
        'sesiones' => $valor == 1 ? 'día' : 'días',
        };
        @endphp

        <a href="{{ route('retos.mostrar', ['id' => $reto->id, 'from' => 'home']) }}" class="block group">
            <div class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden flex">
                <div class="{{ $bgIcon }} w-1.5 rounded-l-xl"></div>

                {{-- Contenido del reto --}}
                <div class="flex-1 flex flex-col">
                    {{-- Cabecera --}}
                    <div class="{{ $bgTitle }} px-4 py-3 flex items-center space-x-3 rounded-tr-xl">
                        <img src="/img/icons/{{ $reto->tipo === 'diario' ? 'reto_diario' : $reto->deporte . '_reto' }}.svg"
                            alt="Tipo reto"
                            class="w-6 h-6 {{ $iconColor }} transform transition-transform duration-200 group-hover:scale-120">
                        <h3 class="text-md sm:text-md font-bold truncate {{ $textColor1 }}">
                            {{ $reto->nombre }}
                        </h3>

                    </div>

                    {{-- Detalles --}}
                    <div class="p-4 space-y-2">
                        <div class="flex items-center space-x-2 text-xs sm:text-sm {{ $textColor }}">
                            <img src="/img/icons/objetivo.svg" alt="icono objetivo"
                                class="w-4 h-4 {{ $iconText }} filter invert-0 sepia brightness-100 hue-rotate-[100deg]">
                            <span>{{ ucfirst($reto->deporte) }} {{ $valor }} {{ $unidad }}</span>
                        </div>
                        <div class="flex items-center space-x-2 text-xs sm:text-sm {{ $textColor }}">
                            <img src="/img/icons/fecha.svg" alt="icono fecha"
                                class="w-4 h-4 {{ $iconText }} filter invert-0 sepia brightness-100 hue-rotate-[100deg]">
                            <span>
                                {{ \Carbon\Carbon::parse($reto->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reto->fecha_fin)->format('d/m/Y') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </a>
        @endforeach
    </div>

    @if($retosActivos->count() > 4)
    <div class="text-center mt-6">
        <a href="{{ route('retos.misRetos', ['tab' => 'activos']) }}"
            class="inline-flex items-center justify-center bg-black text-white px-6 py-2 rounded hover:bg-gray-800 font-bold text-sm sm:text-base">
            <span class="text-lg sm:text-xl mr-2">+</span> Ver todos mis retos
        </a>
    </div>
    @endif

    @else
    <p class="text-gray-600 text-sm sm:text-base">No tienes retos activos.</p>
    @endif

    @if(isset($noticiasSeleccionadas) && !empty($noticiasSeleccionadas))
    <h2 class="text-xl sm:text-2xl font-bold mt-8 mb-4">Noticias</h2>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-x-4 gap-y-8">
        @foreach($noticiasSeleccionadas as $noticia)
        <div class="w-full max-w-lg border border-gray-300 p-4 rounded-lg shadow hover:shadow-lg transition">
            <a href="{{ route('noticia.mostrar', ['id' => $noticia['id']]) }}" target="_blank" class="text-black no-underline">
                <img src="{{ asset($noticia['imagen'] ?? 'img/noticias/placeholder.jpg') }}" alt="Imagen noticia"
                    class="w-full h-60 object-cover rounded">

                <h3 class="text-base sm:text-lg font-bold mt-2">{{ $noticia['titulo'] ?? 'Sin título' }}</h3>
                <p class="text-sm sm:text-base">{{ Str::limit($noticia['entradilla'] ?? 'Sin descripción disponible.', 100) }}</p>
            </a>
        </div>
        @endforeach
    </div>
    @else
    <p class="text-sm sm:text-base text-gray-600">No se encontraron noticias.</p>
    @endif
</div>

@endsection
