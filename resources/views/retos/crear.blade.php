@extends('layout')

@section('content')
<div class="mt-4 sm:mt-6 md:mt-10 text-xs sm:text-sm md:text-base">

    {{-- Breadcrumb responsive --}}
    <div class="flex items-center mb-6">
        {{-- Icono atrás (solo en móviles) --}}
        <a href="{{ route('retos.index') }}" class="md:hidden flex items-center text-green-600 font-semibold hover:underline">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
            Atrás
        </a>

        {{-- Breadcrumb tradicional (solo en escritorio) --}}
        <div class="hidden md:flex text-sm text-gray-600 items-center space-x-2 truncate">
            <a href="{{ route('retos.index') }}" class="text-green-600 hover:underline font-semibold">
                Retos
            </a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-500 truncate max-w-[200px] block">Crear reto</span>
        </div>
    </div>

    <h1 class="text-xl sm:text-2xl md:text-3xl font-bold mb-6 text-center">Crear nuevo reto</h1>

    {{-- Error --}}
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
        {{ session('error') }}
    </div>
    @endif

    {{-- Aviso de puntos --}}
    <div id="puntos-aviso" class="hidden bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded mb-6"></div>

    <form id="crear-reto-form" method="POST" action="{{ route('retos.store') }}">
        @csrf

        {{-- Tipo de reto --}}
        <div class="bg-gray-100 p-4 sm:p-5 rounded-xl shadow-sm mb-6">
            @if(Auth::user()->role === 'admin')
            <div class="mb-6">
                <label class="block font-semibold text-gray-800 mb-2 text-base sm:text-lg">Tipo de reto</label>

                @php
                    $tipos = [
                        'usuario' => ['label' => 'Clásico', 'emoji' => '📝', 'color' => 'sky'],
                        'oficial' => ['label' => 'Comunidad', 'emoji' => '🌍', 'color' => 'yellow'],
                        'diario' => ['label' => 'Diario', 'emoji' => '📆', 'color' => 'orange'],
                    ];
                    $tipoSeleccionado = old('tipo', 'usuario');
                @endphp

                <div class="grid grid-cols-3 gap-4" id="tipo-reto-selector">
                    @foreach ($tipos as $key => $data)
                        @php
                            $isSelected = $tipoSeleccionado === $key;
                            $base = 'bg-white cursor-pointer border-2 rounded-xl px-4 py-4 text-center font-medium transition-all duration-150';
                            $colors = $isSelected
                                ? "bg-{$data['color']}-100 text-{$data['color']}-800 border-{$data['color']}-400"
                                : "border-gray-300 hover:border-{$data['color']}-400 hover:bg-{$data['color']}-50 hover:text-{$data['color']}-800";
                        @endphp

                        <div
                            data-value="{{ $key }}"
                            class="tipo-reto-card {{ $base }} {{ $colors }}">
                            <div class="text-2xl mb-1">{{ $data['emoji'] }}</div>
                            <div>{{ $data['label'] }}</div>
                        </div>
                    @endforeach
                </div>

                <input type="hidden" name="tipo" id="tipo-reto" value="{{ $tipoSeleccionado }}">
            </div>
            @else
                <input type="hidden" name="tipo" id="tipo-reto" value="usuario">
            @endif

            {{-- Deporte --}}
            <label class="block font-semibold text-gray-800 mb-2 text-base sm:text-lg">Selecciona el deporte</label>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="deporte-selector">
                @php
                    $deportes = ['correr', 'caminar', 'bicicleta', 'ejercicio'];
                    $deporteSeleccionado = old('deporte', 'correr');
                @endphp

                @foreach ($deportes as $deporte)
                    @php
                        $isSelected = $deporteSeleccionado === $deporte;
                    @endphp
                    <div
                        data-value="{{ $deporte }}"
                        class="deporte-card flex items-center justify-center gap-3 cursor-pointer border-2 rounded-xl px-4 py-3 transition-all duration-200
                        {{ $isSelected ? 'border-green-600 bg-green-100 text-green-800 font-semibold' : 'border-gray-300 bg-white hover:border-green-500' }}">
                        <img src="/img/icons/{{ $deporte }}_reto.svg" alt="{{ ucfirst($deporte) }}" class="h-6 w-6">
                        <p class="capitalize">{{ $deporte }}</p>
                    </div>
                @endforeach
            </div>

            <input type="hidden" name="deporte" id="deporte-input" value="{{ old('deporte', 'correr') }}">
        </div>

        {{-- Objetivo y fechas --}}
        <div class="bg-gray-100 p-4 sm:p-5 rounded-xl shadow-sm mb-6">
            <div class="mb-6">
                <label class="block font-semibold text-gray-800 mb-2 text-base sm:text-lg">Objetivo del reto</label>

                <div class="flex flex-col md:flex-row gap-4">
                    <input
                        type="number"
                        name="objetivo_valor"
                        min="1"
                        max="5000"
                        step="1"
                        value="{{ old('objetivo_valor') }}"
                        placeholder="Ejemplo: 10"
                        class="w-full border border-gray-300 bg-white rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>

                    <select
                        name="objetivo_tipo"
                        class="w-full border border-gray-300 bg-white rounded px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                        required>
                        <option value="distancia" {{ old('objetivo_tipo') == 'distancia' ? 'selected' : '' }}>km</option>
                        <option value="tiempo" {{ old('objetivo_tipo') == 'tiempo' ? 'selected' : '' }}>horas</option>
                        <option value="sesiones" {{ old('objetivo_tipo') == 'sesiones' ? 'selected' : '' }}>días</option>
                    </select>
                </div>
            </div>

            <div class="mb-6">
                <label class="block font-semibold text-gray-800 mb-2 text-base sm:text-lg">Fechas del reto</label>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-medium text-green-700 mb-1">Inicio</label>
                        <input
                            type="date"
                            name="fecha_inicio"
                            min="{{ \Carbon\Carbon::today()->toDateString() }}"
                            value="{{ old('fecha_inicio') }}"
                            class="w-full border border-gray-300 bg-white rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            required>
                    </div>

                    <div>
                        <label class="block font-medium text-green-700 mb-1">Fin</label>
                        <input
                            type="date"
                            name="fecha_fin"
                            min="{{ \Carbon\Carbon::today()->toDateString() }}"
                            value="{{ old('fecha_fin') }}"
                            class="w-full border border-gray-300 bg-white rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            required>
                    </div>
                </div>
            </div>

            {{-- Fechas ocupadas (admin) --}}
            @if(Auth::user()->role === 'admin')
            <div id="fechas-diarias-ocupadas" class="mb-6 hidden">
                <label class="block font-semibold text-red-600 mb-2 text-sm">Fechas con reto diario ya existente:</label>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-2 text-xs">
                    @foreach ($fechasOcupadas as $fecha)
                    <div class="bg-red-50 text-red-800 border border-red-200 rounded px-3 py-2 text-center shadow-sm">
                        {{ $fecha }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Información y puntos --}}
        <div class="bg-gray-100 p-4 sm:p-5 rounded-xl shadow-sm mb-6">
            <div class="mb-6">
                <label class="block font-semibold text-gray-800 mb-2 text-base sm:text-lg">Información del reto</label>
                <div class="flex flex-col md:flex-row gap-4">
                    <div class="md:w-1/2">
                        <label class="block text-sm font-medium text-green-700 mb-1">Nombre</label>
                        <input
                            type="text"
                            name="nombre"
                            maxlength="40"
                            value="{{ old('nombre') }}"
                            class="w-full border border-gray-300 bg-white rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            required>
                    </div>

                    <div class="md:w-1/2">
                        <label class="block text-sm font-medium text-green-700 mb-1">Descripción</label>
                        <textarea
                            name="descripcion"
                            rows="3"
                            maxlength="200"
                            class="w-full border border-gray-300 bg-white rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500"
                            required>{{ old('descripcion') }}</textarea>
                    </div>
                </div>
            </div>

            @php
                $usuario = Auth::user();
                $puntosUsuario = $usuario->puntos ?? 0;
                $rangoNombre = $usuario->rango['nombre'] ?? 'Novato';
                $limites = ['Novato' => 50, 'Constante' => 100, 'Proactivo' => 150, 'Leyenda' => 200];
                $coloresTexto = ['Novato' => 'text-green-600', 'Constante' => 'text-blue-600', 'Proactivo' => 'text-purple-600', 'Leyenda' => 'text-yellow-600'];
                $limiteApuesta = $limites[$rangoNombre] ?? 50;
                $colorRangoTexto = $coloresTexto[$rangoNombre] ?? 'text-gray-500';
            @endphp

            <div id="puntos-apostados-div" class="mb-6 max-w-md">
                <label class="block text-sm font-medium text-green-700 mb-1">
                    Puntos apostados
                    <span class="text-xs font-normal text-gray-500">(tienes: {{ $puntosUsuario }})</span>
                </label>
                <input
                    type="number"
                    name="puntos_apuesta"
                    min="0"
                    step="1"
                    max="{{ $puntosUsuario }}"
                    value="{{ old('puntos_apuesta') }}"
                    class="w-full border border-gray-300 bg-white rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
                <p class="text-xs text-gray-600 mt-1">
                    Como <strong class="{{ $colorRangoTexto }}">{{ $rangoNombre }}</strong>, puedes apostar hasta <strong>{{ $limiteApuesta }} puntos</strong>.
                </p>
            </div>

            @if(Auth::user()->role === 'admin')
            <div id="puntos-recompensa-div" class="mb-6 max-w-md hidden">
                <label class="block text-sm font-medium text-green-700 mb-1">Puntos de recompensa</label>
                <input
                    type="number"
                    name="puntos_recompensa"
                    min="0"
                    max="5000"
                    value="{{ old('puntos_recompensa', 0) }}"
                    class="w-full border border-gray-300 bg-white rounded-md px-4 py-2 focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            @endif
        </div>

        {{-- Submit --}}
        <div class="mt-6">
            <button
                type="submit"
                class="w-full max-w-sm mx-auto block bg-green-600 hover:bg-green-700 text-white font-bold text-base sm:text-lg px-8 py-3 rounded-lg shadow-md transition-all duration-200 cursor-pointer">
                Crear reto
            </button>
            <input type="hidden" name="confirmado" id="confirmado" value="0">
        </div>
    </form>
</div>
@endsection


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('crear-reto-form');
        const puntosInput = form.querySelector('input[name="puntos_apuesta"]');
        const tipoInput = document.getElementById('tipo-reto');
        const recompensaDiv = document.getElementById('puntos-recompensa-div');
        const apuestaDiv = document.getElementById('puntos-apostados-div');
        const fechasOcupadasDiv = document.getElementById('fechas-diarias-ocupadas');
        const fechaInicio = form.querySelector('input[name="fecha_inicio"]');
        const fechaFin = form.querySelector('input[name="fecha_fin"]');
        const objetivoSelect = document.getElementById('objetivo-tipo');
        const hiddenInput = document.getElementById('deporte-input');

        function actualizarCampos() {
            if (!tipoInput) return;

            const tipo = tipoInput.value;

            if (tipo === 'oficial' || tipo === 'diario') {
                recompensaDiv?.classList.remove('hidden');
                apuestaDiv?.classList.add('hidden');
            } else {
                recompensaDiv?.classList.add('hidden');
                apuestaDiv?.classList.remove('hidden');
            }

            if (tipo === 'diario') {
                fechasOcupadasDiv?.classList.remove('hidden');
                fechaFin.readOnly = true;
                if (fechaInicio.value) {
                    fechaFin.value = fechaInicio.value;
                }
            } else {
                fechasOcupadasDiv?.classList.add('hidden');
                fechaFin.readOnly = false;
            }
        }

        function actualizarOpcionesObjetivo() {
            if (!tipoInput || !objetivoSelect || !hiddenInput) return;

            const tipo = tipoInput.value;
            const deporte = hiddenInput.value;

            [...objetivoSelect.options].forEach(opt => {
                // Ocultar 'sesiones' si el tipo es diario
                if (tipo === 'diario' && opt.value === 'sesiones') {
                    opt.disabled = true;
                    opt.hidden = true;
                    if (objetivoSelect.value === 'sesiones') {
                        objetivoSelect.value = 'distancia';
                    }
                } else if (opt.value === 'sesiones') {
                    opt.disabled = false;
                    opt.hidden = false;
                }

                // Ocultar 'distancia' si el deporte es ejercicio
                if (deporte === 'ejercicio' && opt.value === 'distancia') {
                    opt.disabled = true;
                    opt.hidden = true;
                    if (objetivoSelect.value === 'distancia') {
                        objetivoSelect.value = 'tiempo'; // puedes cambiar por 'sesiones' si prefieres
                    }
                } else if (opt.value === 'distancia') {
                    opt.disabled = false;
                    opt.hidden = false;
                }
            });
        }



        // Eventos al cargar y cuando cambia tipo
        actualizarCampos();
        actualizarOpcionesObjetivo();

        if (fechaInicio) {
            fechaInicio.addEventListener('change', () => {
                if (tipoInput.value === 'diario') {
                    fechaFin.value = fechaInicio.value;
                }
            });
        }

        // Confirmación si hay puntos apostados
        form.addEventListener('submit', function(e) {
            const puntos = parseInt(puntosInput.value) || 0;
            const confirmadoInput = document.getElementById('confirmado');

            // Si ya está confirmado, permitir el envío normal
            if (confirmadoInput.value === '1') return;

            if (!apuestaDiv.classList.contains('hidden') && puntos > 0) {
                e.preventDefault(); // detenemos el envío
                Swal.fire({
                    title: '¿Crear reto con apuesta?',
                    html: `Vas a gastar ${puntos} punto${puntos === 1 ? '' : 's'} para crear este reto.<br>¿Quieres continuar?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, crear reto',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        confirmadoInput.value = '1'; // ← ahora sí está confirmado
                        form.submit(); // reenvía el formulario
                    }
                });
            }
        });


        // Validación para evitar caracteres no válidos en los inputs numéricos
        const numberInputs = document.querySelectorAll('input[type=number]');
        numberInputs.forEach(input => {
            input.addEventListener('keydown', function(e) {
                if (['e', 'E', '+', '-', '.', ','].includes(e.key)) {
                    e.preventDefault();
                }
            });
            input.addEventListener('paste', function(e) {
                const paste = (e.clipboardData || window.clipboardData).getData('text');
                if (/[eE\+\-]/.test(paste)) {
                    e.preventDefault();
                }
            });
        });

        // Limitar a 8 cifras en inputs numéricos con data-maxlength
        document.querySelectorAll('input[type=number][data-maxlength]').forEach(input => {
            input.addEventListener('input', function() {
                const maxLength = parseInt(this.dataset.maxlength);
                if (this.value.length > maxLength) {
                    this.value = this.value.slice(0, maxLength);
                }
            });
        });


        // Selección visual del tipo de reto
        const tipoCards = document.querySelectorAll('.tipo-reto-card');
        tipoCards.forEach(card => {
            card.addEventListener('click', () => {
                tipoCards.forEach(c => {
                    c.classList.remove(
                        'bg-sky-100', 'text-sky-800', 'border-sky-400',
                        'bg-yellow-100', 'text-yellow-800', 'border-yellow-400',
                        'bg-orange-100', 'text-orange-800', 'border-orange-400'
                    );
                    c.classList.add('bg-white', 'border-gray-300');
                });

                const value = card.getAttribute('data-value');
                tipoInput.value = value;

                if (value === 'usuario') {
                    card.classList.add('bg-sky-100', 'text-sky-800', 'border-sky-400');
                } else if (value === 'oficial') {
                    card.classList.add('bg-yellow-100', 'text-yellow-800', 'border-yellow-400');
                } else if (value === 'diario') {
                    card.classList.add('bg-orange-100', 'text-orange-800', 'border-orange-400');
                }

                card.classList.remove('bg-white', 'border-gray-300');

                actualizarCampos();
                actualizarOpcionesObjetivo();
            });
        });

        // Forzar selección visual correcta del tipo tras recarga
        if (tipoInput) {
            const tipoInicial = tipoInput.value;
            const cardInicial = document.querySelector(`.tipo-reto-card[data-value="${tipoInicial}"]`);
            if (cardInicial) {
                cardInicial.click();
                actualizarOpcionesObjetivo();
            }
        }



        // Selección visual del deporte
        const cards = document.querySelectorAll('.deporte-card');


        cards.forEach(card => {
            card.addEventListener('click', () => {
                cards.forEach(c => {
                    c.classList.remove('border-green-600', 'bg-green-100', 'text-green-800', 'font-semibold');
                    c.classList.add('border-gray-300', 'bg-white');
                });

                card.classList.remove('border-gray-300', 'bg-white');
                card.classList.add('border-green-600', 'bg-green-100', 'text-green-800', 'font-semibold');

                hiddenInput.value = card.getAttribute('data-value');
                actualizarOpcionesObjetivo();
            });
        });

        // Forzar selección visual correcta del deporte tras recarga
        setTimeout(() => {
            const deporteInicial = hiddenInput?.value;
            const cardInicial = document.querySelector(`.deporte-card[data-value="${deporteInicial}"]`);
            if (cardInicial) {
                cardInicial.click();
            }
        }, 0); // Espera al siguiente ciclo de eventos del DOM


    });
</script>

@if(session('error'))
<script>
    document.addEventListener('DOMContentLoaded', function() {
        Swal.fire({
            icon: 'error',
            title: 'Error al crear el reto',
            html: "{{ session('error') }}",
            confirmButtonColor: '#d33'
        });
    });
</script>
@endif