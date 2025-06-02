@extends('layout')

@section('content')
<div class="mt-4 sm:mt-6 md:mt-10">

    {{-- TÍTULO --}}
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-5 text-xs sm:text-sm md:text-base">
        <h2 class="text-2xl sm:text-3xl font-bold mb-2 sm:mb-3 md:mb-0">Reto Diario</h2>
    </div>

    {{-- RETO DIARIO + BOTONES en responsive --}}
    <div class="flex flex-col space-y-3 sm:space-y-4 md:space-y-0 md:grid md:grid-cols-12 gap-4 mb-10 items-stretch text-xs sm:text-sm md:text-base">

        {{-- RETO DIARIO --}}
        <div class="md:col-span-6">
            @php
            use Illuminate\Support\Facades\Auth;
            use App\Models\UsuariosReto;

            $retoCompletado = false;
            if (Auth::check() && $retoDiario) {
            $retoCompletado = UsuariosReto::where('usuario_id', Auth::id())
            ->where('reto_id', $retoDiario->id)
            ->where('completado', true)
            ->exists();
            }

            $bgColor = $retoCompletado ? 'bg-green-50' : 'bg-white';
            $iconBg = $retoCompletado ? 'bg-green-100' : 'bg-black';
            $iconFilter = $retoCompletado ? '' : 'filter invert';
            $titleColor = $retoCompletado ? 'text-green-700' : 'text-black';
            @endphp

            @if($retoDiario && \Carbon\Carbon::now()->between($retoDiario->fecha_inicio, $retoDiario->fecha_fin))
            <a href="{{ route('retos.mostrar', $retoDiario->id) }}"
                class="group flex h-full bg-white rounded-xl shadow-md hover:shadow-lg transition overflow-hidden ring-1 ring-gray-200 hover:ring-green-400">
                <div class="{{ $iconBg }} w-1/4 flex items-center justify-center p-4">
                    <div class="w-12 h-12 sm:w-16 sm:h-16 rounded-full flex items-center justify-center group">
                        <img src="/img/icons/reto_diario.svg"
                            alt="icono reto diario"
                            class="w-full h-full {{ $iconFilter }} transform transition-transform duration-300 group-hover:scale-110">
                    </div>
                </div>
                <div class="w-3/4 p-4 flex flex-col justify-center space-y-1">
                    <h2 class="text-base sm:text-xl font-bold {{ $titleColor }} truncate">
                        {{ \Illuminate\Support\Str::limit($retoDiario->nombre, 50) }}
                    </h2>
                    <p class="text-gray-600 text-xs truncate">{{ \Illuminate\Support\Str::limit($retoDiario->descripcion, 60) }}</p>
                    <div class="text-xs text-gray-600">
                        <p><strong>Actividad:</strong> {{ ucfirst($retoDiario->deporte) }}</p>
                        <p><strong>Objetivo:</strong> {{ $retoDiario->objetivo_valor }}
                            {{ $retoDiario->objetivo_tipo === 'distancia' ? 'km' : ($retoDiario->objetivo_tipo === 'tiempo' ? 'horas' : 'sesiones') }}
                        </p>
                        <p><strong>Recompensa:</strong> {{ $retoDiario->puntos ?? $retoDiario->puntos_recompensa ?? 0 }} pts</p>
                    </div>
                </div>
            </a>
            @else
            <div class="bg-gray-100 rounded-lg shadow p-5 flex items-center justify-center h-full">
                <p class="text-gray-500 text-sm">No hay reto diario disponible hoy.</p>
            </div>
            @endif
        </div>

        {{-- BOTONES --}}
        <div class="md:col-span-6 flex flex-col md:flex-col sm:flex-row sm:space-x-4 space-y-4 sm:space-y-0 md:space-y-4 justify-center">
            <a href="{{ route('retos.crear') }}"
                class="flex items-center justify-center bg-emerald-500 text-white py-3 px-6 text-sm sm:text-base rounded-lg shadow hover:bg-emerald-600 font-bold w-full sm:w-1/2 md:w-full">
                <span class="text-xl mr-2">+</span> CREAR RETO
            </a>
            <a href="{{ route('retos.misRetos') }}"
                class="flex items-center justify-center bg-black text-white py-3 px-6 text-sm sm:text-base rounded-lg shadow hover:bg-gray-800 font-bold w-full sm:w-1/2 md:w-full">
                <span class="text-lg mr-2">📋</span> MIS RETOS
            </a>
        </div>

    </div>


    {{-- FILTROS DEPORTES --}}
    <div class="flex flex-wrap gap-3 mb-10 text-xs sm:text-sm md:text-base">
        @php
        $deportes = ['correr' => '🏃‍♂️', 'caminar' => '🚶‍♂️', 'bicicleta' => '🚴‍♂️', 'ejercicio' => '🏋️‍♂️'];
        $filtrosActivos = request()->query('filtros', []);
        if (!is_array($filtrosActivos)) {
        $filtrosActivos = [$filtrosActivos];
        }
        @endphp

        @foreach ($deportes as $deporte => $icono)
        @php
        $activo = in_array($deporte, $filtrosActivos);
        $nuevosFiltros = $activo
        ? array_diff($filtrosActivos, [$deporte])
        : array_merge($filtrosActivos, [$deporte]);
        $hoverColor = match($deporte) {
        'correr' => 'hover:bg-orange-200 hover:text-orange-800',
        'caminar' => 'hover:bg-green-200 hover:text-green-800',
        'bicicleta' => 'hover:bg-blue-200 hover:text-blue-800',
        'ejercicio' => 'hover:bg-purple-200 hover:text-purple-800',
        default => 'hover:bg-gray-300 hover:text-gray-800',
        };
        $estilo = $activo ? 'bg-black text-white' : "bg-gray-200 text-black $hoverColor";
        @endphp

        <button
            type="button"
            class="filtro-reto px-4 py-2 rounded transition cursor-pointer {{ $estilo }}"
            data-deporte="{{ $deporte }}"
            data-activo="{{ $activo ? '1' : '0' }}">
            {{ $icono }} {{ ucfirst($deporte) }}
        </button>
        @endforeach
    </div>


    {{-- SECCIONES DE RETOS --}}
    @if ($retosRecomendados->count() && empty($filtrosActivos))
    <h3 class="text-xl sm:text-2xl font-semibold mt-10 mb-4">Recomendados para ti</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-xs sm:text-sm md:text-base">
        @foreach ($retosRecomendados as $reto)
        @php
        $esDiario = $reto->tipo === 'diario';
        $bgIcon = $esDiario ? 'bg-black' : match($reto->deporte) {
        'correr' => 'bg-orange-200',
        'caminar' => 'bg-emerald-200',
        'bicicleta' => 'bg-blue-200',
        'ejercicio' => 'bg-purple-200',
        default => 'bg-gray-300'
        };
        $bgTitle = $esDiario ? 'bg-black text-white' : 'bg-emerald-400 text-black';
        $iconColor = $esDiario ? 'filter invert' : 'filter brightness-0';
        $textColor = $esDiario ? 'text-white' : 'text-black';
        $iconText = $esDiario ? 'text-white' : 'text-green-600';

        $valor = $reto->objetivo_valor;
        $unidad = match($reto->objetivo_tipo) {
        'distancia' => 'km',
        'tiempo' => $valor == 1 ? 'hora' : 'horas',
        'sesiones' => $valor == 1 ? 'día' : 'días',
        };
        @endphp

        <div class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden flex flex-col group">
            <div class="flex">
                <div class="{{ $bgIcon }} w-1.5 rounded-l-xl"></div>

                <div class="flex-1 flex flex-col">
                    <div class="reto-card cursor-pointer flex-1 flex flex-col justify-between text-xs sm:text-sm md:text-base" data-url="{{ route('retos.mostrar', ['id' => $reto->id, 'from' => 'retos']) }}">
                        {{-- CABECERA --}}
                        <div class="{{ $bgTitle }} px-4 py-3 flex items-center space-x-3 rounded-tr-xl">
                            <img src="/img/icons/{{ $esDiario ? 'reto_diario' : $reto->deporte . '_reto' }}.svg"
                                alt="Tipo reto"
                                class="w-6 h-6 {{ $iconColor }} transition-transform duration-200 group-hover:scale-110">
                            <h3 class="font-bold truncate {{ $textColor }}">{{ $reto->nombre }}</h3>
                        </div>

                        {{-- DETALLES --}}
                        <div class="p-4 space-y-2 flex-1">
                            <div class="flex items-center space-x-2 text-black">
                                <img src="/img/icons/objetivo.svg" alt="icono objetivo"
                                    class="w-4 h-4 {{ $iconText }}">
                                <span>{{ ucfirst($reto->deporte) }} {{ $valor }} {{ $unidad }}</span>
                            </div>
                            <div class="flex items-center space-x-2 text-black">
                                <img src="/img/icons/fecha.svg" alt="icono fecha"
                                    class="w-4 h-4 {{ $iconText }}">
                                <span>
                                    {{ \Carbon\Carbon::parse($reto->fecha_inicio)->format('d/m/Y') }} -
                                    {{ \Carbon\Carbon::parse($reto->fecha_fin)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- BOTÓN --}}
                    <div class="px-4 pb-4">
                        <button type="button"
                            class="btn-toggle-reto w-full py-2 rounded font-semibold transition text-xs sm:text-sm md:text-base cursor-pointer
                        {{ $reto->ya_unido ? 'bg-white text-black border hover:bg-gray-100' : 'bg-black text-white hover:bg-gray-800' }}"
                            data-id="{{ $reto->id }}"
                            data-ya-unido="{{ $reto->ya_unido ? '1' : '0' }}">
                            {{ $reto->ya_unido ? 'Abandonar reto' : 'Unirse al reto' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    @foreach ($retosPorCategoria as $deporte => $retos)
    @if ($retos->count())
    <h3 class="text-xl sm:text-2xl font-semibold mt-10 mb-4">Retos de {{ ucfirst($deporte) }}</h3>
    <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 text-xs sm:text-sm md:text-base">
        @foreach ($retos as $reto)
        @php
        $esDiario = $reto->tipo === 'diario';
        $bgIcon = $esDiario ? 'bg-black' : match($reto->deporte) {
        'correr' => 'bg-orange-200',
        'caminar' => 'bg-emerald-200',
        'bicicleta' => 'bg-blue-200',
        'ejercicio' => 'bg-purple-200',
        default => 'bg-gray-300'
        };
        $bgTitle = $esDiario ? 'bg-black text-white' : 'bg-emerald-400 text-black';
        $iconColor = $esDiario ? 'filter invert' : 'filter brightness-0';
        $textColor = $esDiario ? 'text-white' : 'text-black';
        $iconText = $esDiario ? 'text-white' : 'text-green-600';

        $valor = $reto->objetivo_valor;
        $unidad = match($reto->objetivo_tipo) {
        'distancia' => 'km',
        'tiempo' => $valor == 1 ? 'hora' : 'horas',
        'sesiones' => $valor == 1 ? 'día' : 'días',
        };
        @endphp

        <div class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden flex flex-col group">
            <div class="flex">
                <div class="{{ $bgIcon }} w-1.5 rounded-l-xl"></div>

                <div class="flex-1 flex flex-col">
                    <div class="reto-card cursor-pointer flex-1 flex flex-col justify-between text-xs sm:text-sm md:text-base" data-url="{{ route('retos.mostrar', ['id' => $reto->id, 'from' => 'retos']) }}">
                        {{-- CABECERA --}}
                        <div class="{{ $bgTitle }} px-4 py-3 flex items-center space-x-3 rounded-tr-xl">
                            <img src="/img/icons/{{ $esDiario ? 'reto_diario' : $reto->deporte . '_reto' }}.svg"
                                alt="Tipo reto"
                                class="w-6 h-6 {{ $iconColor }} transition-transform duration-200 group-hover:scale-110">
                            <h3 class="font-bold truncate {{ $textColor }}">{{ $reto->nombre }}</h3>
                        </div>

                        {{-- DETALLES --}}
                        <div class="p-4 space-y-2 flex-1">
                            <div class="flex items-center space-x-2 text-black">
                                <img src="/img/icons/objetivo.svg" alt="icono objetivo"
                                    class="w-4 h-4 {{ $iconText }}">
                                <span>{{ ucfirst($reto->deporte) }} {{ $valor }} {{ $unidad }}</span>
                            </div>
                            <div class="flex items-center space-x-2 text-black">
                                <img src="/img/icons/fecha.svg" alt="icono fecha"
                                    class="w-4 h-4 {{ $iconText }}">
                                <span>
                                    {{ \Carbon\Carbon::parse($reto->fecha_inicio)->format('d/m/Y') }} -
                                    {{ \Carbon\Carbon::parse($reto->fecha_fin)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- BOTÓN --}}
                    <div class="px-4 pb-4">
                        <button type="button"
                            class="btn-toggle-reto w-full py-2 rounded font-semibold transition text-xs sm:text-sm md:text-base
                        {{ $reto->ya_unido ? 'bg-white text-black border hover:bg-gray-100' : 'bg-black text-white hover:bg-gray-800' }} cursor-pointer"
                            data-id="{{ $reto->id }}"
                            data-ya-unido="{{ $reto->ya_unido ? '1' : '0' }}">
                            {{ $reto->ya_unido ? 'Abandonar reto' : 'Unirse al reto' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
    @endforeach



</div>


<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Toastify({
        text: `<div style="display:flex;align-items:center;">
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


<script>
    document.querySelectorAll('.filtro-reto').forEach(button => {
        button.addEventListener('click', function() {
            const deporte = this.getAttribute('data-deporte');
            const activo = this.getAttribute('data-activo') === '1';

            const url = new URL(window.location.href);
            let filtros = [];

            // Recuperar todos los filtros actuales (filtros[] puede repetirse)
            url.searchParams.forEach((value, key) => {
                if (key === 'filtros[]' || key === 'filtros') {
                    filtros.push(value);
                }
            });

            // Agregar o quitar el filtro
            if (activo) {
                filtros = filtros.filter(f => f !== deporte); // quitarlo
            } else {
                filtros.push(deporte); // agregarlo
            }

            // Eliminar todos los filtros actuales del URL
            url.searchParams.delete('filtros[]');
            url.searchParams.delete('filtros');

            // Volver a agregar los filtros únicos
            const filtrosUnicos = [...new Set(filtros)];
            filtrosUnicos.forEach(f => url.searchParams.append('filtros[]', f));

            history.pushState({}, '', url);
            location.reload(); // o reemplázalo con AJAX más adelante
        });
    });

    document.querySelectorAll('.reto-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Evitar que haga redirect si se hace click en un botón o en un input dentro del card
            if (e.target.closest('form') || e.target.tagName === 'BUTTON') return;
            window.location.href = this.dataset.url;
        });
    });
</script>

<script>
    document.querySelectorAll('.btn-toggle-reto').forEach(button => {
        button.addEventListener('click', async function() {
            const retoId = this.dataset.id;
            const yaUnido = this.dataset.yaUnido === '1';
            const url = `/retos/${retoId}/${yaUnido ? 'abandonar' : 'unirse'}`;
            const token = document.querySelector('meta[name="csrf-token"]').content;

            // Cambio inmediato del texto y clases
            if (yaUnido) {
                this.textContent = 'Unirse al reto';
                this.classList.remove('bg-white', 'text-black', 'border', 'hover:bg-gray-100');
                this.classList.add('bg-black', 'text-white', 'hover:bg-gray-800');
                this.dataset.yaUnido = '0';
            } else {
                this.textContent = 'Abandonar reto';
                this.classList.remove('bg-black', 'text-white', 'hover:bg-gray-800');
                this.classList.add('bg-white', 'text-black', 'border', 'hover:bg-gray-100');
                this.dataset.yaUnido = '1';
            }

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) throw new Error(data.message || 'Error inesperado');

                const isUnido = !yaUnido;

                const icon = isUnido ?
                    `<svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" stroke-width="2"
        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
         <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
       </svg>` :
                    `<svg class="w-5 h-5 mr-2 text-white" fill="none" stroke="currentColor" stroke-width="2"
        viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
         <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3H5.25A2.25 2.25 0 003 5.25v13.5A2.25 2.25 0 005.25 21H13.5a2.25 2.25 0 002.25-2.25V15" />
         <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H9m0 0l3-3m-3 3l3 3" />
       </svg>`;

                Toastify({
                    text: `<div style="display:flex;align-items:center;">${icon}<span>${data.message}</span></div>`,
                    duration: 3000,
                    gravity: "top",
                    position: "right",
                    backgroundColor: isUnido ? "#10b981" : "#ef4444",
                    stopOnFocus: true,
                    escapeMarkup: false
                }).showToast();



            } catch (error) {
                // Revertimos visualmente si hubo error
                if (yaUnido) {
                    this.textContent = 'Abandonar reto';
                    this.classList.remove('bg-black', 'text-white', 'hover:bg-gray-800');
                    this.classList.add('bg-white', 'text-black', 'border', 'hover:bg-gray-100');
                    this.dataset.yaUnido = '1';
                } else {
                    this.textContent = 'Unirse al reto';
                    this.classList.remove('bg-white', 'text-black', 'border', 'hover:bg-gray-100');
                    this.classList.add('bg-black', 'text-white', 'hover:bg-gray-800');
                    this.dataset.yaUnido = '0';
                }

                alert(error.message);
            }
        });
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/toastify-js"></script>



@endsection