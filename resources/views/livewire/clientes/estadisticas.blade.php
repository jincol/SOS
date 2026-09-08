<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Session;

new class extends Component {
    //

    public $user_data = null;
    public $estadisticas = null;


    public function mount()
    {

        $this->user_data = collect(Session::get('user_data', []))->except('password')->all();
        //dd($this->user_data);
        //$this->cargarEstadisticas();


    }



    public function cargarEstadisticas()
    {
        $this->estadisticas = $this->getEstadisticas($this->user_data['ruc']);
    }

    public function getEstadisticas($ruc)
    {
        //$ruc = '20602805132';

        try {
            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
            ])->get(config('services.sistema25.base_url').'/report/invoices', [
                'partner_vat' => $ruc
            ]);

            $data = $response->json();

            if (!isset($data['data'])) {
                Log::error('Missing invoices key:', ['data' => $data]);
                return null;
            }

            //$comprobantes = collect($data['invoices']);
            $comprobantes = collect($data['data']);

            return [
                'total_comprobantes' => $comprobantes->count(),
                'total_monto' => round($comprobantes->sum('amount_total'), 2),
                'monto_pendiente' => round($comprobantes->sum('amount_residual'), 2),
                'por_estado' => [
                    'pagados' => $comprobantes->filter(fn($c) => $c['payment_state'] === 'paid')->count(),
                    'parciales' => $comprobantes->filter(fn($c) => $c['payment_state'] === 'partial')->count(),
                    'pendientes' => $comprobantes->filter(fn($c) => $c['payment_state'] === 'not_paid')->count(),
                ],
                'comprobantes_por_mes' => $comprobantes
                    ->groupBy(fn($c) => \Carbon\Carbon::parse($c['invoice_date'])->format('Y-m'))
                    ->map(fn($group) => $group->count())
                    ->take(6)
            ];
        } catch (\Exception $e) {
            Log::error('Error en getEstadisticas:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }


}; ?>

<div x-init="$nextTick(()=>{ $wire.cargarEstadisticas() })">
    @include('livewire.clientes.partials.estadisticas-redesign')
    @if(false)

    <style>
        {{-- Loader spiner-componente --}}
        .loader {
            width: 15px;
            aspect-ratio: 1;
            border-radius: 50%;
            animation: l5 1s infinite linear alternate;
        }

        @keyframes l5 {
            0% {
                box-shadow: 20px 0 #000, -20px 0 #0002;
                background: #000
            }
            33% {
                box-shadow: 20px 0 #000, -20px 0 #0002;
                background: #0002
            }
            66% {
                box-shadow: 20px 0 #0002, -20px 0 #000;
                background: #0002
            }
            100% {
                box-shadow: 20px 0 #0002, -20px 0 #000;
                background: #000
            }
        }

    </style>


    <div wire:loading wire:target="cargarEstadisticas()">

        <h2 class="text-2xl font-bold text-gray-900 mb-6">Dashboard Principal</h2>

        <div style="margin-left: 40px">
            <div class="loader"></div>
        </div>

        <br>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <!-- Título skeleton -->
                        <div class="h-4 bg-gray-300 rounded w-24 mb-3"></div>
                        <!-- Número skeleton -->
                        <div class="h-8 bg-gray-300 rounded w-16"></div>
                    </div>
                    <!-- Ícono skeleton -->
                    <div class="bg-gray-200 p-3 rounded-full">
                        <div class="w-6 h-6 bg-gray-300 rounded"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="h-4 bg-gray-300 rounded w-28 mb-3"></div>
                        <div class="h-8 bg-gray-300 rounded w-20"></div>
                    </div>
                    <div class="bg-gray-200 p-3 rounded-full">
                        <div class="w-6 h-6 bg-gray-300 rounded"></div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between">
                    <div class="flex-1">
                        <div class="h-4 bg-gray-300 rounded w-32 mb-3"></div>
                        <div class="h-8 bg-gray-300 rounded w-12"></div>
                    </div>
                    <div class="bg-gray-200 p-3 rounded-full">
                        <div class="w-6 h-6 bg-gray-300 rounded"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>
    <div wire:loading.remove wire:target="cargarEstadisticas()">


        <h2 class="text-2xl font-bold text-gray-900 mb-6">
            Dashboard Principal
            <button wire:click="cargarEstadisticas" class="ml-3 bg-white rounded-3xl p-1.5 text-blue-700">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                    <path fill-rule="evenodd"
                          d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z"
                          clip-rule="evenodd"/>
                </svg>
            </button>
        </h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <!-- Card 1 -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Comprobantes</p>
                        <div class="text-2xl font-bold">{{ $estadisticas['total_comprobantes'] ?? 0 }}</div>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Monto Total</p>
                        <div class="text-2xl font-bold">
                            S/ {{ number_format($estadisticas['total_monto'] ?? 0, 2) }}</div>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Monto Pendiente</p>
                        <div class="text-2xl font-bold">
                            S/ {{ number_format($estadisticas['monto_pendiente'] ?? 0, 2) }}</div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-semibold text-gray-900 mb-3">Estado de Pagos</p>
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Pagados:</span>
                                <span class="font-semibold">{{ $estadisticas['por_estado']['pagados'] ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Parciales:</span>
                                <span class="font-semibold">{{ $estadisticas['por_estado']['parciales'] ?? 0 }}</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Pendientes:</span>
                                <span class="font-semibold">{{ $estadisticas['por_estado']['pendientes'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <br>


        <!-- Sección del Gráfico -->
        @if(isset($estadisticas['comprobantes_por_mes']) && count($estadisticas['comprobantes_por_mes']) > 0)
            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200" x-data="{ showChart: true}">
                <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Comprobantes por Mes</h2>

                    <button x-on:click="showChart = !showChart"
                            style="z-index: 1002 !important; position: relative !important; pointer-events: auto !important;"
                            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                        <span x-text="showChart ? 'Ocultar' : 'Mostrar'"></span> Gráfico
                    </button>
                </div>

                <!-- Contenido del Gráfico -->
                <div x-show="showChart" x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     class="p-6">

                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                        @foreach($estadisticas['comprobantes_por_mes'] as $mes => $cantidad)
                            @php
                                $maxCantidad = collect($estadisticas['comprobantes_por_mes'])->max();
                                $porcentaje = $maxCantidad > 0 ? round(($cantidad / $maxCantidad) * 100) : 0;
                                $colores = ['#3b82f6', '#10b981', '#8b5cf6', '#eab308', '#ef4444', '#6366f1'];
                                $colorIndex = $loop->index % count($colores);
                            @endphp

                            <div class="text-center">
                                <div class="flex flex-col items-center justify-end h-40 mb-2">
                                    <div
                                        style="width: 88px; height: {{ max(20, ($porcentaje * 1.6)) }}px; background-color: {{ $colores[$colorIndex] }}; border-radius: 8px 8px 0 0;">
                                    </div>
                                </div>

                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    {{ $mes }}
                                </span>

                                <div class="text-xs text-gray-600 mt-1">
                                    {{ $cantidad }} ({{ $porcentaje }}%)
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="text-center py-12">
                    <h2 class="text-lg font-semibold text-gray-500 mb-2">
                        No hay datos disponibles
                    </h2>
                </div>
            </div>
        @endif


    </div>
    @endif
</div>
