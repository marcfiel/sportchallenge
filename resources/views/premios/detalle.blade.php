@extends('layout')

@php
use Illuminate\Support\Str;

$from = request('from') ?? 'premios';
$breadcrumbLabel = 'Catálogo de Premios';
$breadcrumbRoute = route('premios.index');

$nombreCorto = Str::limit($premio->nombre, 40);
$puntosUsuario = auth()->user()?->puntos ?? 0;
$puntosNecesarios = $premio->puntos_necesarios;
$nombrePremio = $premio->nombre;
@endphp

@section('content')

<div class="text-xs sm:text-sm md:text-base mt-5">

    {{-- Encabezado: Breadcrumb (escritorio) o Icono atrás (móvil) + puntos --}}
    <div class="w-full flex items-center justify-between mb-6">
        <div>
            {{-- Icono atrás solo en móvil --}}
            <a href="{{ $breadcrumbRoute }}"
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
                <a href="{{ $breadcrumbRoute }}" class="text-green-600 hover:underline font-semibold">
                    {{ $breadcrumbLabel }}
                </a>
                <span class="text-gray-400">/</span>
                <span class="text-gray-500 truncate max-w-[200px] block">{{ $nombreCorto }}</span>
            </div>
        </div>

        {{-- Puntos --}}
        <div class="text-sm sm:text-base font-bold text-green-600 text-right">
            {{ number_format($puntosUsuario, 0, ',', '.') }} pts
        </div>
    </div>

    {{-- Contenido del premio --}}
    <div class="max-w-4xl mx-auto px-3 py-6 sm:px-5 sm:py-10 bg-white rounded-xl shadow-md mt-5 overflow-hidden">
        <div class="flex flex-col md:flex-row gap-6">
            {{-- Imagen --}}
            <div class="w-full md:w-1/2">
                <img src="{{ $premio->imagen }}" alt="{{ $premio->nombre }}"
                     class="w-full h-64 object-cover rounded-lg">
            </div>

            {{-- Texto y botón --}}
            <div class="flex-1 flex flex-col justify-between pr-2">
                <div>
                    <h1 class="text-xl sm:text-2xl font-bold text-gray-800 mb-4">{{ $premio->nombre }}</h1>
                    <p class="text-gray-600 mb-6 leading-relaxed text-sm sm:text-base">{{ $premio->descripcion }}</p>
                </div>

                <div>
                    <div class="text-green-600 text-base sm:text-lg font-extrabold mb-4">
                        {{ number_format($puntosNecesarios, 0, ',', '.') }} puntos
                    </div>

                    @if ($yaCanjeado)
                    <div class="bg-green-100 text-green-800 px-4 py-2 rounded font-semibold text-center">
                        Ya has canjeado este premio
                    </div>
                    @else
                    {{-- Botón con SweetAlert --}}
                    <form method="POST" action="{{ route('premios.canjear', $premio->id) }}" id="canjear-form">
                        @csrf
                        <button type="button" id="btn-canjear"
                                class="bg-green-600 hover:bg-green-700 text-white font-bold text-sm sm:text-base px-6 py-3 rounded-lg w-full cursor-pointer"
                                data-usuario="{{ $puntosUsuario }}"
                                data-necesarios="{{ $puntosNecesarios }}"
                                data-nombre="{{ $nombrePremio }}">
                            Canjear puntos
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- SweetAlert2 --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('btn-canjear');
        if (!btn) return;

        const puntosUsuario = parseInt(btn.dataset.usuario);
        const puntosNecesarios = parseInt(btn.dataset.necesarios);
        const nombrePremio = btn.dataset.nombre;

        const puntosUsuarioFormatted = puntosUsuario.toLocaleString('es-ES');
        const puntosNecesariosFormatted = puntosNecesarios.toLocaleString('es-ES');

        btn.addEventListener('click', function (e) {
            e.preventDefault();

            if (puntosUsuario < puntosNecesarios) {
                Swal.fire({
                    icon: 'error',
                    title: 'Puntos insuficientes',
                    text: `Necesitas ${puntosNecesariosFormatted} puntos, pero solo tienes ${puntosUsuarioFormatted}.`,
                    confirmButtonColor: '#d33',
                });
            } else {
                Swal.fire({
                    title: '¿Canjear este premio?',
                    html: `Vas a gastar <strong>${puntosNecesariosFormatted}</strong> puntos para obtener <strong>${nombrePremio}</strong>.`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, canjear',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        document.getElementById('canjear-form').submit();
                    }
                });
            }
        });
    });
</script>
@endsection
