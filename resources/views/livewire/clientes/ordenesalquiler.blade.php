<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

new class extends Component {

    public $ruc;
    public array $ordenesAlquiler = [];
    public string $error = '';
    public $user_data;

    // Propiedades para filtros
    public $filtroFecha = '';
    public $filtroOrden = '';
    public $filtroCompany = '';
    public $filtroConsignatario = '';
    public $filtroSucursal = '';
    public $filtroUsuario = '';

    // Propiedades para paginación
    public $porPagina = 10;
    public $page = 1;

    // Propiedades para ordenamiento
    public $ordenarPor = 'creation_datetime';
    public $ordenAscendente = false; // Por defecto más recientes primero

    public $carga_ejecutada=false;

    public $verFacturas = false;



    public function mount(){
        $this->user_data = collect(Session::get('user_data', []))->except('password')->all();
        $this->ruc = $this->user_data['ruc'];
        //$this->obtenerOrdenesAlquiler();
    }

    public function setPage($page)
    {
        $this->page = $page;
    }

    // Para resetear filtros
    public function resetFiltros()
    {
        $this->filtroFecha = '';
        $this->filtroOrden = '';
        $this->filtroCompany = '';
        $this->filtroConsignatario = '';
        $this->filtroSucursal = '';
        $this->filtroUsuario = '';
        $this->page = 1;
    }

    public function ordenar($campo)
    {
        if ($this->ordenarPor === $campo) {
            $this->ordenAscendente = !$this->ordenAscendente;
        } else {
            $this->ordenarPor = $campo;
            $this->ordenAscendente = true;
        }
    }

    public function updated($propertyName)
    {
        // Si cualquier filtro cambia, resetear a página 1
        if (str_starts_with($propertyName, 'filtro') || $propertyName === 'porPagina') {
            $this->page = 1;
        }
    }

    //with() para filtros y paginacion
    public function with(): array
    {
        $ordenesFiltradas = collect($this->ordenesAlquiler)
            ->when($this->filtroFecha, function($collection) {
                return $collection->filter(function($orden) {
                    $fecha = \Carbon\Carbon::parse($orden['creation_datetime'])->format('Y-m-d');
                    return str_contains($fecha, $this->filtroFecha);
                });
            })
            ->when($this->filtroOrden, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains($orden['name'], $this->filtroOrden);
                });
            })
            ->when($this->filtroCompany, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['company_id']), strtolower($this->filtroCompany));
                });
            })
            ->when($this->filtroConsignatario, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['consignatario']), strtolower($this->filtroConsignatario)) ||
                        str_contains(strtolower($orden['consignatario_ruc']), strtolower($this->filtroConsignatario));
                });
            })
            ->when($this->filtroSucursal, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['branch_id']), strtolower($this->filtroSucursal));
                });
            })
            ->when($this->filtroUsuario, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['user_id']), strtolower($this->filtroUsuario));
                });
            })
            ->when(true, function($collection) {
                return $collection->sort(function($a, $b) {
                    $valorA = $this->ordenarPor === 'creation_datetime'
                        ? strtotime($a[$this->ordenarPor])
                        : $a[$this->ordenarPor] ?? '';
                    $valorB = $this->ordenarPor === 'creation_datetime'
                        ? strtotime($b[$this->ordenarPor])
                        : $b[$this->ordenarPor] ?? '';

                    if ($valorA == $valorB) return 0;

                    $comparacion = $valorA < $valorB ? -1 : 1;
                    return $this->ordenAscendente ? $comparacion : -$comparacion;
                });
            });

        // Paginar la colección
        $paginatedData = $ordenesFiltradas->slice(($this->page - 1) * $this->porPagina, $this->porPagina)->values();
        $total = $ordenesFiltradas->count();

        return ([
            'ordenesFiltradas' => $paginatedData,
            'hasMore' => ($this->page * $this->porPagina) < $total,
            'total' => $total,
            'page' => $this->page
        ]);
    }


    #[On('obtener-ordenes-alquiler')]
    public function primeraCargaDesdeMenu()
    {

        if($this->carga_ejecutada==false){
            $this->obtenerOrdenesAlquiler();
            //dd('ya paso carga');
        }
        else{
            // dd('ya tiene primera carga Ejecutada');
            // si quierenq ue cargue de nuevo para actualizar data deben darle en actualizar arriba
        }
    }




    public function obtenerOrdenesAlquiler(){
        //sleep(1);
        $this->error = '';

        try {
            // Crear carpeta si no existe
            $storagePath = storage_path('app/public/ordenesAlquiler/' . date('Y/m'));
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
            ])
                ->connectTimeout(15)
                ->timeout(60)
                ->retry(2, 1000)
                ->get(config('services.sistema25.base_url').'/report/warehouse/rentals', [
                    'partner_vat' => $this->ruc
                ]);

            Log::info('Response details:', [
                'status' => $response->status(),
                'data' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Validar estructura de respuesta JSON
                if (!isset($data['error']) || !isset($data['data']) || $data['error'] !== false) {
                    throw new \Exception('Formato de respuesta inválido');
                }

                $this->ordenesAlquiler = collect($data['data'])->map(function ($orden) {
                    $procesado = [
                        'name' => $orden['name'] ?? '',
                        'creation_datetime' => $orden['creation_datetime'] ?? '',
                        'consignatario' => $orden['consignatario'] ?? '',
                        'consignatario_ruc' => $orden['consignatario_ruc'] ?? '',
                        'user_id' => $orden['user_id'] ?? '',
                        'state' => $orden['state'] ?? '',
                        'company_id' => $orden['company_id'] ?? '',
                        'branch_id' => $orden['branch_id'] ?? '',
                        'number_m2' => $orden['number_m2'] ?? '',
                        'number_quadrant' => $orden['number_quadrant'] ?? '',
                        'account_move_id' => $orden['account_move_id'] ?? '',
                        'warehouse_pricelist' => $orden['warehouse_pricelist'] ?? '',
                        'account_move_count' => $orden['account_move_count'] ?? '',
                        'warehouse_sese_count' => $orden['warehouse_sese_count'] ?? '',
                        'number_bundles' => $orden['number_bundles'] ?? '',
                        'warehouse_system_sese_count' => $orden['warehouse_system_sese_count'] ?? '',
                        'warehouse_rent_system_count' => $orden['warehouse_rent_system_count'] ?? '',
                        'warehouse_output_system_count' => $orden['warehouse_output_system_count'] ?? '',
                        'last_execution_datetime' => $orden['last_execution_datetime'] ?? '',
                        'invoice_runtime_configuration_id' => $orden['invoice_runtime_configuration_id'] ?? '',
                        'account_move_draft_count' => $orden['account_move_draft_count'] ?? '',
                        'finished_datetime' => $orden['finished_datetime'] ?? '',
                        'finished_user_id' => $orden['finished_user_id'] ?? '',
                        'next_due_datetime' => $orden['next_due_datetime'] ?? '',
                        'periodo' => $orden['periodo'] ?? '',
                        'end_due_datetime' => $orden['end_due_datetime'] ?? '',
                        'approval_due_date' => $orden['approval_due_date'] ?? '',
                        //subarreglos
                        'freight_container_simple_location_ids' => $orden['freight_container_simple_location_ids'] ?? [],
                        'freight_container_history_location_ids' => $orden['freight_container_history_location_ids'] ?? [],
                        'warehouse_system_rent_history_ids' => $orden['warehouse_system_rent_history_ids'] ?? [],
                        'account_move_ids' => $orden['account_move_ids'] ?? [],
                    ];

                    return $procesado;

                })->toArray();

                Log::info('Ordenes procesadas:', [
                    'cantidad' => count($this->ordenesAlquiler),
                    'primer_orden' => $this->ordenesAlquiler[0] ?? null
                ]);

            } else {
                Log::error('API Response Error:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);

                throw new \Exception('Error en la respuesta de la API: ' . $response->status());
            }

            $this->carga_ejecutada = true;

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            $this->carga_ejecutada = true;
            $this->error = 'El servicio de órdenes de alquiler está tardando más de lo esperado. Intenta actualizar nuevamente en unos momentos.';
            Log::error('Error en obtenerordenesAlquiler:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        } catch (\Throwable $e) {
            $this->carga_ejecutada = true;
            $this->error = 'No pudimos actualizar las órdenes de alquiler. Intenta nuevamente en unos momentos.';
            Log::error('Error en obtenerordenesAlquiler:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    public $showModalDetalleOrdenAlquiler = false;
    public $ordenSeleccionada = [];

    public function verDetalleOrdenAlquiler($nameOrden){
        $orden = collect($this->ordenesAlquiler)->firstWhere('name',$nameOrden);
        $this->ordenSeleccionada = $orden;
        $this->showModalDetalleOrdenAlquiler = true;
    }

    #[On('cerrarModalOrdenAlquiler')]
    public function cerrarDetalleOrdenAlquiler(){
        $this->showModalDetalleOrdenAlquiler = false;
    }



    public $ordenSeleccionadaFacturas = [];

    public function VerModalFacturas($nameOrden){

        $orden = collect($this->ordenesAlquiler)->firstWhere('name',$nameOrden);

        //dd($orden);
        $this->ordenSeleccionadaFacturas = $orden;
        $this->verFacturas = true;

     //   dd($this->ordenSeleccionadaFacturas);
    }

    public function CerrarModalFacturas(){

        $this->verFacturas = false;
    }





}; ?>

<div wire:init="primeraCargaDesdeMenu">
    @include('livewire.clientes.partials.orders-alquiler-redesign')
    @if(false)
    <!-- Estilos CSS -->
    <style>
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 1rem;
        }

        .alert-error {
            background-color: #fee2e2;
            border-left: 4px solid #ef4444;
            color: #991b1b;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .table-container {
            overflow-x: auto;
            position: relative;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            border-radius: 0.5rem;
            margin-top: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.875rem;
            text-align: left;
            color: #4b5563;
        }

        thead {
            background-color: #f9fafb;
            text-transform: uppercase;
            font-size: 0.75rem;
            color: #374151;
        }

        th, td {
            padding: 0.75rem;
            border-bottom: 1px solid #e5e7eb;
        }

        tbody tr:hover {
            background-color: #f9fafb;
        }

        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-pink {
            background-color: #fdf2f8;
            color: #be185d;
        }

        .badge-gray {
            background-color: #e7e7ee;
            color: #1f2937;
        }

        .filters-container {
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 8px;
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filter-item {
            display: flex;
            flex-direction: column;
        }

        .filter-label {
            font-size: 14px;
            margin-bottom: 5px;
            color: #374151;
        }

        .filter-input {
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
        }

        .reset-button {
            padding: 8px 16px;
            background-color: #ef4444;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }

        .reset-button:hover {
            background-color: #dc2626;
        }

        .select-none {
            user-select: none;
        }

        th[wire\:click] {
            position: relative;
            cursor: pointer;
        }

        th[wire\:click]:hover {
            background-color: #f3f4f6;
        }

        .pagination-container {
            margin-top: 20px;
            padding: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #f8f9fa;
            border-radius: 8px;
        }

        .pagination-info {
            color: #6b7280;
            font-size: 14px;
        }

        .pagination-buttons {
            display: flex;
            gap: 10px;
        }

        .pagination-button {
            padding: 8px 16px;
            background-color: #fff;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            color: #374151;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.2s;
        }

        .pagination-button:disabled {
            background-color: #f3f4f6;
            cursor: not-allowed;
            color: #9ca3af;
        }

        .pagination-button:not(:disabled):hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }

        .items-per-page {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .items-select {
            padding: 6px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            background-color: white;
        }

        .no-results {
            text-align: center;
            color: #6b7280;
            padding: 1rem;
        }

        .loader_a {
            width: 12px;
            height: 12px;
            border: 2px solid #FFF;
            border-bottom-color: #FF3D00;
            border-radius: 50%;
            display: inline-block;
            box-sizing: border-box;
            animation: rotation 1s linear infinite;
        }

        @keyframes rotation {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
        }
    </style>

    @if($error)
        <div class="alert-error">
            <p>{{ $error }}</p>
        </div>
    @endif


    {{--  Primera carga de esqueleton independiente y unica xD --}}

    @if(count($ordenesFiltradas)==0 && $carga_ejecutada==false)

            <div class="w-full">
            <h2 class="text-2xl font-bold text-gray-900 mb-0">Mis Órdenes de Alquiler</h2>

            <div style="margin-left: 40px; margin-top:20px">
                <div class="loader"></div>
            </div>

            <br>
            {{-- Skeleton --}}
            <div class="w-full">
                <div class="w-full">
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200  w-full">
                        <div class="flex items-center justify-between w-full">
                            <div class="flex-1">
                                <div class="h-4 bg-gray-300 rounded w-24 mb-3"></div>
                                <div class="h-8 bg-gray-300 rounded w-16"></div>
                            </div>
                            <div class="bg-gray-200 p-3 rounded-full">
                                <div class="w-6 h-6 bg-gray-300 rounded"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else



            {{-- Loading con skeleton --}}
            <div wire:loading wire:target="obtenerOrdenesAlquiler" class="w-full">
                <h2 class="text-2xl font-bold text-gray-900 mb-0">Mis Órdenes de Alquiler</h2>

                <div style="margin-left: 40px; margin-top:20px">
                    <div class="loader"></div>
                </div>

                <br>
                {{-- Skeleton --}}
                <div class="w-full">
                    <div class="w-full">
                        <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 w-full">
                            <div class="flex items-center justify-between w-full">
                                <div class="flex-1">
                                    <div class="h-4 bg-gray-300 rounded w-24 mb-3"></div>
                                    <div class="h-8 bg-gray-300 rounded w-16"></div>
                                </div>
                                <div class="bg-gray-200 p-3 rounded-full">
                                    <div class="w-6 h-6 bg-gray-300 rounded"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>


            <div wire:loading.remove wire:target="obtenerOrdenesAlquiler" class="w-full">

                <h2 class="text-2xl font-bold text-gray-900 flex justify-start mb-0">Mis Órdenes de Alquiler
                    <button wire:click="obtenerOrdenesAlquiler" class="ml-3 bg-white rounded-3xl p-1.5 text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd" d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z" clip-rule="evenodd" />
                        </svg>
                    </button>
                </h2>

                <br>

                <div class="bg-white p-6 rounded-lg shadow-md border border-orange-700">

                    <!-- Sección de Filtros -->
                    <div class="filters-container font-semibold">
                        <div class="filters-grid">
                            <div class="filter-item">
                                <label class="filter-label">Fecha de Creación</label>
                                <input
                                    type="date"
                                    wire:model.live="filtroFecha"
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Orden de Alquiler</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroOrden"
                                    placeholder="Buscar por orden..."
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Compañía</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroCompany"
                                    placeholder="Buscar compañía..."
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Consignatario</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroConsignatario"
                                    placeholder="Buscar consignatario..."
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Sucursal</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroSucursal"
                                    placeholder="Buscar sucursal..."
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Usuario</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroUsuario"
                                    placeholder="Buscar usuario..."
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">&nbsp;</label>
                                <button
                                    wire:click="resetFiltros"
                                    class="reset-button"
                                >
                                    Limpiar Filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Órdenes de Alquiler -->
                    <div class="table-container">
                        <table class="font-semibold">
                            <thead>
                            <tr>
                                <th wire:click="ordenar('name')" class="select-none">
                                    Orden
                                    @if($ordenarPor === 'name')
                                        <span class="ml-1">
                                            @if($ordenAscendente) ↑ @else ↓ @endif
                                        </span>
                                    @endif
                                </th>
                                <th wire:click="ordenar('company_id')" class="select-none">
                                    Compañía
                                    @if($ordenarPor === 'company_id')
                                        <span class="ml-1">
                                            @if($ordenAscendente) ↑ @else ↓ @endif
                                        </span>
                                    @endif
                                </th>
                                <th>Consignatario</th>
                                <th>m²</th>
                                <th>Bultos</th>
                                <th>Sucursal</th>
                                <th wire:click="ordenar('creation_datetime')" class="select-none">
                                    Fecha Creación
                                    @if($ordenarPor === 'creation_datetime')
                                        <span class="ml-1">
                                            @if($ordenAscendente) ↑ @else ↓ @endif
                                        </span>
                                    @endif
                                </th>
                                <th>Usuario</th>
                                <th>Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($ordenesFiltradas as $orden)
                                <tr>
                                    <td>
                                        <span class="inline-flex items-center rounded-md bg-pink-50 px-2 py-1 text-xs font-medium text-pink-700 ring-1 ring-pink-700/10 ring-inset">
                                            {{ $orden['name'] }}
                                        </span>

                                        @if(count($orden['account_move_ids'])>=1)
                                        <a wire:click="VerModalFacturas('{{$orden['name']}}')" href="#"  class="text-blue-800 hover:bg-white hover:underline">

                                            <span wire:loading.remove wire:target="VerModalFacturas('{{$orden['name']}}')" class="ml-1 p-1 rounded-sm">
                                                Facturas
                                           </span>
                                            <span wire:key="{{$orden['name']}}" wire:loading wire:target="VerModalFacturas('{{$orden['name']}}')" class="ml-1 rounded-sm p-1 bg-zinc-200">
                                                Facturas<span class="loader_a"></span>
                                            </span>
                                        </a>
                                        @endif

                                    </td>
                                    <td>
                                        <span>{{ $orden['company_id'] }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $orden['consignatario_ruc'] }} - {{ $orden['consignatario'] }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $orden['number_m2'] }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $orden['number_bundles'] }}</span>
                                    </td>
                                    <td>
                                        <span>{{ $orden['branch_id'] }}</span>
                                    </td>
                                    <td>
                                        <span>{{ \Carbon\Carbon::parse($orden['creation_datetime'])->format('d/m/Y H:i:s') }}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-gray">{{ $orden['user_id'] }}</span>
                                    </td>
                                    <td>
                                        <button
                                            wire:click="verDetalleOrdenAlquiler('{{$orden['name']}}')"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200">
                                                <span wire:loading.remove wire:target="verDetalleOrdenAlquiler('{{$orden['name']}}')">
                                                Ver Detalle
                                                </span>
                                            <span wire:loading wire:target="verDetalleOrdenAlquiler('{{$orden['name']}}')" class="loader_a"></span>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="no-results">
                                        No se encontraron órdenes con los filtros seleccionados
                                    </td>
                                </tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="pagination-container">
                        <div class="pagination-info">
                            Mostrando {{ ($ordenesFiltradas->count() > 0) ? (($page - 1) * $porPagina) + 1 : 0 }}
                            - {{ min($page * $porPagina, $total) }}
                            de {{ $total }} órdenes
                        </div>

                        <div class="items-per-page">
                            <label>Items por página:</label>
                            <select wire:model.live="porPagina" class="items-select">
                                <option value="5">5</option>
                                <option value="10">10</option>
                                <option value="25">25</option>
                                <option value="50">50</option>
                            </select>
                        </div>

                        <div class="pagination-buttons">
                            <button
                                wire:click="setPage({{ $page - 1 }})"
                                class="pagination-button"
                                @if($page <= 1) disabled @endif
                            >
                                Anterior
                                <span wire:loading wire:target="setPage({{ $page - 1 }})" class="loader_a ml-1"></span>
                            </button>
                            <button
                                wire:click="setPage({{ $page + 1 }})"
                                class="pagination-button"
                                @if(!$hasMore) disabled @endif
                            >
                                Siguiente
                                <span wire:loading wire:target="setPage({{ $page + 1 }})" class="loader_a ml-1"></span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>

            @if($showModalDetalleOrdenAlquiler)
                <livewire:clientes.modalordenalquiler :ordenSeleccionada_="$ordenSeleccionada"></livewire:clientes.modalordenalquiler>
            @endif


            @if($verFacturas)
            <div class="fixed inset-0 bg-gray-500/75 transition-opacity">

                <div class="relative top-20 mx-auto p-5 border max-w-2xl shadow-lg rounded-md bg-white">

                    <!-- Cabecera del modal -->
                    <div class="flex items-center justify-between mb-4">

                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 0 1-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004ZM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 0 1-.921.42Z" />
                            <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v.816a3.836 3.836 0 0 0-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 0 1-.921-.421l-.879-.66a.75.75 0 0 0-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 0 0 1.5 0v-.81a4.124 4.124 0 0 0 1.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 0 0-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 0 0 .933-1.175l-.415-.33a3.836 3.836 0 0 0-1.719-.755V6Z" clip-rule="evenodd" />
                        </svg>

                        <span class="text-lg font-medium text-gray-900"> Facturas</span>

                        <button wire:click="CerrarModalFacturas()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>

                    </div>

                    <!-- Contenido del modal -->
                    <div class="mt-2 px-2 py-3">
                       {{--  <h3>@foreach($this->ordenSeleccionadaFacturas as $r)
                                'Name: '{{$r}}
                            @endforeach</h3> --}}
                        <div class="grid grid-cols-1 lg:grid-cols-4 text-sm mb-2 gap-1 bg-gray-100 font-semibold p-2 rounded-md">

                            <span> Factura </span>
                            <span> Fecha </span>


                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-4 gap-1 text-sm text-center text-gray-700 font-regular mb-2">
                            @foreach($ordenSeleccionadaFacturas['account_move_ids'] as $index=>$factura)

                                    <span>{{$index + 1}}</span>
                                    <span class="badge badge-comprobante"> {{ $factura['name'] }}</span>
                                    <span>
                                        @if(isset($factura['invoice_date']))
                                            {{ \Carbon\Carbon::parse($factura['invoice_date'])->subHours(5)->format('d/m/Y H:i:s') }}
                                        @else
                                            N/A
                                        @endif
                                    </span>

                            @endforeach
                        </div>


                    </div>

                    <!-- Pie del modal -->
                    <div class="flex justify-end mt-4 space-x-2">
                        <button wire:click="CerrarModalFacturas()" wire:loading wire:target="CerrarModalComprobantesVinculados"
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-sm rounded-md hover:bg-gray-400 focus:outline-none">
                            <span class="loader_a"></span>
                        </button>
                        <button wire:click="CerrarModalFacturas()" wire:loading.remove wire:target="CerrarModalComprobantesVinculados"
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-sm rounded-md hover:bg-gray-400 focus:outline-none">
                            Entendido
                        </button>
                    </div>

                </div>
            </div>
            @endif




    @endif {{-- Fin de If general que muestra el primer Esqueleton independiente xD --}}



    @endif
</div>
