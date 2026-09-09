<div class="sos-modal" role="dialog" aria-modal="true" aria-labelledby="invoice-modal-title" wire:click.self="{{ $closeAction }}">
    <section class="sos-modal-panel sos-modal-panel-sm">
        <header class="sos-modal-header">
            <span class="sos-modal-icon"><x-sos-icon name="receipt" /></span>
            <div>
                <h2 id="invoice-modal-title">{{ $modalTitle }}</h2>
                <p>Orden {{ $selectedOrderName ?: 'seleccionada' }}</p>
            </div>
            <button class="icon-button sos-modal-close" type="button" wire:click="{{ $closeAction }}" aria-label="Cerrar">
                <x-sos-icon name="close" />
            </button>
        </header>

        <div class="sos-modal-body">
            <div class="related-list">
                @forelse($selectedInvoices as $index => $factura)
                    <article class="related-item">
                        <span class="related-item-icon"><x-sos-icon name="document" /></span>
                        <div>
                            <strong>{{ $factura['name'] ?? 'Comprobante sin número' }}</strong>
                            <span>
                                @if(!empty($factura['invoice_date']))
                                    {{ \Carbon\Carbon::parse($factura['invoice_date'])->format('d/m/Y') }}
                                @else
                                    Fecha no registrada
                                @endif
                            </span>
                        </div>
                        <span class="related-index">{{ str_pad($index + 1, 2, '0', STR_PAD_LEFT) }}</span>
                    </article>
                @empty
                    <div class="empty-state compact">
                        <span class="empty-icon"><x-sos-icon name="document" /></span>
                        <h3>Sin comprobantes relacionados</h3>
                        <p>Esta orden todavía no tiene documentos asociados.</p>
                    </div>
                @endforelse
            </div>
        </div>

        <footer class="sos-modal-footer">
            <button class="btn btn-secondary" type="button" wire:click="{{ $closeAction }}">Cerrar</button>
        </footer>
    </section>
</div>
