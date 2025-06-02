@extends('layout')

@section('content')
<div class="max-w-4xl mx-auto mt-10 p-6 bg-white rounded-xl shadow-md">
    {{-- ALERTAS --}}
    @if (session('status'))
        <div class="mb-4 p-4 rounded bg-green-100 border border-green-400 text-green-800">
            {{ session('status') }}
        </div>
    @endif

    @if (session('error'))
        <div class="mb-4 p-4 rounded bg-red-100 border border-red-400 text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row">
        {{-- Columna izquierda: Foto + Rol --}}
        <div class="flex flex-col items-center w-full md:w-1/3 space-y-4">
            @php
                $hasValidImage = $usuario->profile_picture && str_starts_with($usuario->profile_picture, 'http');
            @endphp

            @if ($hasValidImage)
                <img src="{{ $usuario->profile_picture }}" alt="Foto de perfil"
                     class="w-32 h-32 sm:w-40 sm:h-40 md:w-48 md:h-48 rounded-full object-cover">
            @else
                <img src="{{ asset('img/user-icon.png') }}" alt="Foto por defecto"
                     class="w-32 h-32 sm:w-40 sm:h-40 md:w-48 md:h-48 rounded-full object-cover">
            @endif

            {{-- Rol visual y botón para mostrar/ocultar cambio --}}
            @if ($usuario->role === 'admin')
                <button id="toggleRolBtn"
                        class="inline-block px-4 py-1 text-sm font-semibold text-white bg-red-600 rounded-full cursor-pointer">
                    Admin
                </button>
            @else
                <button id="toggleRolBtn" type="button"
                        onclick="document.getElementById('modalClave').classList.remove('hidden')"
                        class="inline-block px-4 py-1 text-sm font-semibold text-white bg-blue-600 rounded-full cursor-pointer">
                    Usuario
                </button>
            @endif

            {{-- Botón cambiar de rol oculto inicialmente --}}
            <form method="POST" action="{{ route('perfil.cambiarRol') }}" class="w-full text-center">
                @csrf
                @if ($usuario->role === 'admin')
                    <button id="changeRolBtn" type="submit"
                            class="hidden mt-2 px-4 py-2 bg-gray-200 hover:bg-gray-300 rounded text-sm font-semibold w-full cursor-pointer">
                        Cambiar a Usuario
                    </button>
                @endif
            </form>
        </div>

        {{-- Columna derecha: Datos + Cerrar sesión --}}
        <div class="flex flex-col justify-between w-full md:w-2/3 items-end">

            {{-- Datos --}}
            <div class="space-y-4 w-full sm:w-[80%] md:w-[75%]">
                <div>
                    <label class="block text-gray-700 font-semibold">Usuario:</label>
                    <div class="mt-1 p-2 bg-gray-100 rounded-md">{{ $usuario->username }}</div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Nombre:</label>
                    <div class="mt-1 p-2 bg-gray-100 rounded-md">
                        {{ $usuario->firstname }} {{ $usuario->lastname }}
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Ubicación:</label>
                    <div class="mt-1 p-2 bg-gray-100 rounded-md">
                        {{ $usuario->city }}, {{ $usuario->country }}
                    </div>
                </div>

                <div>
                    <label class="block text-gray-700 font-semibold">Sexo:</label>
                    <div class="mt-1 p-2 bg-gray-100 rounded-md">
                        @if ($usuario->sex === 'M') Hombre
                        @elseif ($usuario->sex === 'F') Mujer
                        @else No especificado
                        @endif
                    </div>
                </div>
            </div>

            {{-- Botón cerrar sesión --}}
            <div class="w-full sm:w-[80%] md:w-[75%] mt-6 flex justify-end">
                <form method="POST" action="{{ route('logout') }}" id="logout-form-perfil">
                    @csrf
                    <button type="button" id="logout-button-perfil"
                            class="px-5 py-2 bg-black text-white rounded-md hover:bg-gray-800 font-semibold cursor-pointer">
                        CERRAR SESIÓN
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Modal para ingresar clave de admin --}}
<div id="modalClave" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center hidden z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h2 class="text-xl font-semibold mb-4">Convertirse en administrador</h2>
        <p class="mb-4 text-sm text-gray-600">Introduce la clave de administrador para confirmar:</p>

        <form method="POST" action="{{ route('perfil.cambiarRol') }}">
            @csrf
            <input type="password" name="clave_admin" placeholder="Clave de administrador"
                   class="w-full border border-gray-300 rounded px-4 py-2 mb-4 focus:outline-none focus:ring focus:ring-gray-300" required>

            <div class="flex justify-end gap-3">
                <button type="button"
                        onclick="document.getElementById('modalClave').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 text-sm cursor-pointer">
                    Cancelar
                </button>
                <button type="submit"
                        class="px-4 py-2 bg-black text-white rounded hover:bg-gray-800 text-sm cursor-pointer">
                    Confirmar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Script para mostrar/ocultar el botón de cambio de rol --}}
@if ($usuario->role === 'admin')
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const toggleBtn = document.getElementById('toggleRolBtn');
        const changeBtn = document.getElementById('changeRolBtn');

        toggleBtn.addEventListener('click', function () {
            changeBtn.classList.toggle('hidden');
        });
    });
</script>
@endif

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        const logoutBtnPerfil = document.getElementById("logout-button-perfil");
        const logoutFormPerfil = document.getElementById("logout-form-perfil");

        if (logoutBtnPerfil && logoutFormPerfil) {
            logoutBtnPerfil.addEventListener("click", function (e) {
                e.preventDefault();

                Swal.fire({
                    title: '¿Cerrar sesión?',
                    text: "Se cerrará tu sesión actual.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc2626',
                    cancelButtonColor: '#000000',
                    confirmButtonText: 'Sí, cerrar sesión',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        logoutFormPerfil.submit();
                    }
                });
            });
        }
    });
</script>

@endsection
