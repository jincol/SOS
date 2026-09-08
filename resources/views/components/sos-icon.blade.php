@props(['name', 'class' => ''])
@php
    $paths = [
        'menu' => '<path d="M4 7h16M4 12h16M4 17h16"/>',
        'close' => '<path d="m6 6 12 12M18 6 6 18"/>',
        'dashboard' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
        'receipt' => '<path d="M6 3h12v18l-3-2-3 2-3-2-3 2V3Z"/><path d="M9 8h6M9 12h6"/>',
        'inbox' => '<path d="M4 4h16v16H4z"/><path d="M4 14h5l2 3h2l2-3h5"/>',
        'outbox' => '<path d="M4 4h16v16H4z"/><path d="M4 14h5l2 3h2l2-3h5M12 13V6M9 9l3-3 3 3"/>',
        'truck' => '<path d="M3 6h11v10H3zM14 10h4l3 3v3h-7z"/><circle cx="7" cy="18" r="2"/><circle cx="18" cy="18" r="2"/>',
        'tools' => '<path d="m14 7 3-3 3 3-3 3M4 20l8-8M5 4l15 15M4 4l4 1 1 4"/>',
        'warehouse' => '<path d="m3 9 9-6 9 6v12H3V9Z"/><path d="M8 21v-8h8v8M7 9h10"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21c1-5 4-7 8-7s7 2 8 7"/>',
        'logout' => '<path d="M10 4H4v16h6M14 8l4 4-4 4M18 12H8"/>',
        'building' => '<path d="M4 21V5l8-2v18M12 8h8v13M7 8h2M7 12h2M7 16h2M15 12h2M15 16h2"/>',
        'chevron-down' => '<path d="m6 9 6 6 6-6"/>',
        'phone' => '<path d="M6 3h4l2 5-3 2c1 3 2 4 5 5l2-3 5 2v4c0 2-2 3-4 3A15 15 0 0 1 3 7c0-2 1-4 3-4Z"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'clock' => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'money' => '<circle cx="12" cy="12" r="9"/><path d="M15 8.5c-.7-.5-1.7-.8-2.8-.8-1.7 0-3 .8-3 2s1.1 1.8 3 2.2 3 1 3 2.2-1.3 2.1-3 2.1c-1.2 0-2.3-.4-3.1-1M12 5.8v12.4"/>',
        'alert' => '<path d="M12 3 2 21h20L12 3Z"/><path d="M12 9v5M12 18h.01"/>',
        'support' => '<circle cx="12" cy="12" r="9"/><path d="M8 14v-3a4 4 0 0 1 8 0v3M7 14h2v4H7zM15 14h2v4h-2z"/>',
    ];
    $path = $paths[$name] ?? $paths['dashboard'];
@endphp
<svg {{ $attributes->merge(['class' => 'icon '.$class]) }} viewBox="0 0 24 24" aria-hidden="true" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">{!! $path !!}</svg>
