<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.19/css/intlTelInput.css">
        @vite('resources/css/app.css')
        <link rel="stylesheet" href="{{ asset('css/sos-redesign.css') }}?v=1.0.1">
        <meta name="theme-color" content="#b80d24">
        <title>{{ $title ?? 'Portal de clientes | Almacenes SOS' }}</title>
    </head>
    <body>
        {{ $slot }}

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                document.addEventListener('livewire:response-error', function (event) {
                    const status = event.detail.status;
                    const content = event.detail.content;

                    if (status === 401 || status === 419 || status === 425) {
                        alert('Tu sesión ha expirado. Inicia sesión nuevamente.');
                        window.location.href = '/login';
                        return;
                    }

                    console.error('Error de Livewire:', status, content);
                });
            });
        </script>
    </body>
</html>
