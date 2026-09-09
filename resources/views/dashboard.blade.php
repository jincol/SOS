<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#b80d24">
    <meta name="description" content="Portal de clientes de Almacenes y Depósitos de Aduanas SOS">
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <title>Portal de clientes | Almacenes SOS</title>
    <script>
        const legacyPortalRoutes = {
            '#/dashboard': '/dashboard',
            '#/comprobantes': '/comprobantes',
            '#/ordenes/ingresos': '/ordenes/ingresos',
            '#/ordenes/salidas': '/ordenes/salidas',
            '#/ordenes/transportes': '/ordenes/transportes',
            '#/ordenes/servicios': '/ordenes/servicios',
            '#/ordenes/alquileres': '/ordenes/alquileres',
            '#/perfil': '/perfil'
        };
        const legacyDestination = legacyPortalRoutes[window.location.hash];
        if (legacyDestination && window.location.pathname !== legacyDestination) {
            window.location.replace(legacyDestination);
        }
    </script>
    @vite('resources/css/app.css')
    <link rel="stylesheet" href="{{ asset('css/sos-redesign.css') }}?v=1.1.3">
    @livewireStyles
</head>
@php
    $companyName = Session::get('user_data.razon_social') ?? 'Empresa cliente';
    $companyRuc = Session::get('user_data.ruc') ?? 'RUC no registrado';
    $initials = collect(preg_split('/\s+/', trim($companyName)))->filter()->take(2)->map(fn ($word) => mb_strtoupper(mb_substr($word, 0, 1)))->join('');
    $activeMenu = $activeMenu ?? 'dashboard';
    $pageMeta = [
        'dashboard' => ['Resumen de tu operación', 'Indicadores claros para revisar documentos y operaciones pendientes.', 'Resumen'],
        'comprobantes' => ['Comprobantes', 'Consulta periodos, vencimientos, saldos y documentos sin mezclar conceptos.', 'Facturación'],
        'ordenesingreso' => ['Órdenes de ingreso', 'Consulta el ingreso de mercancía, responsable, almacén y estado operativo.', 'Operaciones'],
        'ordenessalida' => ['Órdenes de salida', 'Controla despachos, órdenes relacionadas y destinos.', 'Operaciones'],
        'ordenestransporte' => ['Órdenes de transporte', 'Revisa transportista, ruta, unidad y situación de cada traslado.', 'Operaciones'],
        'ordenesservicio' => ['Órdenes de servicio', 'Consulta servicios operativos, conceptos y aprobaciones.', 'Operaciones'],
        'ordenesalquiler' => ['Órdenes de alquiler', 'Visualiza espacios asignados, área ocupada y mercancía relacionada.', 'Operaciones'],
        'perfil' => ['Perfil de empresa', 'Administra la información y seguridad de la cuenta.', 'Cuenta'],
    ];
    $currentPage = $pageMeta[$activeMenu] ?? $pageMeta['dashboard'];
@endphp
<body
    class="portal-body"
    x-data="{
        sidebarOpen: false,
        accountOpen: false,
        activeMenu: @js($activeMenu),
        openSidebar() {
            this.sidebarOpen = true;
            document.body.classList.add('nav-open');
        },
        closeSidebar() {
            this.sidebarOpen = false;
            document.body.classList.remove('nav-open');
        }
    }"
    @keydown.escape.window="closeSidebar(); accountOpen = false"
>
    <a class="skip-link" href="#main-content">Ir al contenido principal</a>

    <div class="app-shell">
        <aside class="sidebar" id="portal-sidebar" aria-label="Navegación principal">
            <button class="icon-button portal-mobile-close" type="button" @click="closeSidebar()" aria-label="Cerrar menú">
                <x-sos-icon name="close" />
            </button>

            <div class="sidebar-brand">
                <img src="{{ asset('imgcc/logos_empresas_edit.png') }}" alt="Almacenes y Depósitos de Aduanas SOS">
            </div>

            <nav class="sidebar-nav">
                <p class="nav-group-label">General</p>
                <a class="nav-link" :class="{ 'active': activeMenu === 'dashboard' }" :aria-current="activeMenu === 'dashboard' ? 'page' : null"
                   href="{{ route('dashboard') }}" @click="closeSidebar()">
                    <x-sos-icon name="dashboard" /><span class="nav-text">Resumen</span>
                </a>
                <a class="nav-link" :class="{ 'active': activeMenu === 'comprobantes' }" :aria-current="activeMenu === 'comprobantes' ? 'page' : null"
                   href="{{ route('clientes.comprobantes') }}" @click="closeSidebar()">
                    <x-sos-icon name="receipt" /><span class="nav-text">Comprobantes</span>
                </a>

                <p class="nav-group-label">Operaciones</p>
                <a class="nav-link" :class="{ 'active': activeMenu === 'ordenesingreso' }" :aria-current="activeMenu === 'ordenesingreso' ? 'page' : null"
                   href="{{ route('clientes.ordenes.ingresos') }}" @click="closeSidebar()">
                    <x-sos-icon name="inbox" /><span class="nav-text">Órdenes de ingreso</span>
                </a>
                <a class="nav-link" :class="{ 'active': activeMenu === 'ordenessalida' }" :aria-current="activeMenu === 'ordenessalida' ? 'page' : null"
                   href="{{ route('clientes.ordenes.salidas') }}" @click="closeSidebar()">
                    <x-sos-icon name="outbox" /><span class="nav-text">Órdenes de salida</span>
                </a>
                <a class="nav-link" :class="{ 'active': activeMenu === 'ordenestransporte' }" :aria-current="activeMenu === 'ordenestransporte' ? 'page' : null"
                   href="{{ route('clientes.ordenes.transportes') }}" @click="closeSidebar()">
                    <x-sos-icon name="truck" /><span class="nav-text">Órdenes de transporte</span>
                </a>
                <a class="nav-link" :class="{ 'active': activeMenu === 'ordenesservicio' }" :aria-current="activeMenu === 'ordenesservicio' ? 'page' : null"
                   href="{{ route('clientes.ordenes.servicios') }}" @click="closeSidebar()">
                    <x-sos-icon name="tools" /><span class="nav-text">Órdenes de servicio</span>
                </a>
                <a class="nav-link" :class="{ 'active': activeMenu === 'ordenesalquiler' }" :aria-current="activeMenu === 'ordenesalquiler' ? 'page' : null"
                   href="{{ route('clientes.ordenes.alquileres') }}" @click="closeSidebar()">
                    <x-sos-icon name="warehouse" /><span class="nav-text">Órdenes de alquiler</span>
                </a>
            </nav>

            <div class="sidebar-bottom">
                <form action="{{ route('salir') }}" method="GET">
                    @csrf
                    <button class="nav-link" type="submit">
                        <x-sos-icon name="logout" /><span class="nav-text">Cerrar sesión</span>
                    </button>
                </form>
            </div>
        </aside>

        <button class="mobile-overlay" type="button" @click="closeSidebar()" aria-label="Cerrar menú"></button>

        <div class="app-main portal-content">
            <header class="topbar">
                <button class="icon-button topbar-menu" type="button" @click="openSidebar()" aria-label="Abrir menú" aria-controls="portal-sidebar">
                    <x-sos-icon name="menu" />
                </button>
                <span class="company-mark"><x-sos-icon name="building" /></span>
                <div class="company-context">
                    <strong>{{ $companyName }}</strong>
                    <span>RUC {{ $companyRuc }}</span>
                </div>
                <div class="topbar-spacer"></div>

                <div class="account-menu" @click.outside="accountOpen = false">
                    <button class="account-trigger" type="button" @click="accountOpen = !accountOpen" :aria-expanded="accountOpen" aria-controls="account-popover">
                        <span class="avatar">{{ $initials ?: 'SOS' }}</span>
                        <span class="account-copy"><strong>{{ $companyName }}</strong><span>Perfil de empresa</span></span>
                        <x-sos-icon name="chevron-down" class="icon-sm" />
                    </button>
                    <div class="account-popover" id="account-popover" x-cloak x-show="accountOpen" x-transition>
                        <a href="{{ route('clientes.perfil-usuario') }}"><x-sos-icon name="user" class="icon-sm" /> Ver perfil</a>
                        <form action="{{ route('salir') }}" method="GET">
                            @csrf
                            <button type="submit"><x-sos-icon name="logout" class="icon-sm" /> Cerrar sesión</button>
                        </form>
                    </div>
                </div>
            </header>

            <main class="page" id="main-content">
                <nav class="breadcrumb" aria-label="Migas de pan">
                    <a href="{{ route('dashboard') }}">Inicio</a>
                    <span aria-hidden="true">›</span>
                    <span>{{ $currentPage[2] }}</span>
                </nav>
                <header class="page-header">
                    <div class="page-title-group">
                        <h1>{{ $currentPage[0] }}</h1>
                        <p>{{ $currentPage[1] }}</p>
                    </div>
                </header>

                <section class="portal-section portal-livewire">
                    @switch($activeMenu)
                        @case('comprobantes')
                            <livewire:clientes.comprobantes />
                            @break
                        @case('ordenesingreso')
                            <livewire:clientes.ordenes />
                            @break
                        @case('ordenessalida')
                            <livewire:clientes.ordenessalida />
                            @break
                        @case('ordenestransporte')
                            <livewire:clientes.ordenestransporte />
                            @break
                        @case('ordenesservicio')
                            <livewire:clientes.ordenesservicio />
                            @break
                        @case('ordenesalquiler')
                            <livewire:clientes.ordenesalquiler />
                            @break
                        @case('perfil')
                            <livewire:clientes.perfil-usuario />
                            @break
                        @default
                            <livewire:clientes.estadisticas />
                    @endswitch
                </section>
            </main>
        </div>
    </div>

    <a class="support-fab" href="https://api.whatsapp.com/send?phone=51960229005" target="_blank" rel="noopener noreferrer" aria-label="Contactar soporte SOS por WhatsApp">
        <x-sos-icon name="support" />
    </a>

    @livewireScripts
    <script>
        function enhancePortalTables() {
            document.querySelectorAll('.portal-livewire table').forEach((table) => {
                const labels = Array.from(table.querySelectorAll('thead th')).map((cell) => cell.textContent.trim().replace(/\s+/g, ' '));
                table.querySelectorAll('tbody tr').forEach((row) => {
                    Array.from(row.children).forEach((cell, index) => {
                        if (!cell.dataset.label) cell.dataset.label = labels[index] || 'Dato';
                    });
                });
            });
        }

        document.addEventListener('DOMContentLoaded', enhancePortalTables);
        document.addEventListener('livewire:initialized', () => {
            enhancePortalTables();
            Livewire.hook('morph.updated', enhancePortalTables);
        });
    </script>
</body>
</html>
