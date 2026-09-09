<?php

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\On;


new class extends Component {
    //

    use WithPagination;

    public array $comprobantes = [];
    public string $error = '';
    public $ruc;

    public function mount()
    {

        $this->user_data = collect(Session::get('user_data', []))->except('password')->all();

        // dd($this->user_data);
        $this->ruc = $this->user_data['ruc'];
        //$this->obtenerComprobantes();
    }


    // Propiedades para filtros
    public $filtroFecha = '';
    public $filtroNumero = '';
    public $filtroMonto = '';
    public $filtroEstadoPago = '';
    public $filtroTipoServicio = '';

    public $porPagina = 10;
    public $page = 1;
    public $totalComprobantes = 0;
    public $totalPaginas = 1;
    public $hasNextPage = false;
    public $hasPreviousPage = false;


    public $carga_ejecutada=false;


    // Metodo para cambiar de página
    public function setPage($page)
    {
        $page = max(1, min((int) $page, max(1, (int) $this->totalPaginas)));

        if ($page === (int) $this->page) {
            return;
        }

        $this->page = $page;
        $this->obtenerComprobantes();
    }

    public function updatedPorPagina($value)
    {
        $value = (int) $value;
        $this->porPagina = in_array($value, [10, 25, 50], true) ? $value : 10;
        $this->page = 1;

        if ($this->carga_ejecutada) {
            $this->obtenerComprobantes();
        }
    }


    // Para resetear filtros
    public function resetFiltros()
    {
        $this->filtroFecha = '';
        $this->filtroNumero = '';
        $this->filtroMonto = '';
        $this->filtroEstadoPago = '';
        $this->filtroTipoServicio = ''; //Tipo de factura
        $this->page = 1;

        if ($this->carga_ejecutada) {
            $this->obtenerComprobantes();
        }
    }


    /*public function render()
    {
        return view('livewire.lista-comprobantes');
    }*/

    //Filtros ...
    public function with(): array
    {
        $filtersActive = filled($this->filtroFecha)
            || filled($this->filtroNumero)
            || filled($this->filtroMonto)
            || filled($this->filtroEstadoPago)
            || filled($this->filtroTipoServicio);

        $comprobantesFiltrados = collect($this->comprobantes)
            ->when($this->filtroFecha, function ($collection) {
                return $collection->filter(function ($comprobante) {
                    $fecha = \Carbon\Carbon::parse($comprobante['invoice_date'])->format('Y-m-d');
                    return str_contains($fecha, $this->filtroFecha);
                });
            })
            ->when($this->filtroNumero, function ($collection) {
                return $collection->filter(function ($comprobante) {
                    return str_contains(strtolower($comprobante['name']), strtolower($this->filtroNumero));
                });
            })
            ->when($this->filtroMonto, function ($collection) {
                return $collection->filter(function ($comprobante) {
                    return $comprobante['amount_total'] == $this->filtroMonto;
                });
            })
            ->when($this->filtroEstadoPago, function ($collection) {
                return $collection->filter(function ($comprobante) {
                    return in_array($this->filtroEstadoPago, $comprobante['payment_state']);
                });
            })
            ->when($this->filtroTipoServicio, function ($collection) {
                return $collection->filter(function ($comprobante) {
                    return $comprobante['service_type'] === $this->filtroTipoServicio;
                });
            });;

        return ([
            'comprobantesFiltrados' => $comprobantesFiltrados->values(),
            'hasMore' => $this->hasNextPage,
            'hasPrevious' => $this->hasPreviousPage,
            'total' => $this->totalComprobantes,
            'lastPage' => $this->totalPaginas,
            'page' => $this->page,
            'visibleCount' => $comprobantesFiltrados->count(),
            'filtersActive' => $filtersActive,
            'firstItem' => $this->totalComprobantes > 0 ? (($this->page - 1) * $this->porPagina) + 1 : 0,
            'lastItem' => min($this->page * $this->porPagina, $this->totalComprobantes),
        ]);
    }


    #[On('cargar-comprobantes')]
    public function primeraCargaDesdeMenu()
    {

        if($this->carga_ejecutada==false){
            $this->obtenerComprobantes();
            //dd('ya paso carga');
        }
        else{
            // dd('ya tiene primera carga Ejecutada');
            // si quierenq ue cargue de nuevo para actualizar data deben darle en actualizar arriba
        }
    }



    public function obtenerComprobantes()
    {

        //sleep(3);
        try {
            $this->error = '';


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
                    'partner_vat' => $this->ruc,
                    'page' => $this->page,
                    'limit' => $this->porPagina,
                ]);

            Log::info('Response details:', [
                'status' => $response->status(),
                'page' => $this->page,
                'limit' => $this->porPagina,
            ]);

            if ($response->successful()) {
                $data = $response->json();


                // dd($data);

                // Validar estructura de respuesta JSON
                //if (!isset($data['error']) || !isset($data['invoices']) || $data['error'] !== false) {
                if (!isset($data['error']) || !isset($data['data']) || $data['error'] !== false) {
                    throw new \Exception('Formato de respuesta inválido');
                }

                $pagination = $data['pagination'] ?? [];
                $this->page = max(1, (int) ($pagination['page'] ?? $this->page));
                $this->totalComprobantes = max(0, (int) ($pagination['total_records'] ?? count($data['data'])));
                $this->totalPaginas = max(1, (int) ($pagination['total_pages'] ?? 1));
                $this->hasNextPage = (bool) ($pagination['has_next'] ?? ($this->page < $this->totalPaginas));
                $this->hasPreviousPage = (bool) ($pagination['has_previous'] ?? ($this->page > 1));

                //$this->comprobantes = collect($data['invoices'])->map(function ($comprobante) {
                $this->comprobantes = collect($data['data'])->map(function ($comprobante) {
                    $procesado = [
                        'name' => $comprobante['name'] ?? '',
                        'invoice_date' => $comprobante['invoice_date'] ?? '',
                        'amount_total' => $comprobante['amount_total'] ?? 0.0,
                        'amount_residual' => $comprobante['amount_residual'] ?? 0.0,
                        'dam' => $comprobante['dam'] ?? '',
                        'date_start' => $comprobante['date_start'] ?? '',
                        'date_outlet' => $comprobante['date_outlet'] ?? '',
                        'vencimiento' => $comprobante['remaining_days'] ?? '',
                        'service_type' => $comprobante['service_type'] ?? '',
                        'payment_state' => is_array($comprobante['payment_state']) ? $comprobante['payment_state'] : [$comprobante['payment_state'] ?? 'not_paid'],
                        'payment_state_' => $comprobante['payment_state'] ?? '',
                        'state' => is_array($comprobante['state']) ? $comprobante['state'] : [$comprobante['state'] ?? 'draft'],
                        'ruta_xml' => '',
                        'ruta_cdr' => '',
                        'ruta_pdf' => $comprobante['file_pdf_url'] ?? ''
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

            $this->carga_ejecutada = true;

        } catch (\Exception $e) {
            $this->carga_ejecutada = true;
            $this->error = 'Error: ' . $e->getMessage();
            Log::error('Error en obtenerComprobantes:', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    //Funcion que decodifica 2 veces en base 64

    private function guardarArchivo(string $base64Content, string $filename): string
    {
        try {
            // Primera decodificación
            $decodedContent = base64_decode($base64Content);
            if ($decodedContent === false) {
                throw new \Exception('Contenido base64 inválido en primera decodificación');
            }

            // Para archivos XML, verificamos si necesita una segunda decodificación
            //if (str_ends_with(strtolower($filename), '.xml')) {
            // Verificamos si el contenido decodificado aún parece ser base64
            if (preg_match('/^[A-Za-z0-9+\/]+={0,2}$/', trim($decodedContent))) {
                $secondDecode = base64_decode($decodedContent);
                if ($secondDecode !== false) {
                    $decodedContent = $secondDecode;
                    Log::info('Se realizó una segunda decodificación base64 para XML');
                }
            }
            //}

            $relativePath = 'comprobantes/' . date('Y/m') . '/' . $filename;
            $success = Storage::disk('public')->put($relativePath, $decodedContent);

            if (!$success) {
                throw new \Exception('No se pudo guardar el archivo');
            }

            Log::info('Archivo guardado exitosamente:', [
                'path' => $relativePath,
                'size' => strlen($decodedContent),
                'tipo' => str_ends_with(strtolower($filename), '.xml') ? 'XML' : 'Otro'
            ]);

            return Storage::disk('public')->url($relativePath);
        } catch (\Exception $e) {
            Log::error('Error guardando archivo:', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    //funcion que guarda  decodificado una sola vez en base 64
    /*private function guardarArchivo(string $base64Content, string $filename): string
    {
        try {
            $decodedContent = base64_decode($base64Content);
            if ($decodedContent === false) {
                throw new \Exception('Contenido base64 inválido');
            }

            $relativePath = 'comprobantes/' . date('Y/m') . '/' . $filename;
            $success = Storage::disk('public')->put($relativePath, $decodedContent);

            if (!$success) {
                throw new \Exception('No se pudo guardar el archivo');
            }

            Log::info('Archivo guardado exitosamente:', [
                'path' => $relativePath,
                'size' => strlen($decodedContent)
            ]);

            return Storage::disk('public')->url($relativePath);
        } catch (\Exception $e) {
            Log::error('Error guardando archivo:', [
                'filename' => $filename,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }*/

}; ?>

<div wire:init="primeraCargaDesdeMenu">
    @include('livewire.clientes.partials.comprobantes-redesign')
    @if(false)

    {{-- General --}}
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

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        /* Estados y badges */
        .badge {
            padding: 0.25rem 0.5rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
        }

        .badge-green {
            background-color: #d1fae5;
            color: #065f46;
        }

        .badge-yellow {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-red {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-gray {
            background-color: #e7e7ee;
            color: #1f2937;
        }

        .badge-blue {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .badge-comprobante {
            background-color: #0a53be;
            color: white;

        }

        /* Botones de documentos */
        .doc-buttons {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 0.25rem;
            font-size: 0.875rem;
            color: white;
            text-decoration: none;
            transition: background-color 0.2s;
        }

        .btn svg {
            width: 1rem;
            height: 1rem;
            margin-right: 0.25rem;
        }

        .btn-dark {
            background-color: #1f2937;
        }

        .btn-dark:hover {
            background-color: #374151;
        }

        .btn-indigo {
            background-color: #4f46e5;
        }

        .btn-indigo:hover {
            background-color: #4338ca;
        }

        .btn-info {
            background-color: #0a53be;
        }

        .btn-red {
            background-color: #dc2626;
        }

        .btn-red:hover {
            background-color: #b91c1c;
        }

        /* Espacio entre elementos */
        .space-x {
            margin: 0 0.25rem;
        }

        /* Mensaje de no resultados */
        .no-results {
            text-align: center;
            color: #6b7280;
            padding: 1rem;
        }
    </style>


    @if($error)
        <div class="alert-error">
            <p>{{ $error }}</p>
        </div>
    @endif




    {{-- el principio de unica carga hasta que tenga datos el listado --}}

    @if(count($comprobantesFiltrados)== 0 && $carga_ejecutada==false){{-- se mostrara esqueleto mientras el dispatch hace cargar primera vez los datos--}}

        <div class="w-full">
            <h2 class="text-2xl font-bold text-gray-900 flex justify-start mb-0">Mis Comprobantes </h2>

            <div style="margin-left: 40px; margin-top: 20px">
                <div class="loader"></div>
            </div>

            <br>
            {{-- esqueletito --}}
            <div class="w-full">
                <!-- Version de una sola columna -->
                <div class="w-full">
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 w-full">
                        <div class="flex items-center justify-between w-full">
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
                </div>
            </div>

            <br>

            <div class="w-full">
                <!-- Version de una sola columna  esqueleto -->
                <div class="w-full">
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 w-full">
                        <div class="flex items-center justify-between w-full">
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
                </div>
            </div>
        </div>

    @else








        {{-- Aqui empieza el loading con skeleton --}}
        <div wire:loading wire:target="obtenerComprobantes" class="w-full">
            <h2 class="text-2xl font-bold text-gray-900 flex justify-start mb-0">Mis Comprobantes </h2>

            <div style="margin-left: 40px; margin-top: 20px">
                <div class="loader"></div>
            </div>

            <br>
            {{-- esqueletito --}}
            <div class="w-full">
                <!-- Version de una sola columna -->
                <div class="w-full">
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 w-full">
                        <div class="flex items-center justify-between w-full">
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
                </div>
            </div>

            <br>

            <div class="w-full">
                <!-- Version de una sola columna  esqueleto -->
                <div class="w-full">
                    <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200 w-full">
                        <div class="flex items-center justify-between w-full">
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
                </div>
            </div>
        </div>

        {{--  Aqui empieza el liading.remove -> datos reales cargados --}}

        <div wire:loading.remove wire:target="obtenerComprobantes">

            <h2 class="text-2xl font-bold text-gray-900 flex justify-start mb-0">Mis Comprobantes
                <button wire:click="obtenerComprobantes" class="ml-3 bg-white rounded-3xl p-1.5 text-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                        <path fill-rule="evenodd"
                              d="M4.755 10.059a7.5 7.5 0 0 1 12.548-3.364l1.903 1.903h-3.183a.75.75 0 1 0 0 1.5h4.992a.75.75 0 0 0 .75-.75V4.356a.75.75 0 0 0-1.5 0v3.18l-1.9-1.9A9 9 0 0 0 3.306 9.67a.75.75 0 1 0 1.45.388Zm15.408 3.352a.75.75 0 0 0-.919.53 7.5 7.5 0 0 1-12.548 3.364l-1.902-1.903h3.183a.75.75 0 0 0 0-1.5H2.984a.75.75 0 0 0-.75.75v4.992a.75.75 0 0 0 1.5 0v-3.18l1.9 1.9a9 9 0 0 0 15.059-4.035.75.75 0 0 0-.53-.918Z"
                              clip-rule="evenodd"/>
                    </svg>
                </button>

            </h2>


            <br>

            <div class="bg-white p-6 rounded-lg shadow-md border border-gray-200">

                <!-- Sección de Filtros -->
                <div class="filters-container">
                    <style>
                        .filters-container {
                            margin-bottom: 20px;
                            padding: 15px;
                            background-color: #f8f9fa;
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

                        /* para ordenamiento por fecha y/o total */
                        .select-none {
                            user-select: none;
                        }

                        th[wire\:click] {
                            position: relative;
                        }

                        th[wire\:click]:hover {
                            background-color: #f3f4f6;
                        }

                    </style>

                    <div class="filters-grid">
                        <div class="filter-item">
                            <label class="filter-label">Fecha</label>
                            <input
                                type="date"
                                wire:model.live="filtroFecha"
                                class="filter-input"
                            >
                        </div>

                        <div class="filter-item">
                            <label class="filter-label">Número de Comprobante</label>
                            <input
                                type="text"
                                wire:model.live="filtroNumero"
                                placeholder="Buscar por número..."
                                class="filter-input"
                            >
                        </div>

                        <!-- <div class="filter-item">
                             <label class="filter-label">Monto Total</label>
                             <input
                                 type="number"
                                 wire :model.live="filtroMonto"
                                 step="0.01"
                                 class="filter-input"
                             >
                         </div> -->

                        <div class="filter-item">
                            <label class="filter-label">Tipo de Factura</label>
                            <select wire:model.live="filtroTipoServicio" class="filter-select">
                                <option value="">Todos</option>
                                <option value="ALMACENAMIENTO">Almacenamiento</option>
                                <option value="COMPLEMENTARIOS">Complementarios</option>
                            </select>
                        </div>

                        <div class="filter-item">
                            <label class="filter-label">Estado de Pago</label>
                            <select wire:model.live="filtroEstadoPago" class="filter-select">
                                <option value="">Todos</option>
                                <option value="paid">Pagado</option>
                                <option value="partial">Parcial</option>
                                <option value="not_paid">No Pagado</option>
                            </select>
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
                <!-- Fin seccion Filtros -->


                <div class="table-container">

                    <table>
                        <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Comprobante</th>
                            <th>F. Inicio</th>
                            <th>F. Fin</th>
                            <th class="text-center">Estado de Pago</th>
                            <th class="text-center">Estado Contable</th>
                            <th class="text-right">Vencimiento</th>
                            <!-- <th>dam</th> -->
                            {{--  <th wire:click="ordenar('invoice_date')" style="cursor: pointer" class="select-none">
                                 Fecha
                                 @if($ordenarPor === 'invoice_date')
                                     <span class="ml-1">
                                         @if($ordenAscendente) ↑ @else ↓ @endif
                                     </span>
                                 @endif
                             </th> --}}
                            <th wire:click="ordenar('amount_total')" style="cursor: pointer" class="text-right select-none">
                                Total
                                @if($ordenarPor === 'amount_total')
                                    <span class="ml-1">
                                                @if($ordenAscendente)
                                            ↑
                                        @else
                                            ↓
                                        @endif
                                            </span>
                                @endif
                            </th>
                            <!-- <th class="text-right">Pendiente</th> -->
                            <th class="text-center">Documentos</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($comprobantesFiltrados as $comprobante)
                            <tr class="font-semibold">
                                <td>
                                    <span class="badge badge-gray"
                                          style="font-size: 0.7rem">{{ $comprobante['service_type'] }} </span>
                                </td>
                                <td>
                                    @if($comprobante['name']=='/')
                                        <span class="badge badge-gray"> Sin Número </span>
                                    @else
                                        <span class="badge badge-comprobante"> {{ $comprobante['name'] }}</span>
                                    @endif
                                    <br>
                                </td>
                                <td> {{ $comprobante['date_start'] }}</td>
                                <td> {{ $comprobante['date_outlet'] }}</td>

                                @php

                                    $hoy = \Carbon\Carbon::now();
                                    //dd($hoy->format('d/m/Y'));

                                @endphp

                                <td class="text-center"> {{-- Estado de Pago --}}

                                    {{-- $comprobante['payment_state_'] --}}

                                    @if(in_array('paid', $comprobante['payment_state']))

                                        <span class="badge badge-green">Pagado</span>

                                    @elseif(in_array('not_paid', $comprobante['payment_state']) && (\Carbon\Carbon::parse($comprobante['date_outlet'])->format('d/m/Y')) < $hoy->format('d/m/Y'))

                                        <span class="badge badge-red">Vencido</span>

                                    @elseif(in_array('not_paid', $comprobante['payment_state']) && (\Carbon\Carbon::parse($comprobante['date_outlet'])->format('d/m/Y')) > $hoy->format('d/m/Y'))

                                        <span class="badge badge-yellow">No Pagado </span>

                                    @else

                                        <span class="badge badge-gray"> - </span>

                                    @endif

                                </td>

                                <td class="text-center"> {{--Estado Contable --}}

                                    @if(in_array('posted', $comprobante['state']))

                                        <span class="badge badge-blue">Factura</span>

                                    @elseif(in_array('cancel',$comprobante['state']))

                                        <span class="badge badge-red">Cancelado</span>

                                    @elseif(in_array('draft', $comprobante['state']))

                                        <span class="badge badge-gray">Recibo</span>

                                    @endif
                                </td>
                                <td class="text-center">  {{ $comprobante['vencimiento'] }} </td>
                                <!-- <td>{ { $comprobante['dam'] }}</td> -->
                                {{--  <td>{{ \Carbon\Carbon::parse($comprobante['invoice_date'])->format('d/m/Y') }}</td> --}}
                                <td class="text-right">S/ {{ number_format($comprobante['amount_total'], 2) }}</td>
                                <!-- <td class="text-right">S/ { { number_format($comprobante['amount_residual'], 2) }}</td> -->
                                <!-- Pendiente -->


                                <td>
                                    <div class="doc-buttons">
                                        @if(!empty($comprobante['ruta_xml']))
                                            <a href="{{ $comprobante['ruta_xml'] }}" target="_blank" class="btn btn-dark">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                                </svg>
                                                XML
                                            </a>
                                        @endif

                                        @if(!empty($comprobante['ruta_cdr']))
                                            <a href="{{ $comprobante['ruta_cdr'] }}" target="_blank" class="btn btn-indigo">
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                                CDR
                                            </a>
                                        @endif

                                        @if(!empty($comprobante['ruta_pdf']))
                                            <a href="{{ $comprobante['ruta_pdf'] }}" target="_blank"
                                                @class(['btn', 'btn-info'=>in_array('draft', $comprobante['state']), 'btn-red'=>!in_array('draft', $comprobante['state'])])
                                            >
                                                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                          d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                                </svg>
                                                @if($comprobante['name']!=='/')
                                                    Factura
                                                @else
                                                    Recibo
                                                @endif
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="no-results">
                                    No se encontraron comprobantes // con los filtros seleccionados
                                </td>
                            </tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Estilos para la Paginacion -->
                <style>
                    /* ... tus estilos anteriores ... */

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
                </style>

                <!-- Estructura de la paginacon xD -->
                <div class="pagination-container">
                    <div class="pagination-info">
                        Mostrando {{ ($comprobantesFiltrados->count() > 0) ? (($page - 1) * $porPagina) + 1 : 0 }}
                        - {{ min($page * $porPagina, $total) }}
                        de {{ $total }} comprobantes
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



    @endif


    @endif
</div>
