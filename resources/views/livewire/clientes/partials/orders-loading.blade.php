<div class="orders-loading" role="status" aria-live="polite">
    <span class="sr-only">Cargando órdenes</span>
    <div class="orders-loading-bar skeleton"></div>
    <div class="card orders-loading-card">
        <div class="orders-loading-filters">
            <span class="skeleton"></span>
            <span class="skeleton"></span>
            <span class="skeleton"></span>
        </div>
        @for($i = 0; $i < 5; $i++)
            <div class="orders-loading-row">
                <span class="skeleton"></span>
                <span class="skeleton"></span>
                <span class="skeleton"></span>
                <span class="skeleton"></span>
            </div>
        @endfor
    </div>
</div>
