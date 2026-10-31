{{--
    Storefront layout (gulugule shop). Use in every customer-facing page:
    <x-layouts::shop :title="__('Products')"> ... </x-layouts::shop>
    The starter-kit layouts (layouts/app*) stay for dashboard / settings.
--}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0" />

        <title>{{ filled($title ?? null) ? $title.' - gulugule shop' : 'gulugule shop' }}</title>

        <link rel="icon" href="/favicon.ico" sizes="any">
        <link rel="icon" href="/favicon.svg" type="image/svg+xml">

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-body text-gulu-ink antialiased">
        <x-site-header />

        <main class="mx-auto w-full max-w-[1440px] px-6 py-10 lg:px-12">
            @if (session('status'))
                <div class="mb-6 rounded-2xl bg-gulu-success/10 px-5 py-3 text-sm text-gulu-success" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-6 rounded-2xl bg-red-50 px-5 py-3 text-sm text-red-700" role="alert">
                    {{ session('error') }}
                </div>
            @endif

            {{ $slot }}
        </main>

        <x-site-footer />

        @fluxScripts
    </body>
</html>
