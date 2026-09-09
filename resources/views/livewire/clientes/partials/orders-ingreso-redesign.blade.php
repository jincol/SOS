<section class="orders-screen" aria-label="Listado de órdenes de ingreso">
    <div class="orders-progress" wire:loading aria-hidden="true"><span></span></div>
    @if($error)
        <div class="notice notice-danger" role="alert">
            <x-sos-icon name="alert" />
            <div><strong>No pudimos cargar las órdenes</strong><span>{{ $error }}</span></div>
        </div>
    @endif

    @if(count($ordenesFiltradas) === 0 && $carga_ejecutada === false)
        @include('livewire.clientes.partials.orders-loading')
    @else
        <div wire:loading wire:target="obtenerOrdenesIngreso" class="w-full">
            @include('livewire.clientes.partials.orders-loading')
        </div>

        <div wire:loading.remove wire:target="obtenerOrdenesIngreso">
            <div class="orders-toolbar">
                <div class="orders-result-summary">
                    <span class="orders-count">{{ $total }}</span>
                    <div><strong>{{ $total === 1 ? 'orden encontrada' : 'órdenes encontradas' }}</strong><span>Información actualizada desde el sistema operativo</span></div>
                </div>
                <button class="btn btn-secondary btn-sm" type="button" wire:click="obtenerOrdenesIngreso">
                    <x-sos-icon name="refresh" class="icon-sm" />
                    <span>Actualizar</span>
                </button>
            </div>

            <div class="card filters-card orders-filters" x-data="{ advanced: {{ ($filtroAlmacenero || $filtroCompany) ? 'true' : 'false' }} }">
                <div class="orders-filters-main">
                    <div class="field orders-search-field">
                        <label for="ingreso-orden">Buscar orden</label>
                        <div class="search-control">
                            <x-sos-icon name="search" class="icon-sm" />
                            <input id="ingreso-orden" class="control" type="search" wire:model.live.debounce.350ms="filtroOrden" placeholder="Ej. OI25/05/00011" autocomplete="off">
                        </div>
                    </div>
                    <div class="field">
                        <label for="ingreso-fecha">Fecha de ingreso</label>
                        <input id="ingreso-fecha" class="control" type="date" wire:model.live="filtroFecha">
                    </div>
                    <div class="field">
                        <label for="ingreso-tipo">Tipo de ingreso</label>
                        <select id="ingreso-tipo" class="select-control" wire:model.live="filtroTipoIngreso">
                            <option value="">Todos los tipos</option>
                            <option value="Simple">Simple</option>
                            <option value="Aduanero">Aduanero</option>
                        </select>
                    </div>
                    <div class="orders-filter-actions">
                        <button class="btn btn-quiet btn-sm" type="button" @click="advanced = !advanced" :aria-expanded="advanced">
                            <x-sos-icon name="filter" class="icon-sm" />
                            <span x-text="advanced ? 'Menos filtros' : 'Más filtros'"></span>
                        </button>
                        <button class="btn btn-secondary btn-sm" type="button" wire:click="resetFiltros">Limpiar</button>
                    </div>
                </div>
                <div class="orders-filters-advanced" x-cloak x-show="advanced" x-collapse>
                    <div class="field">
                        <label for="ingreso-almacenero">Responsable de almacén</label>
                        <input id="ingreso-almacenero" class="control" type="search" wire:model.live.debounce.350ms="filtroAlmacenero" placeholder="Nombre del responsable">
                    </div>
                    <div class="field">
                        <label for="ingreso-company">Compañía operadora</label>
                        <input id="ingreso-company" class="control" type="search" wire:model.live.debounce.350ms="filtroCompany" placeholder="Nombre de la compañía">
                    </div>
                </div>
            </div>

            <div class="card list-card orders-list-card">
                <div class="table-wrap">
                    <table class="data-table orders-table">
                        <thead>
                            <tr>
                                <th><button class="sort-button" type="button" wire:click="ordenar('name')">Orden @if($ordenarPor === 'name') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                                <th><button class="sort-button" type="button" wire:click="ordenar('fec_ingreso')">Fecha @if($ordenarPor === 'fec_ingreso') <span>{{ $ordenAscendente ? '↑' : '↓' }}</span> @endif</button></th>
                                <th>Tipo</th>
                                <th>Responsable</th>
                                <th>Sede</th>
                                <th class="align-right">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($ordenesFiltradas as $orden)
                                <tr wire:key="ingreso-{{ $orden['name'] }}">
                                    <td data-label="Orden">
                                        <span class="cell-primary order-code">{{ $orden['name'] ?: 'Sin código' }}</span>
                                        <span class="cell-secondary cell-clip" title="{{ $orden['company'] }}">{{ $orden['company'] ?: 'Compañía no registrada' }}</span>
                                    </td>
                                    <td data-label="Fecha">
                                        <span class="cell-primary">{{ !empty($orden['fec_ingreso']) ? \Carbon\Carbon::parse($orden['fec_ingreso'])->format('d/m/Y') : 'No registrada' }}</span>
                                        <span class="cell-secondary">Creada {{ !empty($orden['creation_datetime']) ? \Carbon\Carbon::parse($orden['creation_datetime'])->format('d/m/Y · H:i') : 'sin fecha' }}</span>
                                    </td>
                                    <td data-label="Tipo"><span class="badge neutral">{{ $orden['tipo_ingreso'] ?: 'No registrado' }}</span></td>
                                    <td data-label="Responsable"><span class="cell-clip" title="{{ $orden['almacenero'] }}">{{ $orden['almacenero'] ?: 'No asignado' }}</span></td>
                                    <td data-label="Sede"><span class="cell-clip" title="{{ $orden['sucursal'] }}">{{ $orden['sucursal'] ?: 'No registrada' }}</span></td>
                                    <td data-label="Acciones">
                                        <div class="row-actions">
                                            <button class="btn btn-quiet btn-sm action-icon" type="button" wire:click="VerModalHistorialAlmacenOI('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="VerModalHistorialAlmacenOI('{{ $orden['name'] }}')" title="Ver salidas relacionadas" aria-label="Ver salidas relacionadas con {{ $orden['name'] }}">
                                                <x-sos-icon name="outbox" class="icon-sm" />
                                            </button>
                                            <button class="btn btn-quiet btn-sm action-icon" type="button" wire:click="VerComprobantesVinculados('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="VerComprobantesVinculados('{{ $orden['name'] }}')" title="Ver comprobantes relacionados" aria-label="Ver comprobantes relacionados con {{ $orden['name'] }}">
                                                <x-sos-icon name="receipt" class="icon-sm" />
                                            </button>
                                            <button class="btn btn-secondary btn-sm" type="button" wire:click="verDetalleOrdenIngreso('{{ $orden['name'] }}')" wire:loading.attr="disabled" wire:loading.class="is-loading" wire:target="verDetalleOrdenIngreso('{{ $orden['name'] }}')">
                                                <x-sos-icon name="eye" class="icon-sm" />
                                                <span>Ver detalle</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr class="empty-row">
                                    <td colspan="6">
                                        <div class="empty-state">
                                            <span class="empty-icon"><x-sos-icon name="search" /></span>
                                            <h3>No encontramos órdenes</h3>
                                            <p>Prueba con otra fecha o limpia los filtros para ver todos los resultados.</p>
                                            <button class="btn btn-secondary btn-sm" type="button" wire:click="resetFiltros">Limpiar filtros</button>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @include('livewire.clientes.partials.orders-pagination')
            </div>
        </div>
    @endif

    @if($showModalDetalleOrdenIngreso)
        <livewire:clientes.modalordeningreso :ordenSeleccionada_="$ordenSeleccionada" />
    @endif

    @if($verHistorialOI)
        @php
            $history = collect($ordenSeleccionada['historial_almacen'] ?? []);
            $outputs = $history->filter(fn ($item) => !str_starts_with((string) ($item['code_warehouse'] ?? ''), 'OI'));
        @endphp
        <div class="sos-modal" role="dialog" aria-modal="true" aria-labelledby="history-modal-title" wire:click.self="CerrarModalHistorialAlmacenOI">
            <section class="sos-modal-panel">
                <header class="sos-modal-header">
                    <span class="sos-modal-icon"><x-sos-icon name="outbox" /></span>
                    <div><h2 id="history-modal-title">Movimientos relacionados</h2><p>Orden {{ $ordenSeleccionada['name'] ?? '' }}</p></div>
                    <button class="icon-button sos-modal-close" type="button" wire:click="CerrarModalHistorialAlmacenOI" aria-label="Cerrar"><x-sos-icon name="close" /></button>
                </header>
                <div class="sos-modal-body">
                    <div class="related-summary">
                        <div><span>Salidas</span><strong>{{ $outputs->count() }}</strong></div>
                        <div><span>Bultos retirados</span><strong>{{ abs($outputs->sum(fn ($item) => (float) ($item['number_bundles'] ?? 0))) }}</strong></div>
                        <div><span>Pallets retirados</span><strong>{{ abs($outputs->sum(fn ($item) => (float) ($item['number_pallets'] ?? 0))) }}</strong></div>
                    </div>
                    <div class="table-wrap compact-table-wrap">
                        <table class="data-table related-table">
                            <thead><tr><th>Movimiento</th><th>Fecha</th><th>Bultos</th><th>Pallets</th><th>Contenedores</th></tr></thead>
                            <tbody>
                                @forelse($history as $item)
                                    <tr>
                                        <td data-label="Movimiento"><span class="cell-primary order-code">{{ $item['code_warehouse'] ?? 'Sin código' }}</span></td>
                                        <td data-label="Fecha">{{ $item['date'] ?? 'No registrada' }}</td>
                                        <td data-label="Bultos">{{ $item['number_bundles'] ?? 0 }}</td>
                                        <td data-label="Pallets">{{ $item['number_pallets'] ?? 0 }}</td>
                                        <td data-label="Contenedores">{{ $item['number_containers'] ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5"><div class="empty-state compact"><h3>Sin movimientos relacionados</h3><p>No hay salidas registradas para esta orden.</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <footer class="sos-modal-footer"><button class="btn btn-secondary" type="button" wire:click="CerrarModalHistorialAlmacenOI">Cerrar</button></footer>
            </section>
        </div>
    @endif

    @if($ModalComprobantesViculados)
        @php
            $relatedReceipts = collect($comprobantes)->filter(fn ($item) => ($item['order_income'] ?? null) === $nameOI_comprobantes);
        @endphp
        <div class="sos-modal" role="dialog" aria-modal="true" aria-labelledby="receipts-modal-title" wire:click.self="CerrarModalComprobantesVinculados">
            <section class="sos-modal-panel">
                <header class="sos-modal-header">
                    <span class="sos-modal-icon"><x-sos-icon name="receipt" /></span>
                    <div><h2 id="receipts-modal-title">Comprobantes relacionados</h2><p>Orden {{ $nameOI_comprobantes }}</p></div>
                    <button class="icon-button sos-modal-close" type="button" wire:click="CerrarModalComprobantesVinculados" aria-label="Cerrar"><x-sos-icon name="close" /></button>
                </header>
                <div class="sos-modal-body">
                    <div class="table-wrap compact-table-wrap">
                        <table class="data-table related-table">
                            <thead><tr><th>Comprobante</th><th>Emisión</th><th>Estado</th><th class="align-right">Total</th></tr></thead>
                            <tbody>
                                @forelse($relatedReceipts as $comprobante)
                                    @php
                                        $paymentStates = (array) ($comprobante['payment_state'] ?? []);
                                        $isPaid = in_array('paid', $paymentStates, true);
                                        $isOverdue = !$isPaid && !empty($comprobante['date_outlet']) && \Carbon\Carbon::parse($comprobante['date_outlet'])->isPast();
                                    @endphp
                                    <tr>
                                        <td data-label="Comprobante"><span class="cell-primary order-code">{{ $comprobante['name'] ?? 'Sin número' }}</span></td>
                                        <td data-label="Emisión">{{ !empty($comprobante['invoice_date']) ? \Carbon\Carbon::parse($comprobante['invoice_date'])->format('d/m/Y') : 'No registrada' }}</td>
                                        <td data-label="Estado"><span class="badge {{ $isPaid ? 'success' : ($isOverdue ? 'danger' : 'warning') }}">{{ $isPaid ? 'Pagado' : ($isOverdue ? 'Vencido' : 'Pendiente') }}</span></td>
                                        <td data-label="Total" class="align-right"><strong>S/ {{ number_format((float) ($comprobante['amount_total'] ?? 0), 2) }}</strong></td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4"><div class="empty-state compact"><h3>Sin comprobantes relacionados</h3><p>Esta orden todavía no tiene documentos asociados.</p></div></td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <footer class="sos-modal-footer"><button class="btn btn-secondary" type="button" wire:click="CerrarModalComprobantesVinculados">Cerrar</button></footer>
            </section>
        </div>
    @endif
</section>
