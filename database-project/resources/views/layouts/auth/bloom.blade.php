{{-- Auth pages in the gulugule shop theme (Figma: 02 Sign Up / 03 Sign In): flower panel left, form right. --}}
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
        <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500;600&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-white font-body text-gulu-ink antialiased">
        <div class="grid min-h-screen lg:grid-cols-2">
            <aside class="relative hidden flex-col justify-between overflow-hidden bg-linear-to-br from-[#cfc0d6] to-[#d8b8e0] p-10 text-white lg:flex">
                <a href="{{ route('home') }}" class="font-display text-3xl font-medium">gulugule shop</a>

                <x-bloom-illustration class="mx-auto max-h-[55vh] w-full max-w-md" />

                <p class="font-display text-6xl font-light leading-tight xl:text-7xl">Speak in blooms.</p>
            </aside>

            <main class="flex flex-col justify-center px-6 py-12 sm:px-12 lg:px-20">
                <div class="mx-auto flex w-full max-w-md flex-col gap-6">
                    <a href="{{ route('home') }}" class="font-display text-3xl font-medium lg:hidden">gulugule shop</a>

                    {{ $slot }}
                </div>
            </main>
        </div>

        @fluxScripts
    </body>
</html>
