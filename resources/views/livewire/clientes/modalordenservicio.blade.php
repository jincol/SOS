<?php

use Livewire\Volt\Component;

new class extends Component {


    public $ordenSeleccionada;

    public function mount($ordenSeleccionada_){

        $this->ordenSeleccionada = $ordenSeleccionada_;
    }

    public function cerrarComponente(){

        $this->dispatch('cerrarModalOrdenServicio');

    }


}; ?>

<div>

    <div class="relative z-10" aria-labelledby="dialog-title" role="dialog" aria-modal="true">
        <!-- Background backdrop -->
        <div class="fixed inset-0 bg-gray-500/75 transition-opacity" aria-hidden="true"></div>

        <div class="fixed inset-0 z-10 w-screen overflow-y-auto">
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">

                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all w-full sm:my-8 sm:w-full sm:max-w-6xl lg:max-w-7xl">

                    <!-- Header del Modal -->
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-200">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex size-12 shrink-0 items-center justify-center rounded-full bg-blue-100 sm:mx-0 sm:size-10">
                                <!-- Cambiado el ícono a uno más apropiado para detalles -->
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="size-6">
                                    <path d="M3.375 4.5C2.339 4.5 1.5 5.34 1.5 6.375V13.5h12V6.375c0-1.036-.84-1.875-1.875-1.875h-8.25ZM13.5 15h-12v2.625c0 1.035.84 1.875 1.875 1.875h.375a3 3 0 1 1 6 0h3a.75.75 0 0 0 .75-.75V15Z" />
                                    <path d="M8.25 19.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0ZM15.75 6.75a.75.75 0 0 0-.75.75v11.25c0 .087.015.17.042.248a3 3 0 0 1 5.958.464c.853-.175 1.522-.935 1.464-1.883a18.659 18.659 0 0 0-3.732-10.104 1.837 1.837 0 0 0-1.47-.725H15.75Z" />
                                    <path d="M19.5 19.5a1.5 1.5 0 1 0-3 0 1.5 1.5 0 0 0 3 0Z" />
                                </svg>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left flex-1">
                                <h3 class="text-lg font-semibold text-gray-900" id="dialog-title">
                                    Detalle Orden Servicio Nro: {{$ordenSeleccionada['name']}}
                                </h3>
                                <p class="text-sm text-gray-500 mt-1">Información completa de la orden seleccionada</p>
                            </div>
                            <!-- Botón cerrar en el header -->
                            <button
                                wire:click="cerrarComponente"
                                class="ml-auto text-gray-400 hover:text-gray-600 focus:outline-none sm:ml-4"
                            >
                                <svg class="size-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <div wire:loading wire:target="cerrarComponente">
                                    <span class="loader_a"></span>
                                </div>
                            </button>
                        </div>
                    </div>

                    <!-- Contenido del Modal  -->
                    <div class="bg-white px-4 py-6 sm:px-6 max-h-[70vh] overflow-y-auto">

                        <!-- Grid de 2 columnas en desktop, 1 en móvil -->

                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">

                            <!-- Columna 1: Información General -->
                            <div class="bg-gray-100 rounded-lg p-4">
                                <h4 class="text-md font-semibold text-gray-800 mb-4 flex items-center">
                                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    {{$ordenSeleccionada['name']}}
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
                                    {{--
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Almacenero Responsable</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['finished_user'] ?? 'No registrado'}}</p>
                                    </div>


                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Última fecha de orden de Salida</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['finished_datetime'] ?? 'No registrado'}}</p>
                                    </div>
                                    --}}
                                </div>
                            </div>

                            <!-- Columna 2: Información del Cliente -->
                            <div class="bg-gray-100 rounded-lg p-4">

                                <div class="space-y-1">
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Compañia</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['company'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Sucursal</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['branch'] ?? 'No registrado'}}</p>
                                    </div>
                                    {{--
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Lista de Precios</label>
                                        <p class="text-sm font-semibold text-gray-900 font-mono">No registrado</p>
                                    </div>
                                    --}}
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha y Hora de creacion de la Orden</label>
                                        <p class="text-sm font-semibold text-gray-900">
                                            @if(isset($ordenSeleccionada['creation_datetime']))
                                                {{ \Carbon\Carbon::parse($ordenSeleccionada['creation_datetime'])->subHours(5)->format('d/m/Y H:i:s') }}
                                            @else
                                                No registrado
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Aprobador de la orden</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['finished_user'] ?? 'No registrado'}}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Fecha y Hora de Activación</label>
                                        <p class="text-sm font-semibold text-gray-900">
                                            @if(isset($ordenSeleccionada['finished_datetime']))
                                                {{ \Carbon\Carbon::parse($ordenSeleccionada['finished_datetime'])->subHours(5)->format('d/m/Y H:i:s') }}
                                            @else
                                                No registrado
                                            @endif
                                        </p>
                                    </div>
                                    <div>
                                        <label class="text-xs font-medium text-gray-500 uppercase tracking-wide">Registrado por</label>
                                        <p class="text-sm font-semibold text-gray-900">{{$ordenSeleccionada['user'] ?? 'No registrado'}}</p>
                                    </div>

                                </div>
                            </div>
                        </div>

                        <!-- Sección adicional detalle de los Tabs -->
                        <div class="bg-white border-gray-200">



                            <div class="grid grid-cols-1 md:grid-cols-1 gap-6">

                                <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                                    <div class="p-1" x-data="{ xactiveTab: 'items'}">

                                        <div class="flex space-x-1 mb-6 bg-gray-100 p-1 rounded-lg">
                                            <button @click="xactiveTab='items'"
                                                    class="flex-1 py-2 px-4 rounded-md text-sm font-medium transition-colors duration-200" :class="xactiveTab==='transporte' ? 'tab-active' : 'tab-inactive' ">
                                                1. Items
                                            </button>

                                        </div>



                                        <div x-show="xactiveTab==='items'" class="p-4">
                                            <div class="min-h-96">
                                                <div class="bg-gray-50 p-4 rounded-lg">

                                                    <div class="space-y-2">

                                                        <div class="grid grid-cols-3 gap-1">
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Producto</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Proveedor</div>
                                                            <div class="font-medium text-gray-500 text-center rounded-sm p-2 text-xs">Cantidad</div>

                                                        </div>


                                                        @foreach($ordenSeleccionada['warehouse_system_service_line_ids'] as $index=>$reg)
                                                            <div class="grid grid-cols-3 gap-1">
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $reg['product_id'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $reg['partner_id'] }}</div>
                                                                <div class="border-gray-200 border text-gray-900 text-center font-semibold rounded-sm p-2 text-xs">{{ $reg['amount']}}</div>
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
                            wire:click="cerrarComponente"
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

