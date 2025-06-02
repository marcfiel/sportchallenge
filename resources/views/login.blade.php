<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Sport Challenge - Inicia sesión</title>
    <link rel="stylesheet" href="{{ asset('build/assets/app--dqn27u0.css') }}">
    <script src="{{ asset('build/assets/app-T1DpEqax.js') }}" defer></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body class="bg-gray-50 text-gray-900 font-sans">

    {{-- Contenedor principal que ocupa toda la pantalla --}}
    <div class="flex flex-col min-h-screen">

        {{-- NAV --}}
        <nav class="bg-gray-100 px-4 sm:px-8 py-6 sm:py-10 flex justify-center items-center">
            <h1 class="font-bold text-2xl sm:text-4xl md:text-5xl text-green-600">Sport Challenge</h1>
        </nav>

        {{-- Imagen superior en móvil (oculta en escritorio) --}}
        <div class="block lg:hidden w-full h-52 sm:h-64 md:h-72 bg-cover bg-[center_top_20%]" style="background-image: url('{{ asset('img/imagen0.jpg') }}');"></div>

        {{-- Contenido principal --}}
        <div class="flex flex-1 w-full flex-col lg:flex-row">

            {{-- Imagen lateral solo escritorio --}}
            <div class="hidden lg:block lg:w-1/2 bg-cover bg-center" style="background-image: url('{{ asset('img/imagen0.jpg') }}');"></div>

            {{-- Columna derecha (texto y botón) --}}
            <div class="w-full lg:w-1/2 p-6 sm:p-10 md:p-14 flex flex-col justify-between">

                {{-- Sección de texto --}}
                <div>
                    <h2 class="text-xl sm:text-2xl md:text-3xl font-bold mb-4 sm:mb-6">Tu motivación para moverte empieza aquí</h2>
                    <p class="mb-4 sm:mb-6 text-sm sm:text-base md:text-lg text-gray-700">
                        Conecta tu cuenta de Strava y transforma tu actividad física en retos, logros y motivación diaria.
                    </p>
                    <ul class="list-disc list-inside mb-6 text-sm sm:text-base md:text-lg text-gray-700 space-y-2">
                        <li>Acepta retos diarios y semanales</li>
                        <li>Gana puntos por tu constancia y esfuerzo</li>
                        <li>Descubre desafíos por tipo de ejercicio</li>
                        <li>Visualiza tu progreso personal y mejora poco a poco</li>
                    </ul>
                </div>

                {{-- Sección de login --}}
                <div>
                    <a href="{{ url('/auth/strava') }}"
                        class="block text-center bg-black text-white py-3 sm:py-4 text-base sm:text-xl rounded hover:bg-gray-800 transition mb-4 max-w-xl mx-auto">
                        CONÉCTATE CON STRAVA
                    </a>
                    <p class="text-center text-sm sm:text-base text-gray-600">
                        ¿Aún no usas Strava?
                        <a href="https://www.strava.com/register" target="_blank"
                            class="font-semibold underline hover:text-green-500">Crea una cuenta aquí</a>
                    </p>
                </div>
            </div>
        </div>
    </div>

</body>

</html>