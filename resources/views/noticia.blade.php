@extends('layout')

@section('content')
<div class="flex flex-col items-center mt-6 px-4 sm:px-6 md:px-0 text-sm sm:text-base">

    {{-- Título --}}
    <h1 class="text-xl sm:text-2xl font-bold text-center mb-2">{{ $noticia['titulo'] }}</h1>

    {{-- Entradilla --}}
    <p class="text-base sm:text-lg text-center text-gray-600 mb-4">{{ $noticia['entradilla'] }}</p>

    {{-- Línea separadora --}}
    <div class="w-full sm:w-4/5 md:w-3/5 border-b-2 border-gray-300 mb-6"></div>

    {{-- Imagen centrada con aspecto 14:9 --}}
    <div class="w-full sm:w-4/5 md:w-3/5 aspect-[14/9] mb-6 rounded overflow-hidden shadow">
        <img src="{{ asset($noticia['imagen']) }}"
             alt="Imagen noticia"
             class="w-full h-full object-cover">
    </div>

    {{-- Texto principal --}}
    <div class="w-full sm:w-4/5 md:w-3/5 text-justify space-y-4 text-sm sm:text-base leading-relaxed">
        @foreach (preg_split('/\n\s*\n/', $noticia['texto']) as $parrafo)
            <p>{!! $parrafo !!}</p> {{-- Renderiza HTML directamente --}}
        @endforeach
    </div>
</div>
@endsection
