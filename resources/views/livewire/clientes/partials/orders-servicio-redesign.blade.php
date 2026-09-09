<section class="orders-screen" aria-label="Listado de órdenes de servicio">
    <div class="orders-progress" wire:loading aria-hidden="true"><span></span></div>
    @if($error)
        <div class="notice notice-danger" role="alert"><x-sos-icon name="alert" /><div><strong>No pudimos cargar las órdenes</strong><span>{{ $error }}</span></div></div>
    @endif

    @if(count($ordenesFiltradas) === 0 && $carga_ejecutada === false)
        @include('livewire.clientes.partials.orders-loading')
    @else
        <div wire:loading wire:target="obtenerOrdenesServicio" class="w-full">@include('livewire.clientes.partials.orders-loading')</div>
        <div wire:loading.remove wire:target="obtenerOrdenesServicio">
            <div class="orders-toolbar">
                <div class="orders-result-summary"><span class="orders-count">{{ $total }}</span><div><strong>{{ $total === 1 ? 'orden encontrada' : 'órdenes encontradas' }}</strong><span>Servicios operativos y documentos vinculados</span></div></div>
                <button class="btn btn-secondary btn-sm" type="button" wire:click="obtenerOrdenesServicio"><x-sos-icon name="refresh" class="icon-sm" /><span>Actualizar</span></button>
            </div>

            <div class="card filters-card orders-filters" x-data="{ advanced: {{ ($filtroCompany || $filtroConsignatario || $filtroSucursal || $filtroUsuario) ? 'true' : 'false' }} }">
                <div class="orders-filters-main">
                    <div class="field orders-search-field"><label for="servicio-orden">Buscar orden</label><div class="search-control"><x-sos-icon name="search" class="icon-sm" /><input id="servicio-orden" class="control" type="search" wire:model.live.debounce.350ms="filtroOrden" placeholder="Código de servicio" autocomplete="off"></div></div>
                    <div class="field"><label for="servicio-fecha">Fecha de creación</label><input id="servicio-fecha" class="control" type="date" wire:model.live="filtroFecha"></div>
                    <div class="field"><label for="servicio-descripcion">Descripción</label><input id="servicio-descripcion" class="control" type="search" wire:model.live.debounce.350ms="filtroDescripcion" placeholder="Concepto del servicio"></div>
                    <div class="orders-filter-actions">
                        <button class="btn btn-quiet btn-sm" type="button" @click="advanced = !advanced" :aria-expanded="advanced"><x-sos-icon name="filter" class="icon-sm" /><span x-text="advanced ? 'Menos filtros' : 'Más filtros'"></span></button>
                        <button class="btn btn-secondary btn-sm" type="button" wire:click="resetFiltros">Limpiar</button>
                    </div>
                </div>
                <div class="orders-filters-advanced" x-cloak x-show="advanced" x-collapse>
                    <div class="field"><label for="servicio-company">Compañía operadora</label><input id="servicio-company" class="control" type="search" wire:model.live.debounce.350ms="filtroCompany" placeholder="Nombre de la compañía"></div>
                    <div class="field"><label for="servicio-consignatario">Consignatario</label><input id="servicio-consignatario" class="control" type="search" wire:model.live.debounce.350ms="filtroConsignatario" placeholder="Nombre o razón social"></div>
                    <div class="field"><label for="servicio-sucursal">Sucursal</label><input id="servicio-sucursal" class="control" type="search" wire:model.live.debounce.350ms="filtroSucursal" placeholder="Nombre de la sucursal"></div>
                    <div class="field"><label for="servicio-usuario">Registrado por</label><input id="servicio-usuario" class="control" type="search" wire:model.live.debounce.350ms="filtroUsuario" placeholder="Nombre del usuario"></div>
                </div>
            </div>

            <div class="card list-card orders-list-card">
                <div class="table-wrap">
                    <table class="data-table orders-table">
                        <thead><tr>
                            <th><button class="sort-button" type="button" wire:click="ordenar('name')">Orden @if($ordenarPor === 'name') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                            <th><button class="sort-button" type="button" wire:click="ordenar('creation_datetime')">Fecha @if($ordenarPor === 'creation_datetime') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                            <th>Registrado por</th><th>Servicio</th><th>Consignatario</th><th>Sucursal</th><th>Estado</th><th class="align-right">Acciones</th>
                        </tr></thead>
                        <tbody>
                            @forelse($ordenesFiltradas as $orden)
                                @php
                                    $rawState = strtolower((string) ($orden['state'] ?? ''));
                                    $stateClass = in_array($rawState, ['done', 'finished', 'completed', 'finalizado', 'cerrado'], true) ? 'success' : (in_array($rawState, ['cancel', 'cancelled', 'cancelado'], true) ? 'danger' : 'warning');
                                    $stateLabel = match($rawState) { 'done', 'finished', 'completed', 'finalizado', 'cerrado' => 'Finalizada', 'cancel', 'cancelled', 'cancelado' => 'Cancelada', 'draft', 'borrador' => 'Borrador', default => ($orden['state'] ?: 'Sin estado') };
                                    $description = trim(strip_tags((string) ($orden['narration'] ?? '')));
                                @endphp
                                <tr wire:key="servicio-{{ $orden['name'] }}">
                                    <td data-label="Orden"><span class="cell-primary order-code">{{ $orden['name'] ?: 'Sin código' }}</span><span class="cell-secondary cell-clip">{{ $orden['company'] ?: 'Compañía no registrada' }}</span></td>
                                    <td data-label="Fecha"><span class="cell-primary">{{ !empty($orden['creation_datetime']) ? \Carbon\Carbon::parse($orden['creation_datetime'])->format('d/m/Y') : 'No registrada' }}</span><span class="cell-secondary">{{ !empty($orden['creation_datetime']) ? \Carbon\Carbon::parse($orden['creation_datetime'])->format('H:i') : 'Hora no registrada' }}</span></td>
                                    <td data-label="Registrado por"><span class="cell-clip" title="{{ $orden['user'] }}">{{ $orden['user'] ?: 'No registrado' }}</span></td>
                                    <td data-label="Servicio"><span class="cell-clip order-description" title="{{ $description }}">{{ $description ?: 'Sin descripción' }}</span></td>
                                    <td data-label="Consignatario"><span class="cell-clip" title="{{ $orden['consignatario'] }}">{{ $orden['consignatario'] ?: 'No registrado' }}</span></td>
                                    <td data-label="Sucursal"><span class="cell-clip" title="{{ $orden['branch'] }}">{{ $orden['branch'] ?: 'No registrada' }}</span></td>
                                    <td data-label="Estado"><span class="badge {{ $stateClass }}">{{ $stateLabel }}</span></td>
                                    <td data-label="Acciones"><div class="row-actions">
                                        @if(count($orden['account_move_ids'] ?? []) > 0)
                                            <button class="btn btn-quiet btn-sm action-icon" type="button" wire:click="VerModalFacturas('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="VerModalFacturas('{{ $orden['name'] }}')" title="Ver facturas" aria-label="Ver facturas de {{ $orden['name'] }}"><x-sos-icon name="receipt" class="icon-sm" /></button>
                                        @endif
                                        <button class="btn btn-secondary btn-sm" type="button" wire:click="verDetalleOrdenServicio('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="verDetalleOrdenServicio('{{ $orden['name'] }}')"><x-sos-icon name="eye" class="icon-sm" /><span>Ver detalle</span></button>
                                    </div></td>
                                </tr>
                            @empty
                                <tr class="empty-row"><td colspan="8"><div class="empty-state"><span class="empty-icon"><x-sos-icon name="search" /></span><h3>No encontramos órdenes</h3><p>Prueba con otros criterios o limpia los filtros.</p><button class="btn btn-secondary btn-sm" type="button" wire:click="resetFiltros">Limpiar filtros</button></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('livewire.clientes.partials.orders-pagination')
            </div>
        </div>
    @endif

    @if($showModalDetalleOrdenServicio)
        <livewire:clientes.modalordenservicio :ordenSeleccionada_="$ordenSeleccionada" />
    @endif

    @if($verFacturas)
        @include('livewire.clientes.partials.orders-invoice-modal', [
            'closeAction' => 'CerrarModalFacturas',
            'modalTitle' => 'Facturas relacionadas',
            'selectedOrderName' => $ordenSeleccionadaFacturas['name'] ?? '',
            'selectedInvoices' => $ordenSeleccionadaFacturas['account_move_ids'] ?? [],
        ])
    @endif
</section>
