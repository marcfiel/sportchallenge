@extends('layout')

@section('content')
<div class="mt-5 space-y-16">

    {{-- CONSEJO DEL DÍA --}}
    <div class="p-2 text-center">
        <h2 class="text-xl font-semibold mb-2">Consejo del Día</h2>
        <p class="text-green-700 italic">"{{ $consejo }}"</p>
    </div>

    {{-- EJERCICIOS Y ACTIVIDAD FÍSICA --}}
    <section x-data="{ abierto: true }" class="transition-all">
        <div class="flex items-center justify-between mb-4 cursor-pointer select-none p-5 bg-green-200 rounded-xl" @click="abierto = !abierto">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 uppercase ">
                Ejercicios y actividad física
            </h3>
            <svg :class="{'rotate-180': abierto}" class="w-6 h-6 transition-transform duration-300 text-gray-700 ml-4"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div x-show="abierto" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                @foreach ($ejerciciosDia as $ejercicio)
                <div class="bg-gray-100 rounded-xl shadow-md overflow-hidden hover:shadow-lg transition-all duration-300">
                    <video class="w-full h-48 object-cover rounded-t-xl" controls muted preload="metadata" playsinline loading="lazy">
                        <source src="{{ asset($ejercicio['video']) }}" type="video/mp4">
                        Tu navegador no puede reproducir el video.
                    </video>
                    <div class="bg-gray-100 p-4 text-black">
                        <h4 class="text-lg sm:text-xl text-green-700 font-semibold">{{ $ejercicio['title'] }}</h4>
                        <p class="text-sm mt-2">{{ $ejercicio['description'] }}</p>
                        @if (!empty($ejercicio['recomendacion']))
                        <p class="text-sm mt-1 italic text-emerald-100">{{ $ejercicio['recomendacion'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>


    {{-- BIENESTAR Y SALUD --}}
    <section x-data="{ abierto: true }" class="transition-all">
        <div class="flex items-center justify-between mb-4 cursor-pointer select-none p-5 bg-green-200 rounded-xl" @click="abierto = !abierto">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 uppercase">Bienestar y salud</h3>
            <svg :class="{ 'rotate-180': abierto }" class="w-6 h-6 transition-transform duration-300 text-gray-700 ml-4"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div x-show="abierto" x-transition>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                {{-- Alimentación equilibrada --}}
                <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="/img/entrenamiento/salud-alimentacion.jpg" alt="Alimentación saludable" class="w-full md:w-1/2 h-48 object-cover">
                    <div class="p-4 flex flex-col justify-center">
                        <h4 class="text-base sm:text-xl font-semibold text-green-700">Alimentación equilibrada</h4>
                        <p class="text-sm text-gray-600 mt-2">Una dieta rica en frutas, vegetales, proteínas y carbohidratos complejos es clave para tu energía y recuperación.</p>
                    </div>
                </div>

                {{-- Importancia del descanso --}}
                <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="/img/entrenamiento/salud-descanso.jpg" alt="Descanso y sueño" class="w-full md:w-1/2 h-48 object-cover">
                    <div class="p-4 flex flex-col justify-center">
                        <h4 class="text-base sm:text-xl font-semibold text-blue-700">Importancia del descanso</h4>
                        <p class="text-sm text-gray-600 mt-2">Dormir entre 7 y 8 horas permite que tu cuerpo se recupere, evita lesiones y mejora el rendimiento físico y mental.</p>
                    </div>
                </div>

                {{-- Hidratación --}}
                <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="/img/entrenamiento/salud-hidratacion.jpg" alt="Hidratación deportiva" class="w-full md:w-1/2 h-48 object-cover">
                    <div class="p-4 flex flex-col justify-center">
                        <h4 class="text-base sm:text-xl font-semibold text-cyan-700">Hidratación</h4>
                        <p class="text-sm text-gray-600 mt-2">Beber agua antes, durante y después del ejercicio ayuda a mantener tu cuerpo activo, enfocado y con mejor resistencia.</p>
                    </div>
                </div>

                {{-- Salud mental --}}
                <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="/img/entrenamiento/salud-mental.jpg" alt="Bienestar mental" class="w-full md:w-1/2 h-48 object-cover">
                    <div class="p-4 flex flex-col justify-center">
                        <h4 class="text-base sm:text-xl font-semibold text-purple-700">Salud mental</h4>
                        <p class="text-sm text-gray-600 mt-2">El ejercicio regular mejora el estado de ánimo, reduce el estrés y fortalece tu mente. ¡Cuida también tu salud emocional!</p>
                    </div>
                </div>

                {{-- Prevención de lesiones --}}
                <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="/img/entrenamiento/salud-lesiones.jpg" alt="Prevención de lesiones" class="w-full md:w-1/2 h-48 object-cover">
                    <div class="p-4 flex flex-col justify-center">
                        <h4 class="text-base sm:text-xl font-semibold text-red-700">Prevención de lesiones</h4>
                        <p class="text-sm text-gray-600 mt-2">Calentar antes de entrenar y estirar al finalizar es clave para evitar lesiones y preparar el cuerpo correctamente.</p>
                    </div>
                </div>

                {{-- Higiene y deporte --}}
                <div class="flex flex-col md:flex-row bg-white rounded-xl shadow-md overflow-hidden">
                    <img src="/img/entrenamiento/salud-higiene.jpg" alt="Higiene y deporte" class="w-full md:w-1/2 h-48 object-cover">
                    <div class="p-4 flex flex-col justify-center">
                        <h4 class="text-base sm:text-xl font-semibold text-orange-700">Higiene y deporte</h4>
                        <p class="text-sm text-gray-600 mt-2">Ducharse tras entrenar, lavar la ropa deportiva y mantener una correcta higiene evita infecciones y mejora la recuperación.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- MOTIVACIÓN Y HÁBITOS --}}
    <section x-data="{ abierto: true }" class="transition-all">
        <div class="flex items-center justify-between mb-4 cursor-pointer select-none p-5 bg-green-200 rounded-xl" @click="abierto = !abierto">
            <h3 class="text-lg sm:text-xl font-bold text-gray-800 uppercase">Motivación y hábitos</h3>
            <svg :class="{ 'rotate-180': abierto }" class="w-6 h-6 transition-transform duration-300 text-gray-700 ml-4 group-hover:text-white"
                fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
            </svg>
        </div>

        <div x-show="abierto" x-transition>
            {{-- Checklist de hábitos diarios --}}
            <div class="bg-white p-4 rounded-xl shadow mb-4">
                <h4 class="text-lg font-semibold text-emerald-700 mb-2">Hábitos diarios</h4>
                <ul id="checklist-habitos" class="space-y-3">
                    @php
                    $habitos = [
                    'Beber 2 litros de agua',
                    'Hacer al menos 30 minutos de actividad física',
                    'Dormir mínimo 7 horas',
                    'Evitar comida ultraprocesada',
                    'Estiramientos al despertar o antes de dormir',
                    'Meditar o desconectar 10 minutos'
                    ];
                    @endphp

                    @foreach ($habitos as $index => $habito)
                    <li>
                        <label class="flex items-center space-x-3 cursor-pointer group">
                            <input
                                type="checkbox"
                                onchange="guardarHabito('{{ $index }}')"
                                id="habito-{{ $index }}"
                                class="peer w-5 h-5 accent-green-600 rounded transition duration-150 cursor-pointer">
                            <span
                                class="text-gray-800 text-sm transition-all duration-150 peer-checked:line-through peer-checked:text-gray-400 peer-checked:opacity-70">
                                {{ $habito }}
                            </span>
                        </label>
                    </li>
                    @endforeach
                </ul>
                <div id="mensaje-completado" class="hidden mt-4 p-4 rounded-xl bg-green-100 border border-green-300 text-green-800 text-center font-semibold">
                    ¡Felicidades! Has completado todos tus hábitos diarios.
                </div>
            </div>

            {{-- Frase motivacional --}}
            <div class="bg-gradient-to-r from-emerald-600 via-green-500 to-lime-400 text-white p-6 rounded-2xl shadow-lg mb-6">
                <h4 class="text-xl font-bold mb-3">Frase motivacional</h4>
                <p class="text-lg italic leading-relaxed">
                    "No cuentes los días, haz que los días cuenten." — Muhammad Ali
                </p>
            </div>
        </div>
    </section>


    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const hoy = new Date().toISOString().split('T')[0];
            const savedDate = localStorage.getItem('habitos_fecha');

            if (savedDate !== hoy) {
                localStorage.removeItem('habitos_checklist');
                localStorage.setItem('habitos_fecha', hoy);
            }

            const habitosGuardados = JSON.parse(localStorage.getItem('habitos_checklist')) || {};
            for (let index in habitosGuardados) {
                const checkbox = document.getElementById('habito-' + index);
                if (checkbox) checkbox.checked = habitosGuardados[index];
            }
        });

        // Guarda los hábitos marcados del día en localStorage y los muestra si se completan todos con un mensaje
        function guardarHabito(index) {
            const hoy = new Date().toISOString().split('T')[0];
            let guardados = JSON.parse(localStorage.getItem('habitos_checklist')) || {};
            guardados[index] = document.getElementById('habito-' + index).checked;
            localStorage.setItem('habitos_checklist', JSON.stringify(guardados));
            localStorage.setItem('habitos_fecha', hoy); // actualiza la fecha

            const total = document.querySelectorAll('#checklist-habitos input').length;
            const completados = Object.values(guardados).filter(v => v).length;

            const mensaje = document.getElementById('mensaje-completado');
            mensaje.classList.toggle('hidden', completados !== total);
        }
    </script>
    </section>


    {{-- COMENTARIOS DE LOS USUARIOS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

    <section class="my-16 text-center">
        <h3 class="text-xl md:text-xl font-bold text-gray-800 mb-8 uppercase">Lo que dicen nuestros usuarios</h3>

        @php
        use Illuminate\Support\Str;

        $testimonios = [
        ['texto' => 'Gracias a esta plataforma volví a moverme. Empecé con retos de caminar y ahora hago bicicleta cada semana.', 'autor' => 'Laura, 34 años'],
        ['texto' => 'Me motiva ver los puntos, logros y retos diarios. Es como un juego, pero para mi salud.', 'autor' => 'Carlos, 28 años'],
        ['texto' => 'Los retos diarios me ayudan a mantenerme constante. Me encanta ver cómo progreso día a día.', 'autor' => 'Julia, 25 años'],
        ['texto' => 'He probado muchas apps, pero esta es la única que me enganchó por sus recompensas reales.', 'autor' => 'Toni, 40 años'],
        ];
        @endphp

        <div class="w-full relative">
            <div class="swiper solo-slide-carousel relative pb-10 max-w-2xl mx-auto cursor-pointer">
                <div class="swiper-wrapper">
                    @foreach($testimonios as $t)
                    <div class="swiper-slide">
                        <div class="bg-white border-l-4 border-green-500 rounded-2xl shadow-md flex flex-col sm:flex-row items-center p-6 sm:p-8 gap-6 min-h-60 sm:min-h-48 hover:shadow-lg transition-shadow duration-300">
                            <img src="{{ asset('img/entrenamiento/testimonio-' . Str::slug(explode(',', $t['autor'])[0]) . '.jpg') }}"
                                alt="Foto de {{ $t['autor'] }}"
                                class="w-16 h-16 md:w-20 md:h-20 rounded-full object-cover shadow-sm" />
                            <div class="text-left">
                                <p class="text-gray-700 text-base italic mb-2">"{{ $t['texto'] }}"</p>
                                <div class="text-sm text-green-600 font-semibold mt-2">— {{ $t['autor'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>

                {{-- Paginación --}}
                <div class="swiper-pagination mt-4 !bottom-0"></div>
            </div>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            new Swiper(".solo-slide-carousel", {
                loop: true,
                slidesPerView: 1,
                spaceBetween: 0,
                pagination: {
                    el: ".solo-slide-carousel .swiper-pagination",
                    clickable: true,
                    bulletClass: 'swiper-pagination-bullet',
                    bulletActiveClass: 'swiper-pagination-bullet-active',
                }
            });
        });
    </script>

    <style>
        /* Personaliza los puntos de paginación a verde */
        .swiper-pagination-bullet {
            background-color: #86efac !important;
            /* Tailwind green-300 */
            opacity: 1;
        }

        .swiper-pagination-bullet-active {
            background-color: #22c55e !important;
            /* Tailwind green-500 */
        }
    </style>
</div>
@endsection