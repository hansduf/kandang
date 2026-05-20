<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Hans Jaya Poultry') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Font Awesome -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-50">
            <!-- Header Title and Logo -->
            <div class="text-center mb-6">
                <a href="/" class="inline-flex items-center justify-center gap-3 hover:opacity-80 transition-opacity">
                    <span class="text-5xl">🐔</span>
                    <h1 class="font-bold text-3xl text-gray-900">Hans Jaya Poultry</h1>
                </a>
            </div>

            <!-- Main Content Container WIDER (max-w-lg) -->
            <div class="w-full sm:max-w-lg mt-2 px-8 py-6 bg-white shadow-sm overflow-hidden sm:rounded-xl border border-gray-100">
                {{ $slot }}
            </div>
            
            <!-- Footer Info -->
            <div class="mt-8 text-center">
                <p class="text-xs text-gray-500">
                    © {{ date('Y') }} Hans Jaya Poultry.
                </p>
            </div>
        </div>
    </body>
</html>
