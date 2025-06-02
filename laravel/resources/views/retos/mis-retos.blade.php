@extends('layout')

@section('content')
<div class="mt-6 text-xs sm:text-sm md:text-base">

    {{-- Encabezado con título centrado y breadcrumb o botón atrás --}}
<div class="w-full grid grid-cols-3 items-center mb-4 sm:mb-6 text-xs sm:text-sm md:text-base">

    {{-- IZQUIERDA: Breadcrumb o Atrás --}}
    <div class="text-left">
        {{-- Icono Atrás solo en móvil --}}
        <a href="{{ route('retos.index') }}"
            class="md:hidden text-green-600 hover:underline font-semibold flex items-center gap-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none"
                viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M15 19l-7-7 7-7" />
            </svg>
            Atrás
        </a>

        {{-- Breadcrumb solo visible en escritorio --}}
        <div class="hidden md:flex text-gray-600 items-center space-x-2 truncate">
            <a href="{{ route('retos.index') }}" class="text-green-600 hover:underline font-semibold">
                Retos
            </a>
            <span class="text-gray-400">/</span>
            <span class="text-gray-500 truncate max-w-[200px] block">Mis retos</span>
        </div>
    </div>

    {{-- CENTRO: Título --}}
    <div class="text-center">
        <h1 class="text-base sm:text-2xl md:text-2xl font-bold">Mis Retos</h1>
    </div>

    {{-- DERECHA: espacio vacío para equilibrar --}}
    <div></div>
</div>



    {{-- Tabs --}}
    <div class="mb-4 sm:mb-6 border-b flex space-x-4 sm:space-x-6 text-sm sm:text-lg font-semibold">
        <button onclick="showTab('activos')" class="tab-btn text-black hover:text-green-600 pb-2 border-b-2 border-transparent cursor-pointer">Activos</button>
        <button onclick="showTab('creados')" class="tab-btn text-black hover:text-green-600 pb-2 border-b-2 border-transparent cursor-pointer">Creados</button>
        <button onclick="showTab('historial')" class="tab-btn text-black hover:text-green-600 pb-2 border-b-2 border-transparent cursor-pointer">Historial</button>
    </div>

    {{-- Retos activos --}}
    <div id="tab-activos" class="tab-content hidden">
        @if($retosActivos->isEmpty())
        <p class="text-gray-500 text-sm">No estás unido a ningún reto actualmente.</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($retosActivos as $reto)
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
            'sesiones' => $valor == 1 ? 'día' : 'días'
            };
            @endphp

            <a href="{{ route('retos.mostrar', ['id' => $reto->id, 'from' => 'mis-retos', 'tab' => 'activos']) }}" class="block group">
                <div class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden flex">

                    <div class="{{ $bgIcon }} w-1.5 rounded-l-xl"></div>

                    <div class="flex-1 flex flex-col">
                        <div class="{{ $bgTitle }} px-4 py-3 flex items-center space-x-3 rounded-tr-xl">
                            <img src="/img/icons/{{ $reto->tipo === 'diario' ? 'reto_diario' : $reto->deporte . '_reto' }}.svg"
                                alt="Tipo reto"
                                class="w-6 h-6 {{ $iconColor }} transform transition-transform duration-200 group-hover:scale-110">
                            <h3 class="text-sm font-bold truncate {{ $textColor1 }}">{{ $reto->nombre }}</h3>
                        </div>

                        <div class="p-4 space-y-2 text-xs sm:text-sm {{ $textColor }}">
                            <div class="flex items-center space-x-2">
                                <img src="/img/icons/objetivo.svg" alt="icono objetivo" class="w-4 h-4 {{ $iconText }}">
                                <span>{{ ucfirst($reto->deporte) }} {{ $valor }} {{ $unidad }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <img src="/img/icons/fecha.svg" alt="icono fecha" class="w-4 h-4 {{ $iconText }}">
                                <span>{{ \Carbon\Carbon::parse($reto->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reto->fecha_fin)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Retos creados --}}
    <div id="tab-creados" class="tab-content">
        @if($retos->isEmpty())
        <p class="text-gray-500 text-sm">Aún no has creado ningún reto.</p>
        @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
            @foreach ($retos as $reto)
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

            <a href="{{ route('retos.mostrar', ['id' => $reto->id, 'from' => 'mis-retos', 'tab' => 'creados']) }}" class="block group">
                <div class="bg-white rounded-xl shadow hover:shadow-xl transition overflow-hidden flex">
                    <div class="{{ $bgIcon }} w-1.5 rounded-l-xl"></div>
                    <div class="flex-1 flex flex-col">
                        <div class="{{ $bgTitle }} px-4 py-3 flex items-center space-x-3 rounded-tr-xl">
                            <img src="/img/icons/{{ $reto->tipo === 'diario' ? 'reto_diario' : $reto->deporte . '_reto' }}.svg"
                                alt="Tipo reto"
                                class="w-6 h-6 {{ $iconColor }} transform transition-transform duration-200 group-hover:scale-110">
                            <h3 class="text-sm font-bold truncate {{ $textColor1 }}">{{ $reto->nombre }}</h3>
                        </div>
                        <div class="p-4 space-y-2 text-xs sm:text-sm {{ $textColor }}">
                            <div class="flex items-center space-x-2">
                                <img src="/img/icons/objetivo.svg" alt="icono objetivo" class="w-4 h-4 {{ $iconText }}">
                                <span>{{ ucfirst($reto->deporte) }} {{ $valor }} {{ $unidad }}</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <img src="/img/icons/fecha.svg" alt="icono fecha" class="w-4 h-4 {{ $iconText }}">
                                <span>{{ \Carbon\Carbon::parse($reto->fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($reto->fecha_fin)->format('d/m/Y') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Historial --}}
    <div id="tab-historial" class="tab-content hidden">
        @if($retosHistorial->isEmpty())
        <p class="text-gray-500 text-sm">No hay retos completados o abandonados.</p>
        @else
        <div class="overflow-x-auto">
            <table class="min-w-full border border-gray-300 text-xs sm:text-sm text-left">
                <thead class="bg-black text-white font-bold">
                    <tr>
                        <th class="py-2 px-2 sm:px-4">Nombre</th>
                        <th class="py-2 px-2 sm:px-4">Deporte</th>
                        <th class="py-2 px-2 sm:px-4">Objetivo</th>
                        <th class="py-2 px-2 sm:px-4">Estado</th>
                        <th class="py-2 px-2 sm:px-4">Periodo</th>
                        <th class="py-2 px-2 sm:px-4">Puntos</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($retosHistorial as $historial)
                    @php
                    $estado = 'otro';
                    $color = 'bg-gray-50 hover:bg-gray-100';
                    if ($historial->completado) {
                    $estado = 'Completado';
                    $color = 'bg-green-100 hover:bg-green-300';
                    } elseif ($historial->abandonado) {
                    $estado = 'Abandonado';
                    $color = 'bg-gray-200 hover:bg-gray-300';
                    } elseif (\Carbon\Carbon::parse($historial->fecha_fin)->isPast()) {
                    $estado = 'No superado';
                    $color = 'bg-red-100 hover:bg-red-300';
                    }
                    @endphp

                    <tr onclick="window.location=`{{ route('retos.mostrar', ['id' => $historial->reto_id, 'from' => 'mis-retos', 'tab' => 'historial']) }}`" class="cursor-pointer border-t transition {{ $color }}">
                        <td class="py-2 px-2 sm:px-4">{{ $historial->nombre_reto }}</td>
                        <td class="py-2 px-2 sm:px-4 capitalize">{{ $historial->deporte ?? '-' }}</td>
                        <td class="py-2 px-2 sm:px-4">{{ $historial->objetivo ?? '-' }}</td>
                        <td class="py-2 px-2 sm:px-4 font-semibold">
                            @if($estado === 'Completado')
                            <span class="text-green-600">{{ $estado }}</span>
                            @elseif($estado === 'Abandonado')
                            <span class="text-gray-600">{{ $estado }}</span>
                            @elseif($estado === 'No superado')
                            <span class="text-red-600">{{ $estado }}</span>
                            @else
                            <span class="text-gray-600">Otro</span>
                            @endif
                        </td>
                        <td class="py-2 px-2 sm:px-4">
                            {{ \Carbon\Carbon::parse($historial->fecha_inicio)->format('d/m/Y') }} -
                            {{ \Carbon\Carbon::parse($historial->fecha_fin)->format('d/m/Y') }}
                        </td>
                        <td class="py-2 px-2 sm:px-4">{{ $historial->puntos_obtenidos ?? '-' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>
</div>

<script>
    function showTab(tab) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.getElementById(`tab-${tab}`).classList.remove('hidden');
        document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('border-green-600'));
        event.target.classList.add('border-green-600');
    }
</script>


<script>
    function showTab(tabId) {
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('text-green-600', 'font-bold', 'border-black');
            btn.classList.add('text-black', 'border-transparent');
        });

        document.getElementById('tab-' + tabId)?.classList.remove('hidden');

        const clickedBtn = Array.from(document.querySelectorAll('.tab-btn')).find(btn => btn.getAttribute('onclick')?.includes(tabId));
        if (clickedBtn) {
            clickedBtn.classList.remove('text-black', 'border-transparent');
            clickedBtn.classList.add('text-green-600', 'font-bold', 'border-black');
        }
    }

    document.addEventListener('DOMContentLoaded', () => {
        const params = new URLSearchParams(window.location.search);
        const tab = params.get('tab') || 'creados';
        showTab(tab);
    });
</script>
@endsection