@php
    $visibleCount = $ordenesFiltradas->count();
    $from = $visibleCount > 0 ? (($page - 1) * $porPagina) + 1 : 0;
    $to = $visibleCount > 0 ? min($page * $porPagina, $total) : 0;
@endphp

<div class="pagination" aria-label="Paginación de órdenes">
    <p class="pagination-summary">Mostrando <strong>{{ $from }}–{{ $to }}</strong> de <strong>{{ $total }}</strong></p>
    <label class="page-size">
        <span>Filas</span>
        <select wire:model.live="porPagina" aria-label="Filas por página">
            <option value="5">5</option>
            <option value="10">10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
    </label>
    <div class="pagination-buttons">
        <button class="btn btn-secondary btn-sm" type="button" wire:click="setPage({{ $page - 1 }})" @disabled($page <= 1)>
            <x-sos-icon name="chevron-left" class="icon-sm" />
            <span>Anterior</span>
        </button>
        <span class="page-current" aria-current="page">{{ $page }}</span>
        <button class="btn btn-secondary btn-sm" type="button" wire:click="setPage({{ $page + 1 }})" @disabled(!$hasMore)>
            <span>Siguiente</span>
            <x-sos-icon name="chevron-right" class="icon-sm" />
        </button>
    </div>
</div>
