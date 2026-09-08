<div>
    @if($error)
        <div class="auth-status error profile-notice" role="alert">
            <x-sos-icon name="alert" class="icon-sm" />
            <span>No pudimos obtener los comprobantes. Inténtalo nuevamente.</span>
        </div>
    @endif

    @if(!$carga_ejecutada)
        <div class="card empty-state" wire:loading.remove>
            <span class="empty-icon"><x-sos-icon name="receipt" /></span>
            <h3>Cargando comprobantes</h3>
            <p>Estamos consultando los documentos de tu empresa.</p>
        </div>
    @else
        <div wire:loading wire:target="obtenerComprobantes" class="card empty-state" aria-live="polite">
            <span class="empty-icon"><x-sos-icon name="receipt" /></span>
            <h3>Actualizando comprobantes</h3>
            <p>Esto puede tomar unos segundos.</p>
        </div>

        <div wire:loading.remove wire:target="obtenerComprobantes">
            <div class="page-actions list-toolbar">
                <button class="btn btn-secondary btn-sm" type="button" wire:click="obtenerComprobantes">
                    Actualizar listado
                </button>
            </div>

            <section class="card filters-card">
                <div class="filters-grid">
                    <div class="field">
                        <label for="invoice-search">Buscar comprobante</label>
                        <input class="control" id="invoice-search" type="search" wire:model.live.debounce.350ms="filtroNumero" placeholder="Número de comprobante">
                    </div>
                    <div class="field">
                        <label for="invoice-date">Fecha de emisión</label>
                        <input class="control" id="invoice-date" type="date" wire:model.live="filtroFecha">
                    </div>
                    <div class="field">
                        <label for="invoice-service">Servicio</label>
                        <select class="select-control" id="invoice-service" wire:model.live="filtroTipoServicio">
                            <option value="">Todos</option>
                            <option value="ALMACENAMIENTO">Almacenamiento</option>
                            <option value="COMPLEMENTARIOS">Complementarios</option>
                        </select>
                    </div>
                    <div class="field">
                        <label for="invoice-payment">Estado de pago</label>
                        <select class="select-control" id="invoice-payment" wire:model.live="filtroEstadoPago">
                            <option value="">Todos</option>
                            <option value="paid">Pagado</option>
                            <option value="partial">Parcial</option>
                            <option value="not_paid">Pendiente</option>
                        </select>
                    </div>
                    <div class="filter-actions">
                        <button class="btn btn-secondary" type="button" wire:click="resetFiltros">Limpiar</button>
                    </div>
                </div>
            </section>

            <section class="card list-card" aria-label="Listado de comprobantes">
                @if(count($comprobantesFiltrados))
                    <div class="table-wrap">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Servicio</th>
                                    <th>
                                        <button class="sort-button" type="button" wire:click="ordenar('name')">
                                            Comprobante @if($ordenarPor === 'name') {{ $ordenAscendente ? '↑' : '↓' }} @endif
                                        </button>
                                    </th>
                                    <th>
                                        <button class="sort-button" type="button" wire:click="ordenar('invoice_date')">
                                            Emisión @if($ordenarPor === 'invoice_date') {{ $ordenAscendente ? '↑' : '↓' }} @endif
                                        </button>
                                    </th>
                                    <th>Periodo</th>
                                    <th>Estado de pago</th>
                                    <th>Vencimiento</th>
                                    <th>
                                        <button class="sort-button" type="button" wire:click="ordenar('amount_total')">
                                            Total @if($ordenarPor === 'amount_total') {{ $ordenAscendente ? '↑' : '↓' }} @endif
                                        </button>
                                    </th>
                                    <th>Saldo</th>
                                    <th><span class="sr-only">Acciones</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comprobantesFiltrados as $comprobante)
                                    @php
                                        $paymentCode = $comprobante['payment_state_'] ?? ($comprobante['payment_state'][0] ?? 'not_paid');
                                        $paymentLabel = ['paid' => 'Pagado', 'partial' => 'Parcial', 'not_paid' => 'Pendiente'][$paymentCode] ?? 'Pendiente';
                                        $paymentClass = ['paid' => 'success', 'partial' => 'warning', 'not_paid' => 'danger'][$paymentCode] ?? 'warning';
                                        $service = ['ALMACENAMIENTO' => 'Almacenamiento', 'COMPLEMENTARIOS' => 'Servicio complementario'][$comprobante['service_type'] ?? ''] ?? ($comprobante['service_type'] ?: 'No registrado');
                                        $periodStart = $comprobante['date_start'] ? \Carbon\Carbon::parse($comprobante['date_start'])->format('d/m/Y') : null;
                                        $periodEnd = $comprobante['date_outlet'] ? \Carbon\Carbon::parse($comprobante['date_outlet'])->format('d/m/Y') : null;
                                    @endphp
                                    <tr>
                                        <td data-label="Servicio">{{ $service }}</td>
                                        <td data-label="Comprobante"><span class="cell-primary">{{ $comprobante['name'] ?: 'Sin número' }}</span><span class="cell-secondary">{{ $comprobante['dam'] ?: 'Sin DAM asociada' }}</span></td>
                                        <td data-label="Emisión">{{ $comprobante['invoice_date'] ? \Carbon\Carbon::parse($comprobante['invoice_date'])->format('d/m/Y') : 'No registrada' }}</td>
                                        <td data-label="Periodo">{{ $periodStart ?: 'No registrado' }} @if($periodEnd) <span class="cell-secondary">hasta {{ $periodEnd }}</span> @endif</td>
                                        <td data-label="Estado de pago"><span class="badge {{ $paymentClass }}">{{ $paymentLabel }}</span></td>
                                        <td data-label="Vencimiento">{{ $comprobante['vencimiento'] !== '' ? $comprobante['vencimiento'] : 'No registrado' }}</td>
                                        <td data-label="Total">S/ {{ number_format($comprobante['amount_total'] ?? 0, 2) }}</td>
                                        <td data-label="Saldo">S/ {{ number_format($comprobante['amount_residual'] ?? 0, 2) }}</td>
                                        <td data-label="Acciones">
                                            <div class="row-actions">
                                                @if($comprobante['ruta_pdf'])
                                                    <a class="btn btn-secondary btn-sm" href="{{ $comprobante['ruta_pdf'] }}" target="_blank" rel="noopener noreferrer" aria-label="Abrir PDF de {{ $comprobante['name'] }}">PDF</a>
                                                @endif
                                                @if($comprobante['ruta_xml'])
                                                    <a class="btn btn-quiet btn-sm" href="{{ $comprobante['ruta_xml'] }}" download>XML</a>
                                                @endif
                                                @if($comprobante['ruta_cdr'])
                                                    <a class="btn btn-quiet btn-sm" href="{{ $comprobante['ruta_cdr'] }}" download>CDR</a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @php $lastPage = max(1, (int) ceil($total / $porPagina)); @endphp
                    <footer class="pagination">
                        <span class="pagination-summary">Página {{ $page }} de {{ $lastPage }} · {{ $total }} comprobantes</span>
                        <label class="page-size">Filas
                            <select wire:model.live="porPagina" aria-label="Filas por página">
                                <option value="10">10</option><option value="25">25</option><option value="50">50</option>
                            </select>
                        </label>
                        <div class="pagination-buttons">
                            <button class="btn btn-secondary btn-sm" type="button" wire:click="setPage({{ max(1, $page - 1) }})" @disabled($page <= 1)>Anterior</button>
                            <button class="btn btn-secondary btn-sm" type="button" wire:click="setPage({{ $page + 1 }})" @disabled(!$hasMore)>Siguiente</button>
                        </div>
                    </footer>
                @else
                    <div class="empty-state">
                        <span class="empty-icon"><x-sos-icon name="receipt" /></span>
                        <h3>No encontramos comprobantes</h3>
                        <p>Prueba con otros términos o limpia los filtros.</p>
                        <button class="btn btn-secondary" type="button" wire:click="resetFiltros">Limpiar filtros</button>
                    </div>
                @endif
            </section>
        </div>
    @endif
</div>
