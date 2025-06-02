<!DOCTYPE html>
<html lang="es" x-data="{ open: false }">

<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sport Challenge</title>
    <link rel="stylesheet" href="{{ asset('build/assets/app--dqn27u0.css') }}">
    <script src="{{ asset('build/assets/app-T1DpEqax.js') }}" defer></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>
</head>

<body class="bg-gray-50 text-gray-900 font-sans min-w-full">

    <nav class="bg-gray-100 py-3 md:py-6">
        <div class="w-full px-4 sm:px-6 lg:px-8">
            <div class="max-w-7xl mx-auto flex items-center justify-between relative">
                <!-- Hamburguesa móvil (izquierda) -->
                <div class="md:hidden">
                    <button @click="open = !open" class="text-gray-800 focus:outline-none">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>

                <!-- Logo centrado en móvil -->
                <div class="absolute left-1/2 transform -translate-x-1/2 md:static md:translate-x-0">
                    <a href="{{ route('home') }}"
                        class="font-bold text-2xl md:text-4xl text-green-600 hover:text-green-500">
                        Sport Challenge
                    </a>
                </div>

                <!-- Menú escritorio -->
                <div class="hidden md:flex space-x-12 text-xl ml-12">
                    <a href="{{ route('retos.index') }}"
                        class="relative pb-1 {{ request()->routeIs('retos.index') ? 'font-bold text-black after:content-[\'\'] after:absolute after:bottom-0 after:left-0 after:w-full after:h-[2px] after:bg-green-600' : 'hover:text-green-600' }}">
                        Retos
                    </a>
                    <a href="{{ route('entrenamiento') }}"
                        class="relative pb-1 {{ request()->routeIs('entrenamiento') ? 'font-bold text-black after:content-[\'\'] after:absolute after:bottom-0 after:left-0 after:w-full after:h-[2px] after:bg-green-600' : 'hover:text-green-600' }}">
                        Entrenamiento
                    </a>
                    <a href="{{ route('actividad') }}"
                        class="relative pb-1 {{ request()->routeIs('actividad') ? 'font-bold text-black after:content-[\'\'] after:absolute after:bottom-0 after:left-0 after:w-full after:h-[2px] after:bg-green-600' : 'hover:text-green-600' }}">
                        Mi Actividad
                    </a>
                </div>

                <!-- Perfil (derecha) -->
                @php $usuario = Auth::user(); @endphp
                @if ($usuario)
                <div class="flex items-center gap-3 ml-auto md:ml-0">
                    @if ($usuario->role === 'admin')
                    <span class="px-2 py-0.5 md:px-3 md:py-1 bg-red-600 text-white text-xs md:text-sm font-semibold rounded-full cursor-default">
                        Admin
                    </span>
                    @endif
                    <div class="relative">
                        <button id="profileButton">
                            @if ($usuario->profile_picture && str_starts_with($usuario->profile_picture, 'http'))
                            <img src="{{ $usuario->profile_picture }}?t={{ time() }}" alt="Perfil"
                                class="w-10 h-10 rounded-full object-cover cursor-pointer hover:opacity-80 {{ $usuario->role === 'admin' ? 'border-2 border-red-600' : '' }}">
                            @else
                            <img src="{{ asset('img/user-icon.png') }}" alt="Perfil"
                                class="w-10 h-10 rounded-full cursor-pointer hover:opacity-80 {{ $usuario->role === 'admin' ? 'border-2 border-red-600' : '' }}">
                            @endif
                        </button>
                        <div id="profileMenu"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-2 z-20">
                            <a href="{{ route('perfil') }}"
                                class="block px-4 py-2 text-gray-800 hover:bg-gray-100">Mi Perfil</a>
                            <form id="logout-form" method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="button" id="logout-button"
                                    class="w-full text-left px-4 py-2 text-gray-800 hover:bg-gray-100 cursor-pointer">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Menú móvil -->
            <div class="md:hidden mt-4" x-show="open" @click.away="open = false">
                <div class="flex flex-col space-y-4 text-lg">
                    <a href="{{ route('retos.index') }}" class="hover:text-green-600">Retos</a>
                    <a href="{{ route('entrenamiento') }}" class="hover:text-green-600">Entrenamiento</a>
                    <a href="{{ route('actividad') }}" class="hover:text-green-600">Mi Actividad</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="w-full">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @yield('content')
        </div>
    </div>

    <footer class="bg-black text-white pt-14 pb-4 mt-15 w-full">
        <div class="w-full max-w-7xl mx-auto flex flex-col justify-between px-6">
            <div class="flex flex-col md:flex-row justify-between items-start text-base leading-relaxed gap-6 sm:gap-8 md:gap-12">
                <div class="mb-8 md:mb-0 leading-relaxed">
                    <h2 class="text-2xl font-bold mb-5">Sport Challenge</h2>
                    <p>Reta, compite y mantente en forma</p>
                </div>

                <div class="mb-8 md:mb-0 leading-relaxed">
                    <h3 class="text-xl font-semibold mb-5">Navegación</h3>
                    <ul class="space-y-2">
                        <li><a href="{{ route('home') }}" class="hover:underline hover:text-green-500">Inicio</a></li>
                        <li><a href="{{ route('retos.index') }}" class="hover:underline hover:text-green-500">Retos</a></li>
                        <li><a href="{{ route('entrenamiento') }}" class="hover:underline hover:text-green-500">Entrenamiento</a></li>
                        <li><a href="{{ route('actividad') }}" class="hover:underline hover:text-green-500">Mi Actividad</a></li>
                    </ul>
                </div>

                <div class="mb-8 md:mb-0 leading-relaxed">
                    <h3 class="text-xl font-semibold mb-5">Legal</h3>
                    <ul class="space-y-3">
                        <li><a href="{{ route('legal.aviso') }}" class="hover:underline hover:text-green-500">Aviso Legal</a></li>
                        <li><a href="{{ route('legal.privacidad') }}" class="hover:underline hover:text-green-500">Política de privacidad</a></li>
                        <li><a href="{{ route('legal.terminos') }}" class="hover:underline hover:text-green-500">Términos de uso</a></li>
                    </ul>
                </div>

                <div class="leading-relaxed">
                    <h3 class="text-xl font-semibold mb-5">Contacto</h3>
                    <p>¿Tienes dudas o sugerencias?</p>
                    <p class="mt-4">
                        <a href="mailto:info@sportchallenge.com"
                            class="hover:underline hover:text-green-500">info@sportchallenge.com</a>
                    </p>
                    <div class="flex space-x-12 mt-8">
                        <a href="https://www.instagram.com/">
                            <img src="/img/instagram.png" alt="Instagram"
                                class="w-12 h-12 transition-transform transform hover:scale-110 hover:invert duration-300">
                        </a>
                        <a href="https://x.com/">
                            <img src="/img/twitter.png" alt="X"
                                class="w-12 h-12 transition-transform transform hover:scale-110 hover:invert duration-300">
                        </a>
                        <a href="https://www.youtube.com/">
                            <img src="/img/youtube.png" alt="YouTube"
                                class="w-12 h-12 transition-transform transform hover:scale-110 hover:invert duration-300">
                        </a>
                    </div>
                </div>
            </div>

            <div class="mt-10 text-center text-sm text-gray-400">
                &copy; 2025 Sport Challenge.
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const logoutBtn = document.getElementById("logout-button");
            const logoutForm = document.getElementById("logout-form");

            logoutBtn.addEventListener("click", function(e) {
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
                        logoutForm.submit();
                    }
                });
            });
        });

        const profileButton = document.getElementById('profileButton');
        const profileMenu = document.getElementById('profileMenu');

        profileButton.addEventListener('click', () => {
            profileMenu.classList.toggle('hidden');
        });

        document.addEventListener('click', (e) => {
            if (!profileButton.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.classList.add('hidden');
            }
        });
    </script>
</body>

</html>