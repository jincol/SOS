<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

new class extends Component {

    public $ruc;
    public array $ordenesSalida = [];
    public string $error = '';
    public $user_data;

    // Propiedades para filtros
    public $filtroFecha = '';
    public $filtroOrdenSalida = '';
    public $filtroIngreso = '';
    public $filtroSucursal = '';
    public $filtroCompany = '';
    public $filtroUsuario = '';

    // Propiedades para paginación
    public $porPagina = 10;
    public $page = 1;

    // Propiedades para ordenamiento
    public $ordenarPor = 'date';
    public $ordenAscendente = false; // Por defecto más recientes primero


    public $carga_ejecutada = false;


    public function mount(){
        $this->user_data = collect(Session::get('user_data', []))->except('password')->all();
        $this->ruc = $this->user_data['ruc'];
        //$this->obtenerOrdenesSalida();
    }


    public function setPage($page)
    {
        $this->page = $page;
    }

    // Para resetear filtros
    public function resetFiltros()
    {
        $this->filtroFecha = '';
        $this->filtroOrdenSalida = '';
        $this->filtroIngreso = '';
        $this->filtroSucursal = '';
        $this->filtroCompany = '';
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
        $ordenesFiltradas = collect($this->ordenesSalida)
            ->when($this->filtroFecha, function($collection) {
                return $collection->filter(function($orden) {
                    $fecha = \Carbon\Carbon::parse($orden['date'])->format('Y-m-d');
                    return str_contains($fecha, $this->filtroFecha);
                });
            })
            ->when($this->filtroOrdenSalida, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['name']), strtolower($this->filtroOrdenSalida));
                });
            })
            ->when($this->filtroIngreso, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['warehouse_system']), strtolower($this->filtroIngreso));
                });
            })
            ->when($this->filtroSucursal, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['branch']), strtolower($this->filtroSucursal));
                });
            })
            ->when($this->filtroCompany, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['company']), strtolower($this->filtroCompany));
                });
            })
            ->when($this->filtroUsuario, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['user']), strtolower($this->filtroUsuario));
                });
            })
            ->when(true, function($collection) {
                return $collection->sort(function($a, $b) {
                    $valorA = $this->ordenarPor === 'date'
                        ? strtotime($a[$this->ordenarPor])
                        : $a[$this->ordenarPor];
                    $valorB = $this->ordenarPor === 'date'
                        ? strtotime($b[$this->ordenarPor])
                        : $b[$this->ordenarPor];

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


    #[On('obtener-ordenes-salida')]
    public function primeraCargaDesdeMenu()
    {

        if($this->carga_ejecutada==false){
            $this->obtenerOrdenesSalida();
            //dd('ya paso carga');
        }
        else{
            // dd('ya tiene primera carga Ejecutada');
            // si quierenq ue cargue de nuevo para actualizar data deben darle en actualizar arriba
        }
    }



    public function obtenerOrdenesSalida(){
        //sleep(1);
        try {
            // Crear carpeta si no existe
            $storagePath = storage_path('app/public/ordenesSalida/' . date('Y/m'));
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
            ])
                ->get(config('services.sistema25.base_url').'/report/warehouse/outputs', [
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

                $this->ordenesSalida = collect($data['data'])->map(function ($orden) {
                    $procesado = [
                        'name'=>$orden['name'] ?? '',
                        'warehouse_system'=>$orden['warehouse_system'] ?? '',
                        'branch'=>$orden['branch'] ?? '',
                        'company'=>$orden['company'] ?? '',
                        'number_bundles'=>$orden['number_bundles'] ?? '',
                        'number_m2'=>$orden['number_m2'] ?? '',
                        'number_pallets'=>$orden['number_pallets'] ?? '',
                        'location_id'=>$orden['location_id'] ?? '',
                        'state'=>$orden['state'] ?? '',
                        'date'=>$orden['date'] ?? '',
                        'number_bundles_simple'=>$orden['number_bundles_simple'] ?? '',
                        'number_pallets_simple'=>$orden['number_pallets_simple'] ?? '',
                        'number_bundles_container'=>$orden['number_bundles_container'] ?? '',
                        'number_pallets_container'=>$orden['number_pallets_container'] ?? '',
                        'dam_aduana'=>$orden['dam_aduana'] ?? '',
                        'dam_anio'=>$orden['dam_anio'] ?? '',
                        'dam_regimen'=>$orden['dam_regimen'] ?? '',
                        'dam_dua'=>$orden['dam_dua'] ?? '',
                        'mic_manifesto'=>$orden['mic_manifesto'] ?? '',
                        'mic_segundo_digito'=>$orden['mic_segundo_digito'] ?? '',
                        'mic_tercer_digito'=>$orden['mic_tercer_digito'] ?? '',
                        'mic_numero_orden'=>$orden['mic_numero_orden'] ?? '',
                        'channel_color'=>$orden['channel_color'] ?? '',
                        'tipo_aduana'=>$orden['tipo_aduana'] ?? '',
                        'tipo_mov'=>$orden['tipo_mov'] ?? '',
                        'regimen'=>$orden['regimen'] ?? '',
                        'fecha_numeracion'=>$orden['fecha_numeracion'] ?? '',
                        'fecha_vencimiento'=>$orden['fecha_vencimiento'] ?? '',
                        'consignatario_ruc'=>$orden['consignatario_ruc'] ?? '',
                        'consignatario_id'=>$orden['consignatario_id'] ?? '',
                        'codigo_agente_aduana'=>$orden['codigo_agente_aduana'] ?? '',
                        'agente_aduana_id'=>$orden['agente_aduana_id'] ?? '',
                        'valor_cif'=>$orden['valor_cif'] ?? '',
                        'valor_fob'=>$orden['valor_fob'] ?? '',
                        'number_containers'=>$orden['number_containers'] ?? '',
                        'number_containers_dua'=>$orden['number_containers_dua'] ?? '',
                        'warehouse_order_transport_count'=>$orden['warehouse_order_transport_count'] ?? '',
                        'number_vehicles'=>$orden['number_vehicles'] ?? '',
                        'warehouse_system_count'=>$orden['warehouse_system_count'] ?? '',
                        'departure_datetime'=>$orden['departure_datetime'] ?? '',
                        'departure_hour'=>$orden['departure_hour'] ?? '',
                        'departure_date'=>$orden['departure_date'] ?? '',
                        'creation_datetime'=>$orden['creation_datetime'] ?? '',
                        'creation_hour'=>$orden['creation_hour'] ?? '',
                        'creation_date'=>$orden['creation_date'] ?? '',
                        'user'=>$orden['user'] ?? '',
                        'finished_user'=>$orden['finished_user'] ?? '',
                        'finished_datetime'=>$orden['finished_datetime'] ?? '',
                        'type_regime_id'=>$orden['type_regime_id'] ?? '',
                        'country_origin'=>$orden['country_origin'] ?? '',
                        'bl_dua'=>$orden['bl_dua'] ?? '',
                        'fac_comercial'=>$orden['fac_comercial'] ?? '',
                        'date_fac_comercial'=>$orden['date_fac_comercial'] ?? '',
                        'dua_complete'=>$orden['dua_complete'] ?? '',
                        'total_gross_weight_output'=>$orden['total_gross_weight_output'] ?? '',
                        'total_useful_weight_output'=>$orden['total_useful_weight_output'] ?? '',
                        'total_neto_weight_output'=>$orden['total_neto_weight_output'] ?? '',
                        'number_warehouse_ouput_system'=>$orden['number_warehouse_ouput_system'] ?? '',
                        'total_gross_weight_output_date'=>$orden['total_gross_weight_output_date'] ?? '',
                        'total_gross_weight_output_hour'=>$orden['total_gross_weight_output_hour'] ?? '',
                        'total_useful_weight_output_date'=>$orden['total_useful_weight_output_date'] ?? '',
                        'total_useful_weight_output_hour'=>$orden['total_useful_weight_output_hour'] ?? '',
                        'total_neto_weight_output_date'=>$orden['total_neto_weight_output_date'] ?? '',
                        'total_neto_weight_output_hour'=>$orden['total_neto_weight_output_hour'] ?? '',
                        'is_linked_rent'=>$orden['is_linked_rent'] ?? '',
                        'is_not_linked_rent'=>$orden['is_not_linked_rent'] ?? '',
                        'warehouse_system_rent'=>$orden['warehouse_system_rent'] ?? '',
                        'warehouse_system_rent_count'=>$orden['warehouse_system_rent_count'] ?? '',
                        // aqui empieza los sub arrays
                        'freight_container_output_subserie_ids' => $orden['freight_container_output_subserie_ids'] ?? [],


                    ];

                    return $procesado;

                    //dd($procesado);

                })->toArray();

                Log::info('Ordenes procesadas:', [
                    'cantidad' => count($this->ordenesSalida),
                    'primer_orden' => $this->ordenesSalida[0] ?? null
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

        } catch (\Exception $e) {
            $this->carga_ejecutada = true;
            $this->error = 'Error: ' . $e->getMessage();
            Log::error('Error en obtenerOrdenesSalida:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }




    public $showModalDetalleOrdenSalida = false;
    public $ordenSeleccionada = [];

    public function verDetalleOrdenSalida($nameOrden){

        $orden = collect($this->ordenesSalida)->firstWhere('name',$nameOrden);

        //dd($orden);
        $this->ordenSeleccionada = $orden;
        $this->showModalDetalleOrdenSalida = true;

    }

    #[On('cerrarModalOrdenSalida')]
    public function cerrarDetalleOrdenSalida(){

        $this->showModalDetalleOrdenSalida = false;

    }

    /*
    public $activeTab = 'aduanero';
    public function setActiveTab($tab){
        $this->activeTab = $tab;
    } */








}; ?>

<div wire:init="primeraCargaDesdeMenu">
    @include('livewire.clientes.partials.orders-salida-redesign')
    @if(false)
    <!-- Estilos CSS (mantengo los mismos del primer componente) -->
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

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
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

        .filter-select {
            padding: 8px;
            border: 1px solid #d1d5db;
            border-radius: 4px;
            font-size: 14px;
            background-color: white;
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


    {{--  if del primer y unico esqueleton para cargar datos desde el dispatch --}}

    @if(count($ordenesFiltradas)==0 && $carga_ejecutada==false)


            <div class="w-full">
            <h2 class="text-2xl font-bold text-gray-900 m|b-0">Mis Órdenes de Salida</h2>

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

    @else




            {{-- Loading con skeleton --}}
            <div wire:loading wire:target="obtenerOrdenesSalida" class="w-full">
                    <h2 class="text-2xl font-bold text-gray-900 m|b-0">Mis Órdenes de Salida</h2>

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

            <div wire:loading.remove wire:target="obtenerOrdenesSalida" class="w-full">

                    <h2 class="text-2xl font-bold text-gray-900 flex justify-start mb-0">Mis Órdenes de Salida
                        <button wire:click="obtenerOrdenesSalida" class="ml-3 bg-white rounded-3xl p-1.5 text-blue-700">
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
                                    <label class="filter-label">Fecha de Salida</label>
                                    <input
                                        type="date"
                                        wire:model.live="filtroFecha"
                                        class="filter-input"
                                    >
                                </div>

                                <div class="filter-item">
                                    <label class="filter-label">Orden de Salida</label>
                                    <input
                                        type="text"
                                        wire:model.live="filtroOrdenSalida"
                                        placeholder="Buscar por orden..."
                                        class="filter-input"
                                    >
                                </div>

                                <div class="filter-item">
                                    <label class="filter-label">Orden de Ingreso</label>
                                    <input
                                        type="text"
                                        wire:model.live="filtroIngreso"
                                        placeholder="Buscar ingreso..."
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
                                    <label class="filter-label">Compañía</label>
                                    <input
                                        type="text"
                                        wire:model.live="filtroCompany"
                                        placeholder="Buscar compañía..."
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

                        <!-- Tabla de Órdenes de Salida -->
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
                                    <th wire:click="ordenar('warehouse_system')" class="select-none">
                                        Ingreso
                                        @if($ordenarPor === 'warehouse_system')
                                            <span class="ml-1">
                                                @if($ordenAscendente) ↑ @else ↓ @endif
                                            </span>
                                        @endif
                                    </th>
                                    <th>Sucursal</th>
                                    <th>Bultos S.</th>
                                    <th>Compañía</th>
                                    <th wire:click="ordenar('date')" class="select-none">
                                        Fecha Salida
                                        @if($ordenarPor === 'date')
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
                                            <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-red-600/10 ring-inset">
                                                {{ $orden['name'] }}
                                            </span>
                                        </td>
                                        <td>
                                            <span>{{ $orden['warehouse_system'] }}</span>
                                        </td>
                                        <td>
                                            <span>{{ $orden['branch'] }}</span>
                                        </td>
                                        <td>
                                            <span class="flex text-center">{{ $orden['number_bundles_simple'] }}</span>
                                        </td>
                                        <td>
                                            <span>{{ $orden['company'] }}</span>
                                        </td>
                                        <td>
                                            <span>{{ \Carbon\Carbon::parse($orden['date'])->format('d/m/Y') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge badge-gray">{{ $orden['user'] }}</span>
                                        </td>
                                        <td>
                                            <button
                                                wire:click="verDetalleOrdenSalida('{{$orden['name']}}')"
                                                class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200">
                                                <span wire:loading.remove wire:target="verDetalleOrdenSalida('{{$orden['name']}}')">
                                                Ver Detalle
                                                </span>
                                                <span wire:loading wire:target="verDetalleOrdenSalida('{{$orden['name']}}')" class="loader_a"></span>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="no-results">
                                            No se encontraron órdenes con los filtros seleccionados
                                        </td>
                                    </tr>
                                @endforelse
                                </tbody>
                            </table>
                        </div>

                        <!-- Paginacion -->
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
                                    <span wire:loading wire:target="setPage({{ $page - 1 }}" class="loader_a ml-1"></span>
                                </button>
                                <button
                                    wire:click="setPage({{ $page + 1 }})"
                                    class="pagination-button"
                                    @if(!$hasMore) disabled @endif
                                >
                                    Siguiente
                                    <span wire:loading wire:target="setPage({{ $page + 1 }}" class="loader_a ml-1"></span>
                                </button>
                            </div>
                        </div>

                    </div>


            </div>

            @if($showModalDetalleOrdenSalida)

                <livewire:clientes.modalordensalida :ordenSeleccionada_="$ordenSeleccionada"></livewire:clientes.modalordensalida>

            @endif




    @endif {{-- Fin del if del primer y unico esqueleton de carga por el dispatch del menu --}}









    @endif
</div>
