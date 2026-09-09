<section class="orders-screen" aria-label="Listado de órdenes de salida">
    <div class="orders-progress" wire:loading aria-hidden="true"><span></span></div>
    @if($error)
        <div class="notice notice-danger" role="alert"><x-sos-icon name="alert" /><div><strong>No pudimos cargar las órdenes</strong><span>{{ $error }}</span></div></div>
    @endif

    @if(count($ordenesFiltradas) === 0 && $carga_ejecutada === false)
        @include('livewire.clientes.partials.orders-loading')
    @else
        <div wire:loading wire:target="obtenerOrdenesSalida" class="w-full">@include('livewire.clientes.partials.orders-loading')</div>
        <div wire:loading.remove wire:target="obtenerOrdenesSalida">
            <div class="orders-toolbar">
                <div class="orders-result-summary"><span class="orders-count">{{ $total }}</span><div><strong>{{ $total === 1 ? 'orden encontrada' : 'órdenes encontradas' }}</strong><span>Despachos y mercancía vinculada</span></div></div>
                <button class="btn btn-secondary btn-sm" type="button" wire:click="obtenerOrdenesSalida"><x-sos-icon name="refresh" class="icon-sm" /><span>Actualizar</span></button>
            </div>

            <div class="card filters-card orders-filters" x-data="{ advanced: {{ ($filtroSucursal || $filtroCompany || $filtroUsuario) ? 'true' : 'false' }} }">
                <div class="orders-filters-main">
                    <div class="field orders-search-field">
                        <label for="salida-orden">Buscar orden de salida</label>
                        <div class="search-control"><x-sos-icon name="search" class="icon-sm" /><input id="salida-orden" class="control" type="search" wire:model.live.debounce.350ms="filtroOrdenSalida" placeholder="Ej. OS25/06/00137" autocomplete="off"></div>
                    </div>
                    <div class="field"><label for="salida-fecha">Fecha de salida</label><input id="salida-fecha" class="control" type="date" wire:model.live="filtroFecha"></div>
                    <div class="field"><label for="salida-ingreso">Orden de ingreso</label><input id="salida-ingreso" class="control" type="search" wire:model.live.debounce.350ms="filtroIngreso" placeholder="Código relacionado"></div>
                    <div class="orders-filter-actions">
                        <button class="btn btn-quiet btn-sm" type="button" @click="advanced = !advanced" :aria-expanded="advanced"><x-sos-icon name="filter" class="icon-sm" /><span x-text="advanced ? 'Menos filtros' : 'Más filtros'"></span></button>
                        <button class="btn btn-secondary btn-sm" type="button" wire:click="resetFiltros">Limpiar</button>
                    </div>
                </div>
                <div class="orders-filters-advanced" x-cloak x-show="advanced" x-collapse>
                    <div class="field"><label for="salida-sucursal">Sucursal</label><input id="salida-sucursal" class="control" type="search" wire:model.live.debounce.350ms="filtroSucursal" placeholder="Nombre de la sucursal"></div>
                    <div class="field"><label for="salida-company">Compañía operadora</label><input id="salida-company" class="control" type="search" wire:model.live.debounce.350ms="filtroCompany" placeholder="Nombre de la compañía"></div>
                    <div class="field"><label for="salida-usuario">Registrado por</label><input id="salida-usuario" class="control" type="search" wire:model.live.debounce.350ms="filtroUsuario" placeholder="Nombre del usuario"></div>
                </div>
            </div>

            <div class="card list-card orders-list-card">
                <div class="table-wrap">
                    <table class="data-table orders-table">
                        <thead><tr>
                            <th><button class="sort-button" type="button" wire:click="ordenar('name')">Orden @if($ordenarPor === 'name') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                            <th><button class="sort-button" type="button" wire:click="ordenar('warehouse_system')">Ingreso relacionado @if($ordenarPor === 'warehouse_system') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                            <th><button class="sort-button" type="button" wire:click="ordenar('date')">Fecha @if($ordenarPor === 'date') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                            <th>Registrado por</th><th>Sucursal</th><th>Bultos</th><th>Estado</th><th class="align-right">Acción</th>
                        </tr></thead>
                        <tbody>
                            @forelse($ordenesFiltradas as $orden)
                                @php
                                    $rawState = strtolower((string) ($orden['state'] ?? ''));
                                    $stateClass = in_array($rawState, ['done', 'finished', 'completed', 'finalizado', 'cerrado'], true) ? 'success' : (in_array($rawState, ['cancel', 'cancelled', 'cancelado'], true) ? 'danger' : 'warning');
                                    $stateLabel = match($rawState) { 'done', 'finished', 'completed', 'finalizado', 'cerrado' => 'Finalizada', 'cancel', 'cancelled', 'cancelado' => 'Cancelada', 'draft', 'borrador' => 'Borrador', default => ($orden['state'] ?: 'Sin estado') };
                                @endphp
                                <tr wire:key="salida-{{ $orden['name'] }}">
                                    <td data-label="Orden"><span class="cell-primary order-code">{{ $orden['name'] ?: 'Sin código' }}</span><span class="cell-secondary cell-clip">{{ $orden['company'] ?: 'Compañía no registrada' }}</span></td>
                                    <td data-label="Ingreso relacionado"><span class="cell-primary">{{ $orden['warehouse_system'] ?: 'No relacionado' }}</span></td>
                                    <td data-label="Fecha"><span class="cell-primary">{{ !empty($orden['date']) ? \Carbon\Carbon::parse($orden['date'])->format('d/m/Y') : 'No registrada' }}</span></td>
                                    <td data-label="Registrado por"><span class="cell-clip" title="{{ $orden['user'] }}">{{ $orden['user'] ?: 'No registrado' }}</span></td>
                                    <td data-label="Sucursal"><span class="cell-clip" title="{{ $orden['branch'] }}">{{ $orden['branch'] ?: 'No registrada' }}</span></td>
                                    <td data-label="Bultos"><strong>{{ $orden['number_bundles_simple'] !== '' ? $orden['number_bundles_simple'] : '—' }}</strong></td>
                                    <td data-label="Estado"><span class="badge {{ $stateClass }}">{{ $stateLabel }}</span></td>
                                    <td data-label="Acción"><div class="row-actions"><button class="btn btn-secondary btn-sm" type="button" wire:click="verDetalleOrdenSalida('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="verDetalleOrdenSalida('{{ $orden['name'] }}')"><x-sos-icon name="eye" class="icon-sm" /><span>Ver detalle</span></button></div></td>
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

    @if($showModalDetalleOrdenSalida)
        <livewire:clientes.modalordensalida :ordenSeleccionada_="$ordenSeleccionada" />
    @endif
</section>
