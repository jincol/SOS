<div>
    <div wire:loading wire:target="cargarEstadisticas" aria-live="polite">
        <div class="kpi-grid" aria-label="Cargando indicadores">
            @for($index = 0; $index < 4; $index++)
                <div class="kpi-card">
                    <div class="skeleton skeleton-label"></div>
                    <div class="skeleton skeleton-value"></div>
                </div>
            @endfor
        </div>
    </div>

    <div wire:loading.remove wire:target="cargarEstadisticas">
        @if($estadisticas)
            @php
                $payments = $estadisticas['por_estado'] ?? [];
                $monthly = collect($estadisticas['comprobantes_por_mes'] ?? [])->sortKeys()->take(-5);
                $monthlyMax = max(1, (int) $monthly->max());
                $total = max(1, (int) ($estadisticas['total_comprobantes'] ?? 0));
            @endphp

            <section class="kpi-grid" aria-label="Indicadores principales">
                <article class="kpi-card">
                    <div class="kpi-top">
                        <div><span class="kpi-label">Comprobantes</span><strong class="kpi-value">{{ number_format($estadisticas['total_comprobantes'] ?? 0) }}</strong></div>
                        <span class="kpi-icon"><x-sos-icon name="receipt" /></span>
                    </div>
                    <span class="kpi-meta">Documentos emitidos</span>
                </article>

                <article class="kpi-card">
                    <div class="kpi-top">
                        <div><span class="kpi-label">Monto total</span><strong class="kpi-value">S/ {{ number_format($estadisticas['total_monto'] ?? 0, 2) }}</strong></div>
                        <span class="kpi-icon"><x-sos-icon name="money" /></span>
                    </div>
                    <span class="kpi-meta">Facturación acumulada</span>
                </article>

                <article class="kpi-card">
                    <div class="kpi-top">
                        <div><span class="kpi-label">Saldo pendiente</span><strong class="kpi-value">S/ {{ number_format($estadisticas['monto_pendiente'] ?? 0, 2) }}</strong></div>
                        <span class="kpi-icon warning"><x-sos-icon name="clock" /></span>
                    </div>
                    <span class="kpi-meta">Incluye pagos parciales</span>
                </article>

                <article class="kpi-card">
                    <div class="kpi-top">
                        <div><span class="kpi-label">Comprobantes pagados</span><strong class="kpi-value">{{ number_format($payments['pagados'] ?? 0) }}</strong></div>
                        <span class="kpi-icon success"><x-sos-icon name="check" /></span>
                    </div>
                    <span class="kpi-meta positive">{{ round((($payments['pagados'] ?? 0) / $total) * 100) }}% del total</span>
                </article>
            </section>

            <section class="dashboard-grid">
                <article class="card">
                    <header class="card-header">
                        <div><h2>Comprobantes por mes</h2><p>Orden cronológico · últimos cinco meses disponibles</p></div>
                        <span class="chart-legend">Cantidad emitida</span>
                    </header>
                    <div class="card-body">
                        @if($monthly->isNotEmpty())
                            <div class="chart-area" role="img" aria-label="Gráfico de comprobantes emitidos por mes">
                                @foreach($monthly as $month => $count)
                                    <div class="chart-column">
                                        <strong class="chart-value">{{ $count }}</strong>
                                        <div class="chart-track">
                                            <span class="chart-bar" style="height: {{ max(10, round(($count / $monthlyMax) * 100)) }}%"></span>
                                        </div>
                                        <span class="chart-label">{{ \Carbon\Carbon::createFromFormat('Y-m', $month)->locale('es')->translatedFormat('M Y') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="empty-state"><h3>Sin información mensual</h3><p>Los datos aparecerán cuando existan comprobantes emitidos.</p></div>
                        @endif
                    </div>
                </article>

                <article class="card">
                    <header class="card-header"><div><h2>Estado de pagos</h2><p>Distribución de los comprobantes</p></div></header>
                    <div class="card-body payment-summary">
                        <div><span class="badge success">Pagados</span><strong>{{ number_format($payments['pagados'] ?? 0) }}</strong></div>
                        <div><span class="badge warning">Parciales</span><strong>{{ number_format($payments['parciales'] ?? 0) }}</strong></div>
                        <div><span class="badge danger">Pendientes</span><strong>{{ number_format($payments['pendientes'] ?? 0) }}</strong></div>
                    </div>
                </article>
            </section>
        @else
            <div class="card empty-state">
                <span class="empty-icon"><x-sos-icon name="receipt" /></span>
                <h3>No hay datos disponibles</h3>
                <p>No pudimos obtener indicadores para esta empresa.</p>
                <button class="btn btn-secondary" type="button" wire:click="cargarEstadisticas">Intentar nuevamente</button>
            </div>
        @endif
    </div>
</div>
