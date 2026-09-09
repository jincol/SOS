<?php

use Livewire\Volt\Component;

new class extends Component {
    //


    public $ordenSeleccionada;

    public function mount($ordenSeleccionada_){

        $this->ordenSeleccionada = $ordenSeleccionada_;
    }

    public function cerrarComponente(){

      $this->dispatch('cerrarModalOrdenIngreso');

    }



}; ?>

<div
    class="order-detail-modal"
    x-data="{ modalOpen: true }"
    x-show="modalOpen"
    @keydown.escape.window="if (modalOpen) { modalOpen = false; $dispatch('cerrarModalOrdenIngreso') }"
>
    <div class="relative z-10" aria-labelledby="dialog-title" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true" @click="if (modalOpen) { modalOpen = false; $dispatch('cerrarModalOrdenIngreso') }"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                {{--
                    Modal Panel - Ajustado para ser más grande en desktop
                    - sm:max-w-lg (original) -> sm:max-w-6xl (nuevo)
                    - Añadido w-full para que usetodo el ancho disponible
                    - En móvil sigue siendo responsive
                --}}
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all w-full sm:my-8 sm:w-full sm:max-w-6xl lg:max-w-7xl">

                    <!-- Header del Modal -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:size-10">
                                <!-- Cambiado el ícono a uno más apropiado para detalles -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                    <path fill-rule="evenodd" d="M9.75 6.75h-3a3 3 0 0 0-3 3v7.5a3 3 0 0 0 3 3h7.5a3 3 0 0 0 3-3v-7.5a3 3 0 0 0-3-3h-3V1.5a.75.75 0 0 0-1.5 0v5.25Zm0 0h1.5v5.69l1.72-1.72a.75.75 0 1 1 1.06 1.06l-3 3a.75.75 0 0 1-1.06 0l-3-3a.75.75 0 1 1 1.06-1.06l1.72 1.72V6.75Z" clip-rule="evenodd" />
                                    <path d="M7.151 21.75a2.999 2.999 0 0 0 2.599 1.5h7.5a3 3 0 0 0 3-3v-7.5c0-1.11-.603-2.08-1.5-2.599v7.099a4.5 4.5 0 0 1-4.5 4.5H7.151Z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg font-semibold text-gray-900" id="dialog-title">
                                    Detalle Orden Nro: {{$ordenSeleccionada['name']}}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Información completa de la orden seleccionada</p>
                            </div>
                            <!-- Botón cerrar en el header -->
                            <button
                                type="button"
                                aria-label="Cerrar detalle"
                                @click="if (modalOpen) { modalOpen = false; $dispatch('cerrarModalOrdenIngreso') }"
                                class="ml-auto text-gray-400 hover:text-gray-600 focus:outline-none sm:ml-4"
                            >
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Contenido del Modal - Ahora con más espacio -->
                    <div class="bg-white px-4 py-6 sm:px-6 max-h-[70vh] overflow-y-auto">

                        <!-- Grid de 2 columnas en desktop, 1 en móvil -->

                        @if($ordenSeleccionada['warehouse_system_rent']!==false)
                            <div class="text-sm font-medium mt-3 text-start sm:mt-0  sm:text-left flex bg-gray-100 rounded-lg p-3" >
                                Orden de alquiler vinculada : <span class="ml-1 text-bold"> {{$ordenSeleccionada['warehouse_system_rent']}}</span>
                            </div>
                        @else
                            <div class="text-sm font-medium mt-3 text-start sm:mt-0  sm:text-left flex bg-gray-100 rounded-lg p-3" >
                                Sin Orden de Alquiler vinculada.
                            </div>
                        @endif
                        <br>

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                            <!-- Columna 1: Información General -->
                            <div class="bg-gray-100 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Detalles de Ingreso I
                                </h4>

                                <div class="space-y-1">

                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Tipo de Ingreso</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['tipo_ingreso'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Almacenero</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['almacenero'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Company</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['company'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sucursal</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['sucursal'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha y Hora de creacion de la Orden</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['creation_datetime'] ?? 'No registrado'}}</p>
                                    </div>

                                </div>
                            </div>

                            <!-- Columna 2: Información del Cliente -->
                            <div class="bg-gray-100 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Detalles de Ingreso II
                                </h4>

                                <div class="space-y-1">

                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">RUC</label>
                                        <p class="text-sm font-semibold text-gray-900 font-mono">{{$ordenSeleccionada['consignatario_ruc'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Consignatario</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['consignatario'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Creador de la Orden</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['user'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Aprobador de la Orden</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['finished_user'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha y Hora de Activación</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['finished_datetime'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Ejecutivo Comercial</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['comm_exec_employee'] ?? 'No registrado'}}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Sección adicional detalle de los Tabs -->
                        <div class="bg-white border-gray-200">


                            <!-- Grid de 2 columnas para información adicional -->
                            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">

                                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                                    <div class="p-1" x-data="{ xactiveTab: 'aduanero'}">

                                        <div class="flex space-x-1 mb-6 bg-gray-100 p-1 rounded-lg">
                                            @if($ordenSeleccionada['dam_dua']!==null)
                                                <button @click="xactiveTab = 'aduanero'"
                                                        class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200" :class="xactiveTab==='aduanero' ? 'tab-active' : 'tab-inactive' ">
                                                    Aduanero
                                                </button>
                                            @endif
                                            <button @click="xactiveTab='fletes'"
                                                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200" :class="xactiveTab==='fletes' ? 'tab-active' : 'tab-inactive' ">
                                                Flete
                                            </button>

                                            <button @click="xactiveTab = 'contenedores'"
                                                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200 " :class="xactiveTab==='contenedores' ? 'tab-active' : 'tab-inactive' ">
                                                Contenedores
                                            </button>

                                            <button @click="xactiveTab = 'vehiculos'"
                                                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200" :class="xactiveTab==='vehiculos' ? 'tab-active' : 'tab-inactive' ">
                                                Vehículos
                                            </button>
                                            <button @click="xactiveTab='series'"
                                                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200" :class="xactiveTab==='series' ? 'tab-active' : 'tab-inactive' ">
                                                Series
                                            </button>
                                        </div>

                                        <div class="">


                                            <div x-show="xactiveTab==='aduanero'" class="p-4">
                                                <div class="min-h-96">
                                                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                                                        <div class="bg-gray-100 rounded-lg p-4">

                                                            <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                                Documentos Aduaneros
                                                            </h4>
                                                            <hr>
                                                            <div class="space-y-1 mt-2">

                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">DAM</label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['dam_aduana']) ? : '###'}} - {{ trim($ordenSeleccionada['dam_anio']) ? : '###'}} -
                                                                        {{trim($ordenSeleccionada['dam_regimen']) ? : '##'}} - <span class="pl-2 pr-2 pt-0.5 pb-0.5 bg-gray-500 text-white rounded-lg">{{ trim($ordenSeleccionada['dam_dua']) ? : '####' }}</span>
                                                                    </p>
                                                                </div>

                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">MIC</label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['mic_manifesto']) ? : '###'}} - {{ trim($ordenSeleccionada['mic_segundo_digito']) ? : '###'}} -
                                                                        {{trim($ordenSeleccionada['mic_tercer_digito']) ? : '##'}} - <span class="pl-2 pr-2 pt-0.5 pb-0.5 bg-gray-500 text-white rounded-lg">{{ trim($ordenSeleccionada['mic_numero_orden']) ? : '####' }}</span>
                                                                    </p>
                                                                </div>

                                                            </div>

                                                        </div>
                                                        <div class="bg-gray-100 rounded-lg p-4">

                                                            <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                                Detalle de Aduanaje
                                                            </h4>
                                                            <hr>

                                                            <div class="space-y-1 mt-2">

                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Color de Canal</label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['channel_color']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Tipo de Régimen</label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['dam_regimen']) ? : '##'}} - {{trim($ordenSeleccionada['type_regime']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Fecha de Numeracion</label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{ $ordenSeleccionada['fecha_numeracion'] ? date('d/m/Y', strtotime($ordenSeleccionada['fecha_numeracion'])) : '#####' }}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Fecha de Vencimiento</label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{ $ordenSeleccionada['fecha_vencimiento'] ? date('d/m/Y', strtotime($ordenSeleccionada['fecha_vencimiento'])) : '#####' }}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Consignatario</label>
                                                                    <p class="text-sm font-semibold text-gray-900 font-mono">
                                                                        {{$ordenSeleccionada['consignatario_ruc'] ?? 'No registrado'}} -
                                                                        {{$ordenSeleccionada['consignatario'] ?? 'No registrado'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Código Agente de Aduana </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['codigo_agente_aduana']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Agente de Aduana </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['agente_aduana']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Valor FOB </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['valor_fob']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Valor CIF </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['valor_cif']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Cantidad de contenedores </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['number_containers_dua']) ? : '##'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Pais de Procedencia </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['country_origin']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">BL </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['bl_dua']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Fact. Comercial </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{trim($ordenSeleccionada['fac_comercial']) ? : '#####'}}
                                                                    </p>
                                                                </div>
                                                                <div>
                                                                    <label class="text-xs font-medium text-gray-500 tracking-wide">Fecha Fact. Comercial </label>
                                                                    <p class="text-sm font-semibold text-gray-900">
                                                                        {{ $ordenSeleccionada['date_fac_comercial'] ? date('d/m/Y', strtotime($ordenSeleccionada['date_fac_comercial'])) : '#####' }}
                                                                    </p>
                                                                </div>
                                                            </div>


                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div x-show="xactiveTab==='fletes'" class="p-4">
                                            <div class="min-h-96">
                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                    <h4 class="ml-3 text-sm uppercase font-bold text-gray-800 mb-4 flex items-center">
                                                        STOCK INICIAL
                                                    </h4>

                                                    {{--
                                                    @if(isset($ordenSeleccionada['fletes']))
                                                        <div>✅ Fletes existe. Cantidad: {{ count($ordenSeleccionada['fletes']) }}</div>
                                                    @else
                                                        <div>❌ Fletes NO existe</div>
                                                        <div>Claves disponibles: {{ implode(', ', array_keys($ordenSeleccionada)) }}</div>
                                                    @endif
                                                    --}}

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-12 gap-1">
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Secuencia</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Forma de Ingreso</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Tipo de Mercancía</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Contenedor</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Vehículo</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Bultos Mat.</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Bultos Com.</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Pallets</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Peso Mat.</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Peso Bruto</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Peso Seco</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Peso Carga</div>
                                                        </div>


                                                        @foreach($ordenSeleccionada['fletes'] as $index=>$flete)
                                                            <div class="grid grid-cols-12 gap-1">
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['serie']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['type_income']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $ordenSeleccionada['type_merchandise_simple']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['number_containers']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['number_vehicles']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['manifested_number_bundles']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['number_bundles']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['number_pallets']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['manifested_weight']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['gross_weight']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['useful_weight']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $flete['neto_weight']}}</div>
                                                            </div>
                                                            <div class="grid grid-cols-1">
                                                                <div class="border-gray-200 border text-gray-900 text-start font-semibold rounded-sm p-2 text-xs">
                                                                    <span class="text-gray-500">Observación : </span>
                                                                    {{ trim($flete['observation']) ? : ''}}</div>
                                                            </div>
                                                            <br>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div x-show="xactiveTab==='contenedores'" class="p-4">
                                            <div class="min-h-96 ">
                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                    <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                        CONTENEDORES NETO
                                                    </h4>
                                                    <hr>

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-3 gap-1">
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Serie de Contenedor</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Serie Única</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Estado</div>
                                                        </div>

                                                        @foreach($ordenSeleccionada['contenedores_neto'] as $index=>$contenedor_neto)
                                                            <div class="grid grid-cols-3 gap-1">
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs"> {{ $contenedor_neto['container_serie']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs"> {{ $contenedor_neto['simple_serie']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs"> {{ $contenedor_neto['state']}}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <br>

                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                    <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                        STOCK INICIAL
                                                    </h4>
                                                    <hr>

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-8 gap-1">
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Serie Única</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Tipo de Contenedor</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Serie de Contenedor</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Precintos</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Bultos Manifestados</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Bultos Recepcionados</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Tipos de Mercadería</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Observación</div>
                                                        </div>
                                                        @foreach($ordenSeleccionada['contenedores_inicial'] as $index=>$contenedor_inicial)
                                                            <div class="grid grid-cols-8 gap-1">
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $contenedor_inicial['container_freight'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $contenedor_inicial['type_container_id'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $contenedor_inicial['container_serie'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ __('No registrado') }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $contenedor_inicial['manifested_bundles'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $contenedor_inicial['number_bundles'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $contenedor_inicial['type_merchandise_ids'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $contenedor_inicial['observation'] }}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>


                                            </div>
                                        </div>


                                        <div x-show="xactiveTab==='vehiculos'" class="p-4">
                                            <div class="min-h-96 ">
                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                    <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                        VEHÍCULOS NETOS
                                                    </h4>
                                                    <hr>

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-4 gap-1">
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Serie Única</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">VIN/Chasis</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Categría de Vehiculo</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Estado</div>
                                                        </div>


                                                        <div class="grid grid-cols-4 gap-1">
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">  </div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">  </div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">  </div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">  </div>
                                                        </div>

                                                    </div>
                                                </div>

                                                <br>

                                                <div class="bg-gray-50 p-4 rounded-lg">

                                                    <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                        VEHÍCULOS INICIALES
                                                    </h4>
                                                    <hr>

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-8 gap-1">
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Categría de Vehiculo</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Tipo de Vehículo</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Serie Única</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">VIN/Chasis</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Marca</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Modelo</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Año</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Observación</div>
                                                        </div>
                                                        <div class="grid grid-cols-8 gap-1">
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">###</div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">###</div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">###</div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">###</div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">###</div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">###</div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">###</div>
                                                            <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">#### ##### ##</div>
                                                        </div>

                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                        <div x-show="xactiveTab==='series'" class="p-4">
                                            <div class="min-h-96 ">

                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                    <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                        STOCK NETO
                                                    </h4>
                                                    <hr>

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-3 gap-1">
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Serie</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Bultos</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Pallets</div>
                                                        </div>

                                                        @foreach($ordenSeleccionada['series_neto'] as $index=>$serie_neto)
                                                            <div class="grid grid-cols-3 gap-1">
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $serie_neto['serie']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $serie_neto['number_bundles']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $serie_neto['number_pallets']}}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                                <br>

                                                <div class="bg-gray-50 p-4 rounded-lg">
                                                    <h4 class="text-sm uppercase font-semibold text-gray-800 mb-4 flex items-center">
                                                        STOCK INICIAL
                                                    </h4>
                                                    <hr>

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-6 gap-1">
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Serie Única</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Contenedores</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Descripción de la Mercadería</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Partida Arancelaria</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Bultos</div>
                                                            <div class="font-medium text-gray-900 text-center rounded-sm p-2 text-xs">Pallets</div>
                                                        </div>
                                                        @foreach($ordenSeleccionada['series_inicial'] as $index=>$serie_inicial)
                                                            <div class="grid grid-cols-6 gap-1">
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{$serie_inicial['serie']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{$serie_inicial['freight_container_simple_id']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{$serie_inicial['description_merchandise']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{$serie_inicial['tariff_item']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{$serie_inicial['bundles']}}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{$serie_inicial['pallets']}}</div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                </div>

                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>



                        </div>


                    </div>

                    <!-- Footer del Modal -->
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6 border-t border-gray-200">

                        <button
                            type="button"
                            @click="if (modalOpen) { modalOpen = false; $dispatch('cerrarModalOrdenIngreso') }"
                            class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-gray-300 ring-inset hover:bg-gray-50 sm:mt-0 sm:w-auto"
                        >
                            <div wire:loading.remove wire:target="cerrarComponente">
                                Cerrar
                            </div>

                            <div wire:loading wire:target="cerrarComponente">
                                <span class="loader_a"></span>
                            </div>



                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
