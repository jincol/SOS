<section class="orders-screen" aria-label="Listado de órdenes de alquiler">
    <div class="orders-progress" wire:loading aria-hidden="true"><span></span></div>
    @if($error)
        <div class="notice notice-danger" role="alert"><x-sos-icon name="alert" /><div><strong>No pudimos cargar las órdenes</strong><span>{{ $error }}</span></div></div>
    @endif

    @if(count($ordenesFiltradas) === 0 && $carga_ejecutada === false)
        @include('livewire.clientes.partials.orders-loading')
    @else
        <div wire:loading wire:target="obtenerOrdenesAlquiler" class="w-full">@include('livewire.clientes.partials.orders-loading')</div>
        <div wire:loading.remove wire:target="obtenerOrdenesAlquiler">
            <div class="orders-toolbar">
                <div class="orders-result-summary"><span class="orders-count">{{ $total }}</span><div><strong>{{ $total === 1 ? 'orden encontrada' : 'órdenes encontradas' }}</strong><span>Espacios, ocupación y documentos vinculados</span></div></div>
                <button class="btn btn-secondary btn-sm" type="button" wire:click="obtenerOrdenesAlquiler" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="obtenerOrdenesAlquiler">
                    <x-sos-icon name="refresh" class="icon-sm" />
                    <span wire:loading.remove wire:target="obtenerOrdenesAlquiler">Actualizar</span>
                    <span wire:loading wire:target="obtenerOrdenesAlquiler">Actualizando…</span>
                </button>
            </div>

            <div class="card filters-card orders-filters" x-data="{ advanced: {{ ($filtroCompany || $filtroSucursal || $filtroUsuario) ? 'true' : 'false' }} }">
                <div class="orders-filters-main">
                    <div class="field orders-search-field"><label for="alquiler-orden">Buscar orden</label><div class="search-control"><x-sos-icon name="search" class="icon-sm" /><input id="alquiler-orden" class="control" type="search" wire:model.live.debounce.350ms="filtroOrden" placeholder="Código de alquiler" autocomplete="off"></div></div>
                    <div class="field"><label for="alquiler-fecha">Fecha de creación</label><input id="alquiler-fecha" class="control" type="date" wire:model.live="filtroFecha"></div>
                    <div class="field"><label for="alquiler-consignatario">Consignatario</label><input id="alquiler-consignatario" class="control" type="search" wire:model.live.debounce.350ms="filtroConsignatario" placeholder="Nombre o RUC"></div>
                    <div class="orders-filter-actions">
                        <button class="btn btn-quiet btn-sm" type="button" @click="advanced = !advanced" :aria-expanded="advanced"><x-sos-icon name="filter" class="icon-sm" /><span x-text="advanced ? 'Menos filtros' : 'Más filtros'"></span></button>
                        <button class="btn btn-secondary btn-sm" type="button" wire:click="resetFiltros">Limpiar</button>
                    </div>
                </div>
                <div class="orders-filters-advanced" x-cloak x-show="advanced" x-collapse>
                    <div class="field"><label for="alquiler-company">Compañía operadora</label><input id="alquiler-company" class="control" type="search" wire:model.live.debounce.350ms="filtroCompany" placeholder="Nombre de la compañía"></div>
                    <div class="field"><label for="alquiler-sucursal">Sucursal</label><input id="alquiler-sucursal" class="control" type="search" wire:model.live.debounce.350ms="filtroSucursal" placeholder="Nombre de la sucursal"></div>
                    <div class="field"><label for="alquiler-usuario">Usuario</label><input id="alquiler-usuario" class="control" type="search" wire:model.live.debounce.350ms="filtroUsuario" placeholder="Responsable de registro"></div>
                </div>
            </div>

            <div class="card list-card orders-list-card">
                <div class="table-wrap">
                    <table class="data-table orders-table orders-table-alquiler">
                        <thead><tr>
                            <th><button class="sort-button" type="button" wire:click="ordenar('name')">Orden @if($ordenarPor === 'name') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                            <th><button class="sort-button" type="button" wire:click="ordenar('creation_datetime')">Fecha @if($ordenarPor === 'creation_datetime') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                            <th>Consignatario</th><th>Área ocupada</th><th>Bultos</th><th>Sucursal</th><th>Estado</th><th class="align-right">Acciones</th>
                        </tr></thead>
                        <tbody>
                            @forelse($ordenesFiltradas as $orden)
                                @php
                                    $rawState = strtolower((string) ($orden['state'] ?? ''));
                                    $stateClass = in_array($rawState, ['done', 'finished', 'completed', 'finalizado', 'cerrado'], true) ? 'success' : (in_array($rawState, ['cancel', 'cancelled', 'cancelado'], true) ? 'danger' : 'warning');
                                    $stateLabel = match($rawState) { 'done', 'finished', 'completed', 'finalizado', 'cerrado' => 'Finalizada', 'cancel', 'cancelled', 'cancelado' => 'Cancelada', 'draft', 'borrador' => 'Borrador', default => ($orden['state'] ?: 'Sin estado') };
                                @endphp
                                <tr wire:key="alquiler-{{ $orden['name'] }}">
                                    <td data-label="Orden"><span class="cell-primary order-code">{{ $orden['name'] ?: 'Sin código' }}</span><span class="cell-secondary rental-company" title="{{ $orden['company_id'] }}">{{ $orden['company_id'] ?: 'Compañía no registrada' }}</span></td>
                                    <td data-label="Fecha"><span class="cell-primary">{{ !empty($orden['creation_datetime']) ? \Carbon\Carbon::parse($orden['creation_datetime'])->format('d/m/Y') : 'No registrada' }}</span><span class="cell-secondary">{{ !empty($orden['creation_datetime']) ? \Carbon\Carbon::parse($orden['creation_datetime'])->format('H:i') : 'Hora no registrada' }} · {{ $orden['user_id'] ?: 'Sin usuario' }}</span></td>
                                    <td data-label="Consignatario"><span class="rental-consignee" title="{{ $orden['consignatario'] }}">{{ $orden['consignatario'] ?: 'No registrado' }}</span><span class="cell-secondary">RUC {{ $orden['consignatario_ruc'] ?: 'no registrado' }}</span></td>
                                    <td data-label="Área ocupada"><strong>{{ $orden['number_m2'] !== '' ? $orden['number_m2'] : '—' }} m²</strong></td>
                                    <td data-label="Bultos"><strong>{{ $orden['number_bundles'] !== '' ? $orden['number_bundles'] : '—' }}</strong></td>
                                    <td data-label="Sucursal"><span class="cell-clip" title="{{ $orden['branch_id'] }}">{{ $orden['branch_id'] ?: 'No registrada' }}</span></td>
                                    <td data-label="Estado"><span class="badge {{ $stateClass }}">{{ $stateLabel }}</span></td>
                                    <td data-label="Acciones"><div class="row-actions">
                                        @if(count($orden['account_move_ids'] ?? []) > 0)
                                            <button class="btn btn-quiet btn-sm action-icon" type="button" wire:click="VerModalFacturas('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="VerModalFacturas('{{ $orden['name'] }}')" title="Ver facturas" aria-label="Ver facturas de {{ $orden['name'] }}"><x-sos-icon name="receipt" class="icon-sm" /></button>
                                        @endif
                                        <button class="btn btn-secondary btn-sm" type="button" wire:click="verDetalleOrdenAlquiler('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="verDetalleOrdenAlquiler('{{ $orden['name'] }}')"><x-sos-icon name="eye" class="icon-sm" /><span>Ver detalle</span></button>
                                    </div></td>
                                </tr>
                            @empty
                                <tr class="empty-row"><td colspan="8"><div class="empty-state"><span class="empty-icon"><x-sos-icon name="{{ $error ? 'alert' : 'search' }}" /></span><h3>{{ $error ? 'No se pudieron cargar las órdenes' : 'No encontramos órdenes' }}</h3><p>{{ $error ? 'El servicio no respondió a tiempo. Puedes intentarlo nuevamente.' : 'Prueba con otros criterios o limpia los filtros.' }}</p><button class="btn btn-secondary btn-sm" type="button" wire:click="{{ $error ? 'obtenerOrdenesAlquiler' : 'resetFiltros' }}" wire:loading.attr="disabled">{{ $error ? 'Reintentar' : 'Limpiar filtros' }}</button></div></td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('livewire.clientes.partials.orders-pagination')
            </div>
        </div>
    @endif

    @if($showModalDetalleOrdenAlquiler)
        <livewire:clientes.modalordenalquiler :ordenSeleccionada_="$ordenSeleccionada" />
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
