<?php

use Livewire\Attributes\On;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;


new class extends Component {

    public array $ordenes = [];
    public string $error = '';
    public $ruc;
    public $user_data;

    // Propiedades para filtros
    public $filtroFecha = '';
    public $filtroOrden = '';
    public $filtroTipoIngreso = '';
    public $filtroAlmacenero = '';
    public $filtroCompany = '';
    public $filtroUsuario = '';

    // Propiedades para paginación
    public $porPagina = 10;
    public $page = 1;

    // Propiedades para ordenamiento
    public $ordenarPor = 'fec_ingreso';
    public $ordenAscendente = true;

    public $carga_ejecutada = false;


    public function mount(){
        $this->user_data = collect(Session::get('user_data', []))->except('password')->all();
        $this->ruc = $this->user_data['ruc'];
       // $this->obtenerOrdenesIngreso();
       // $this->obtenerComprobantes();
    }

    // Metodo para cambiar de página
    public function setPage($page)
    {
        $this->page = $page;
    }

    // Para resetear filtros
    public function resetFiltros()
    {
        $this->filtroFecha = '';
        $this->filtroOrden = '';
        $this->filtroTipoIngreso = '';
        $this->filtroAlmacenero = '';
        $this->filtroCompany = '';
        $this->filtroUsuario = '';
        $this->page = 1;
    }

    // Metodo para ordenamiento
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

    //---------------- Empieza Orden de Ingreso ---------------------------------

    // Metodo with() para filtros y paginación
    public function with(): array
    {
        $ordenesFiltradas = collect($this->ordenes)
            ->when($this->filtroFecha, function($collection) {
                return $collection->filter(function($orden) {
                    $fecha = \Carbon\Carbon::parse($orden['fec_ingreso'])->format('Y-m-d');
                    return str_contains($fecha, $this->filtroFecha);
                });
            })
            ->when($this->filtroOrden, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['name']), strtolower($this->filtroOrden));
                });
            })
            ->when($this->filtroTipoIngreso, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['tipo_ingreso']), strtolower($this->filtroTipoIngreso));
                });
            })
            ->when($this->filtroAlmacenero, function($collection) {
                return $collection->filter(function($orden) {
                    return str_contains(strtolower($orden['almacenero']), strtolower($this->filtroAlmacenero));
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
                    $valorA = $this->ordenarPor === 'fec_ingreso'
                        ? strtotime($a[$this->ordenarPor])
                        : $a[$this->ordenarPor];
                    $valorB = $this->ordenarPor === 'fec_ingreso'
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


    #[On('obtener-ordenes-ingreso')]
    public function primeraCargaDesdeMenu()
    {

        if($this->carga_ejecutada==false){
            $this->obtenerOrdenesIngreso();
            //dd('ya paso carga');
        }
        else{
           // dd('ya tiene primera carga Ejecutada');
            // si quierenq ue cargue de nuevo para actualizar data deben darle en actualizar arriba
        }
    }





    public function obtenerOrdenesIngreso(){


        //sleep(1);
        try {
            // Crear carpeta si no existe
            $storagePath = storage_path('app/public/ordenes/' . date('Y/m'));
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
            ])
                ->get(config('services.sistema25.base_url').'/report/warehouse/incomes', [
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

                //dd($data['data']);



                $this->ordenes = collect($data['data'])->map(function ($orden) {
                    $procesado = [
                        //Primero validamos si esta vionculado a una orden de Alquiler
                        'warehouse_system_rent' => $orden['warehouse_system_rent'],
                        //Detalle de Ingreso I
                        'name' => $orden['name'] ?? '',
                        'tipo_ingreso' => $orden['tipo_ingreso'] ?? '',
                        'creation_datetime' => $orden['creation_datetime'] ?? '',
                        'fec_ingreso' => $orden['fec_ingreso'] ?? '',
                        'almacenero' => $orden['almacenero'] ?? '',
                        'company'=> $orden['company']  ?? '',
                        'sucursal'=> $orden['sucursal'] ?? '',
                        'creation_datetime' => $orden['creation_datetime'] ?? '',
                        //Detalle de ingreso II
                        'consignatario_ruc'=> $orden['consignatario_ruc'] ?? '',
                        'consignatario' => $orden['consignatario'] ?? '',
                        'user' => $orden['user'] ?? '',
                        'finished_user' => $orden['finished_user'] ?? '',
                        'finished_datetime' => $orden['finished_datetime'] ?? '',
                        'comm_exec_employee' => $orden['comm_exec_employee'] ?? '',
                        //seccion de los tabs para detalle
                        // tab Aduanero
                        'dam_aduana' => $orden['dam_aduana'] ?? '',
                        'dam_anio' => $orden['dam_anio'] ?? '',
                        'dam_regimen' => $orden['dam_regimen'] ?? '',
                        'dam_dua' => $orden['dam_dua'] ?? '',
                        'mic_manifesto' => $orden['mic_manifesto'] ?? '',
                        'mic_segundo_digito' => $orden['mic_segundo_digito'] ?? '',
                        'mic_tercer_digito' => $orden['mic_tercer_digito'] ?? '',
                        'mic_numero_orden' => $orden['mic_numero_orden'] ?? '',
                        'channel_color' => $orden['channel_color'] ?? '',
                        'type_regime' => $orden['type_regime'] ?? '',
                        'fecha_numeracion' => $orden['fecha_numeracion'] ?? '',
                        'fecha_vencimiento' => $orden['fecha_vencimiento'] ?? '' ,
                        'agente_aduana'=> $orden['agente_aduana'] ?? '',
                        'codigo_agente_aduana' => $orden['codigo_agente_aduana'] ?? '',
                        'valor_cif' => $orden['valor_cif'] ?? '',
                        'valor_fob' => $orden['valor_fob'] ?? '',
                        'number_containers_dua' => $orden['number_containers_dua'] ?? '',
                        'country_origin' => $orden['country_origin'] ?? '',
                        'bl_dua' => $orden['bl_dua'] ?? '',
                        'fac_comercial' => $orden['fac_comercial'] ?? '',
                        'date_fac_comercial' => $orden['date_fac_comercial'] ?? '',

                        //tab fletes tabla: freight_container_simple_ids
                        'fletes' => $orden['freight_container_simple_ids'] ?? [],
                        'type_merchandise_simple' => $orden['type_merchandise_simple'] ?? '',                     //Tipos de Merca..// en Raiz


                        //Tab Contenedores
                        'contenedores_neto' => $orden['container_history_ids'] ?? [],                                    //Tabla para coger campos de contenedores Neto
                        'contenedores_inicial' => $orden['contenedor_ids'] ?? [],                                        //Tabla para coger campos de contenedores Stock Inicial

                        //Tab Vehiculos
                        'vehiculos_netos' => $orden['vehicle_history_ids'] ?? [],
                        'vehiculos_inicial' => $orden['warehouse_system_vehiculo_ids'] ?? [],


                        //Tab Serie
                        'series_neto' => $orden['freight_container_subserie_history_ids'] ?? [],
                        'series_inicial' => $orden['freight_container_subserie_ids'] ?? [],

                        'historial_almacen' => $orden['warehouse_system_history_ids'] ?? [], // ordenes de Salida resumen en la Orden de Ingreso

                    ];

                    return $procesado;

                })->toArray();

                Log::info('Ordenes procesadas:', [
                    'cantidad' => count($this->ordenes),
                    'primer_orden' => $this->ordenes[0] ?? null
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
            Log::error('Error en obtenerOrdenesIngreso:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        $this->obtenerComprobantes();


    }


    public $comprobantes = [];

    public function obtenerComprobantes()
    {
        try {


            // Crear carpeta si no existe
            $storagePath = storage_path('app/public/comprobantes/' . date('Y/m'));
            if (!File::exists($storagePath)) {
                File::makeDirectory($storagePath, 0755, true);
            }

            $response = Http::withHeaders([
                'Api-Key' => config('services.sistema25.api_key'),
                'Content-Type' => 'application/json'
            ])
                ->get(config('services.sistema25.base_url').'/report/invoices', [
                    'partner_vat' => $this->ruc //'20600650166',
                ]);

            Log::info('Response details:', [
                'status' => $response->status(),
                'data' => $response->json()
            ]);

            if ($response->successful()) {
                $data = $response->json();


                // dd($data);

                // Validar estructura de respuesta JSON
                //if (!isset($data['error']) || !isset($data['invoices']) || $data['error'] !== false) {
                if (!isset($data['error']) || !isset($data['data']) || $data['error'] !== false) {
                    throw new \Exception('Formato de respuesta inválido');
                }

                //$this->comprobantes = collect($data['invoices'])->map(function ($comprobante) {
                $this->comprobantes = collect($data['data'])->map(function ($comprobante) {
                    $procesado = [
                        'name' => $comprobante['name'] ?? '',
                        'invoice_date' => $comprobante['invoice_date'] ?? '',
                        'amount_total' => $comprobante['amount_total'] ?? 0.0,
                        'amount_residual' => $comprobante['amount_residual'] ?? 0.0,
                        'dam'=> $comprobante['dam']  ?? '',
                        'date_start' => $comprobante['date_start'] ?? '',
                        'date_outlet'=> $comprobante['date_outlet'] ?? '',
                        'vencimiento'=> $comprobante['remaining_days'] ?? '',
                        'service_type' => $comprobante['service_type'] ?? '',
                        'payment_state' => is_array($comprobante['payment_state']) ? $comprobante['payment_state'] : [$comprobante['payment_state'] ?? 'not_paid'],
                        'payment_state_' => $comprobante['payment_state'] ?? '',
                        'state' => is_array($comprobante['state']) ? $comprobante['state'] : [$comprobante['state'] ?? 'draft'],
                        'ruta_xml' => '',
                        'ruta_cdr' => '',
                        'ruta_pdf' => $comprobante['file_pdf_url'] ?? '',
                        'order_income' =>$comprobante['order_income'] ?? ''
                    ];

                    // Procesar XML
                    if (!empty($comprobante['file_xml'])) {
                        try {
                            $procesado['ruta_xml'] = $this->guardarArchivo(
                                $comprobante['file_xml'],
                                'xml_' . $comprobante['name'] . '.xml'
                            );
                            Log::info('XML guardado:', ['ruta' => $procesado['ruta_xml']]);
                        } catch (\Exception $e) {
                            Log::error('Error guardando XML:', ['error' => $e->getMessage()]);
                        }
                    }

                    // Procesar CDR
                    if (!empty($comprobante['file_cdr'])) {
                        try {
                            $procesado['ruta_cdr'] = $this->guardarArchivo(
                                $comprobante['file_cdr'],
                                'cdr_' . $comprobante['name'] . '.zip'
                            );
                            Log::info('CDR guardado:', ['ruta' => $procesado['ruta_cdr']]);
                        } catch (\Exception $e) {
                            Log::error('Error guardando CDR:', ['error' => $e->getMessage()]);
                        }
                    }

                    // Procesar PDF
                    /*
                    if (!empty($comprobante['file_pdf'])) {
                        try {
                            $procesado['ruta_pdf'] = $this->guardarArchivo(
                                $comprobante['file_pdf'],
                                'pdf_' . $comprobante['name'] . '.pdf'
                            );
                            Log::info('PDF guardado:', ['ruta' => $procesado['ruta_pdf']]);
                        } catch (\Exception $e) {
                            Log::error('Error guardando PDF:', ['error' => $e->getMessage()]);
                        }
                    } */

                    return $procesado;

                })->toArray();

                Log::info('Comprobantes procesados:', [
                    'cantidad' => count($this->comprobantes),
                    'primer_comprobante' => $this->comprobantes[0] ?? null
                ]);

            } else {
                Log::error('API Response Error:', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                    'headers' => $response->headers()
                ]);

                throw new \Exception('Error en la respuesta de la API: ' . $response->status());
            }
        } catch (\Exception $e) {
            $this->error = 'Error: ' . $e->getMessage();
            Log::error('Error en obtenerComprobantes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }



    public $ModalComprobantesViculados = false;
    public $nameOI_comprobantes = '';

    public function VerComprobantesVinculados($nameOI)
    {
            //dd($nameOI);
        $this->nameOI_comprobantes = $nameOI;
        //dd($this->nameOI_comprobantes);
        $this->ModalComprobantesViculados = true;

        //dd($this->comprobantes);

    }

    public function CerrarModalComprobantesVinculados()
    {

        $this->ModalComprobantesViculados = false;
    }






    public $verHistorialOI = false;
    public function VerModalHistorialAlmacenOI($nameOrden){


        $orden = collect($this->ordenes)->firstWhere('name',$nameOrden);

        //dd($orden);
        $this->ordenSeleccionada = $orden;
        $this->verHistorialOI = true;
    }

    public function CerrarModalHistorialAlmacenOI(){
        $this->verHistorialOI = false;
    }


    public $showModalDetalleOrdenIngreso = false;
    public $ordenSeleccionada = [];

    public function verDetalleOrdenIngreso($nameOrden){



        $orden = collect($this->ordenes)->firstWhere('name',$nameOrden);

        //dd($orden);
        $this->ordenSeleccionada = $orden;
        $this->showModalDetalleOrdenIngreso = true;

    }


    #[On('cerrarModalOrdenIngreso')]
    public function cerrarDetalleOrdenIngreso(){

        $this->showModalDetalleOrdenIngreso = false;

    }


    public $activeTab = 'aduanero';
    public function setActiveTab($tab){
        $this->activeTab = $tab;
    }


    // --------------- Termina Orden de Ingreso -------------------------------







}; ?>

<div wire:init="primeraCargaDesdeMenu">
    @include('livewire.clientes.partials.orders-ingreso-redesign')
    @if(false)
            <!-- Estilos CSS -->
            <style>
                /* Estilos generales */
                .container {
                    max-width: 1400px;
                    margin: 0 auto;
                    padding: 1rem;
                }

                /* Alerta de error */
                .alert-error {
                    background-color: #fee2e2;
                    border-left: 4px solid #ef4444;
                    color: #991b1b;
                    padding: 1rem;
                    margin-bottom: 1rem;
                }

                /* Tabla */
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

                /* Estados y badges */
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

                /* Filtros */
                .filters-container {
                    margin-bottom: 20px;
                    padding: 15px;
                    /*background-color: #f9fafb;*/
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

                /* Ordenamiento */
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

                /* Paginación */
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

                /* Mensaje de no resultados */
                .no-results {
                    text-align: center;
                    color: #6b7280;
                    padding: 1rem;
                }
            </style>

            <style> /* para el loader */
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


    {{-- if inicial para ver esqueleton mientras carga el dispatch unico --}}
    @if(count($ordenesFiltradas)==0 && $carga_ejecutada==false)

            <div class="w-full">
            <h2 class="text-2xl font-bold text-gray-900 m|b-0">Mis Órdenes de Ingreso</h2>

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
            <div wire:loading wire:target="obtenerOrdenesIngreso" class="w-full">
                <h2 class="text-2xl font-bold text-gray-900 m|b-0">Mis Órdenes de Ingreso</h2>

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


            {{-- Datos reales cargados --}}
            <div wire:loading.remove wire:target="obtenerOrdenesIngreso" class="w-full">
                <h2 class="text-2xl font-bold text-gray-900 flex justify-start mb-0"> Mis Órdenes de Ingreso
                    <button wire:click="obtenerOrdenesIngreso" class="ml-3 bg-white rounded-3xl p-1.5 text-blue-700">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                            <path fill-rule="evenodd" d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z" clip-rule="evenodd" />
                        </svg>
                    </button>

                </h2>

                <br>

                <div class="bg-white p-6 rounded-lg shadow-md border border-orange-700 ">

                    <!-- Sección de Filtros -->
                    <div class="filters-container  font-semibold">
                        <div class="filters-grid">
                            <div class="filter-item">
                                <label class="filter-label">Fecha de Ingreso</label>
                                <input
                                    type="date"
                                    wire:model.live="filtroFecha"
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Número de Orden</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroOrden"
                                    placeholder="Buscar por orden..."
                                    class="filter-input"
                                >
                            </div>

                            {{--
                             <div class="filter-item">
                                <label class="filter-label">Tipo de Ingreso</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroTipoIngreso"
                                    placeholder="Buscar tipo..."
                                    class="filter-input"
                                >
                            </div> --}}


                            <div class="filter-item">
                                <label class="filter-label">Tipo de Ingreso</label>
                                <select wire:model.live="filtroTipoIngreso" class="filter-select">
                                    <option value="">Todos</option>
                                    <option value="Simple">Simple</option>
                                    <option value="Aduanero">Aduanero</option>
                                </select>
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Almacenero</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroAlmacenero"
                                    placeholder="Buscar almacenero..."
                                    class="filter-input"
                                >
                            </div>

                            <div class="filter-item">
                                <label class="filter-label">Company</label>
                                <input
                                    type="text"
                                    wire:model.live="filtroCompany"
                                    placeholder="Buscar company..."
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

                    <!-- Tabla de Órdenes -->
                    <div class="table-container ">
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
                                <th>Tipo de Ingreso</th>
                                <th wire:click="ordenar('fec_ingreso')" class="select-none">
                                    Fecha Ingreso
                                    @if($ordenarPor === 'fec_ingreso')
                                        <span class="ml-1">
                                            @if($ordenAscendente) ↑ @else ↓ @endif
                                        </span>
                                    @endif
                                </th>
                                <th>Almacenero</th>
                                <th>Company</th>
                                <th>Sucursal</th>
                                <th>F. Creación</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($ordenesFiltradas as $orden)
                                <tr>
                                    <td>
                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-green-600/20 ring-inset">
                                            {{ $orden['name'] }}
                                        </span>
                                        <button
                                            wire:click="VerModalHistorialAlmacenOI('{{ $orden['name'] }}')"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200 "
                                        >   <div wire:loading.remove wire:target="VerModalHistorialAlmacenOI('{{$orden['name']}}')">
                                                Salidas
                                            </div>
                                            <div wire:loading wire:target="VerModalHistorialAlmacenOI('{{$orden['name']}}')" class="ml-1 ">
                                                <span class="loader_a"></span>
                                            </div>
                                        </button>
                                        <button
                                            wire:click="VerComprobantesVinculados('{{ $orden['name'] }}')"
                                            class="bg-gray-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200 "
                                        >   <div wire:loading.remove wire:target="VerComprobantesVinculados('{{$orden['name']}}')">
                                                Comprobantes
                                            </div>
                                            <div wire:loading wire:target="VerComprobantesVinculados('{{$orden['name']}}')" class="ml-1 ">
                                                <span class="loader_a"></span>
                                            </div>
                                        </button>
                                    </td>
                                    <td>{{ $orden['tipo_ingreso'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($orden['fec_ingreso'])->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge badge-gray">{{ $orden['almacenero'] }}</span>
                                    </td>
                                    <td>{{ $orden['company'] }}</td>
                                    <td>{{ $orden['sucursal'] }}</td>
                                    <td>{{ \Carbon\Carbon::parse($orden['creation_datetime'])->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <button
                                            wire:click="verDetalleOrdenIngreso('{{ $orden['name'] }}')"
                                            class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded-md text-xs font-medium transition-colors duration-200 "
                                        >   <div wire:loading.remove wire:target="verDetalleOrdenIngreso('{{$orden['name']}}')">
                                                Ver Detalle
                                            </div>
                                            <div wire:loading wire:target="verDetalleOrdenIngreso('{{$orden['name']}}')" class="ml-1 ">
                                                <span class="loader_a"></span>
                                            </div>
                                        </button>
                                       {{--  <button class="text-yellow-600 hover:text-yellow-900">Editar</button>
                                        <button class="text-red-600 hover:text-red-900">Eliminar</button>
                                        --}}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="no-results">
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
                            </button>
                            <span wire:loading class="loader_a"></span>
                            <button
                                wire:click="setPage({{ $page + 1 }})"
                                class="pagination-button"
                                @if(!$hasMore) disabled @endif
                            >
                                Siguiente
                            </button>
                        </div>
                    </div>

                </div>

            </div>


            {{-- Modal para Detalle de Ordenes  --}}

            <style>
                .tab-active {
                    background-color: #f63b3b;
                    color: white;
                }

                .tab-inactive {
                    background-color: #F3F4F6;
                    color: #6B7280;
                }

                .tab-inactive:hover {
                    background-color: #E5E7EB;
                    color: #374151;
                }
            </style>


            @if($showModalDetalleOrdenIngreso)
                <livewire:clientes.modalordeningreso :ordenSeleccionada_="$ordenSeleccionada" > </livewire:clientes.modalordeningreso>
            @endif


            @if($verHistorialOI)
            <!--  <div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full "> -->
            <div id="modal" class="fixed inset-0 bg-gray-500/75 transition-opacity">
                <div class="relative top-20 mx-auto p-5 border max-w-2xl shadow-lg rounded-md bg-white">

                    <!-- Cabecera del modal -->
                    <div class="flex items-center justify-between mb-4">

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="M9.97.97a.75.75 0 0 1 1.06 0l3 3a.75.75 0 0 1-1.06 1.06l-1.72-1.72v3.44h-1.5V3.31L8.03 5.03a.75.75 0 0 1-1.06-1.06l3-3ZM9.75 6.75v6a.75.75 0 0 0 1.5 0v-6h3a3 3 0 0 1 3 3v7.5a3 3 0 0 1-3 3h-7.5a3 3 0 0 1-3-3v-7.5a3 3 0 0 1 3-3h3Z" />
                                <path d="M7.151 21.75a2.999 2.999 0 0 0 2.599 1.5h7.5a3 3 0 0 0 3-3v-7.5c0-1.11-.603-2.08-1.5-2.599v7.099a4.5 4.5 0 0 1-4.5 4.5H7.151Z" />
                            </svg>

                        <span class="text-lg font-medium text-gray-900">  OS Relacionadas   </span>

                        <button wire:click="CerrarModalHistorialAlmacenOI()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Contenido del modal -->
                    <div class="mt-2 px-2 py-3">
                        <div class="grid grid-cols-1 lg:grid-cols-5 text-sm mb-2 gap-1 bg-gray-100 font-semibold p-2 rounded-md">

                                <span> Codigo </span>
                                <span> Fecha </span>
                                <span> Bultos </span>
                                <span> Nro Pallets</span>
                                <span> Nro Contenedores</span>

                        </div>

                        @php
                        $tbundles = 0; $tpallets=0; $tcontainers=0;
                        @endphp
                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-1 text-sm text-center text-gray-700 font-regular mb-2">
                            @foreach($ordenSeleccionada['historial_almacen'] as $index=>$item_historial)
                                @if(substr($item_historial['code_warehouse'], 0,2)==='OI')
                                    <span class="bg-blue-200 font-medium p-1 rounded-md"> {{$item_historial['code_warehouse']}}</span>
                                    <span class=" p-1"> {{$item_historial['date']}}</span>
                                    <span class=" p-1"> {{$item_historial['number_bundles']}}</span>
                                    <span class=" p-1"> {{$item_historial['number_pallets']}}</span>
                                    <span class=" p-1"> {{$item_historial['number_containers']}}</span>
                                @else
                                    <span class="bg-amber-200 rounded-md font-medium "> {{$item_historial['code_warehouse']}}</span>
                                    <span class="p-1"> {{$item_historial['date']}}</span>
                                    <span class="p-1"> {{$item_historial['number_bundles']}}</span>
                                    <span class="p-1"> {{$item_historial['number_pallets']}}</span>
                                    <span class="p-1"> {{$item_historial['number_containers']}}</span>

                                    @php
                                        $tbundles = $tbundles + $item_historial['number_bundles'];
                                        $tpallets = $tpallets + $item_historial['number_pallets'];
                                        $tcontainers = $tcontainers + $item_historial['number_containers'];
                                    @endphp

                                @endif


                            @endforeach
                        </div>

                        <div class="grid grid-cols-1 lg:grid-cols-5 gap-1 text-sm text-center bg-gray-100 font-semibold p-1 rounded-md">

                            <span> Codigo </span>
                            <span> Fecha </span>
                            <span> {{$tbundles}} </span>
                            <span> {{$tpallets}}</span>
                            <span> {{$tcontainers}}</span>

                        </div>
                    </div>

                    <!-- Pie del modal -->
                    <div class="flex justify-end mt-4 space-x-2">
                        <button wire:click="CerrarModalHistorialAlmacenOI()" wire:loading wire:target="CerrarModalHistorialAlmacenOI"
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-sm rounded-md hover:bg-gray-400 focus:outline-none">
                            <span class="loader_a"></span>
                        </button>
                        <button wire:click="CerrarModalHistorialAlmacenOI()" wire:loading.remove wire:target="CerrarModalHistorialAlmacenOI"
                                class="px-4 py-2 bg-gray-300 text-gray-800 text-sm rounded-md hover:bg-gray-400 focus:outline-none">
                            Entendido
                        </button>
                    </div>

                </div>
            </div>
            @endif

            @if($ModalComprobantesViculados)
                <!-- <div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full "> -->
                <div id="modal" class="fixed inset-0 bg-gray-500/75 transition-opacity">

                    <div class="relative top-20 mx-auto p-5 border max-w-2xl shadow-lg rounded-md bg-white">

                        <!-- Cabecera del modal -->
                        <div class="flex items-center justify-between mb-4">

                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                <path d="M10.464 8.746c.227-.18.497-.311.786-.394v2.795a2.252 2.252 0 0 1-.786-.393c-.394-.313-.546-.681-.546-1.004 0-.323.152-.691.546-1.004ZM12.75 15.662v-2.824c.347.085.664.228.921.421.427.32.579.686.579.991 0 .305-.152.671-.579.991a2.534 2.534 0 0 1-.921.42Z" />
                                <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 6a.75.75 0 0 0-1.5 0v.816a3.836 3.836 0 0 0-1.72.756c-.712.566-1.112 1.35-1.112 2.178 0 .829.4 1.612 1.113 2.178.502.4 1.102.647 1.719.756v2.978a2.536 2.536 0 0 1-.921-.421l-.879-.66a.75.75 0 0 0-.9 1.2l.879.66c.533.4 1.169.645 1.821.75V18a.75.75 0 0 0 1.5 0v-.81a4.124 4.124 0 0 0 1.821-.749c.745-.559 1.179-1.344 1.179-2.191 0-.847-.434-1.632-1.179-2.191a4.122 4.122 0 0 0-1.821-.75V8.354c.29.082.559.213.786.393l.415.33a.75.75 0 0 0 .933-1.175l-.415-.33a3.836 3.836 0 0 0-1.719-.755V6Z" clip-rule="evenodd" />
                            </svg>

                            <span class="text-lg font-medium text-gray-900"> Comprobantes Relacionados</span>


                            <button wire:click="CerrarModalComprobantesVinculados()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <!-- Contenido del modal -->
                        <div class="mt-2 px-2 py-3">
                            <div class="grid grid-cols-1 lg:grid-cols-4 text-sm mb-2 gap-1 bg-gray-100 font-semibold p-2 rounded-md">

                                <span> Comprobante </span>
                                <span> Fecha </span>
                                <span> Estado de Pago </span>
                                <span> Total</span>

                            </div>

                            @php

                                $hoy = \Carbon\Carbon::now();
                                //dd($hoy->format('d/m/Y'));

                            @endphp

                            <div class="grid grid-cols-1 lg:grid-cols-4 gap-1 text-sm text-center text-gray-700 font-regular mb-2">
                                @foreach($comprobantes as $comprobante)

                                    @if($comprobante['order_income'] === $nameOI_comprobantes)
                                    <span class="badge badge-comprobante"> {{ $comprobante['name'] }}</span>
                                    <span> {{ $comprobante['invoice_date']  }}</span>
                                    <span>
                                            @if(in_array('paid', $comprobante['payment_state']))

                                                <span class="badge badge-green">Pagado</span>

                                            @elseif(in_array('not_paid', $comprobante['payment_state']) && (\Carbon\Carbon::parse($comprobante['date_outlet'])->format('d/m/Y')) < $hoy->format('d/m/Y'))

                                                <span class="badge badge-red">Vencido</span>

                                            @elseif(in_array('not_paid', $comprobante['payment_state']) && (\Carbon\Carbon::parse($comprobante['date_outlet'])->format('d/m/Y')) > $hoy->format('d/m/Y'))

                                                <span class="badge badge-yellow">No Pagado </span>

                                            @else

                                                <span class="badge badge-gray"> - </span>

                                            @endif
                                    </span>
                                    <span>
                                        S/ {{ number_format($comprobante['amount_total'], 2) }}
                                    </span>
                                    @endif

                                @endforeach
                            </div>


                        </div>

                        <!-- Pie del modal -->
                        <div class="flex justify-end mt-4 space-x-2">
                            <button wire:click="CerrarModalComprobantesVinculados()" wire:loading wire:target="CerrarModalComprobantesVinculados"
                                    class="px-4 py-2 bg-gray-300 text-gray-800 text-sm rounded-md hover:bg-gray-400 focus:outline-none">
                                <span class="loader_a"></span>
                            </button>
                            <button wire:click="CerrarModalComprobantesVinculados()" wire:loading.remove wire:target="CerrarModalComprobantesVinculados"
                                    class="px-4 py-2 bg-gray-300 text-gray-800 text-sm rounded-md hover:bg-gray-400 focus:outline-none">
                                Entendido
                            </button>
                        </div>

                    </div>
                </div>
            @endif




    @endif {{-- Fi del if que carga el esqueleton unico del dispatch--}}






    @endif
</div>
