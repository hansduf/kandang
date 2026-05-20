<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hans Jaya Poultry - Sistem Manajemen Peternakan</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fortawesome/fontawesome-free@6.4.0/css/all.min.css">
    
    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased text-gray-900 bg-gray-50 selection:bg-blue-500 selection:text-white">
    <!-- Navbar -->
    <header class="bg-white border-b border-gray-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo -->
                <div class="flex items-center gap-3">
                    <span class="text-4xl">🐔</span>
                    <span class="font-bold text-xl md:text-2xl text-blue-900 tracking-tight">Hans Jaya Poultry</span>
                </div>
                
                <!-- Navigation Actions -->
                <div class="flex items-center">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
                                Dashboard
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-6 py-2.5 bg-blue-600 border border-transparent rounded-lg font-semibold text-white hover:bg-blue-700 transition-colors shadow-sm">
                                Masuk Aplikasi
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </header>

    <!-- Hero Section -->
    <main>
        <div class="relative pt-20 pb-20 flex content-center items-center justify-center min-h-[75vh]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative text-center">
                
                <h1 class="text-4xl md:text-5xl lg:text-7xl font-bold text-gray-900 tracking-tight leading-tight mb-6">
                    Kelola <span class="text-blue-600">Peternakan Ayam</span><br> Anda dengan Mudah
                </h1>
                
                <p class="mt-4 text-lg md:text-xl text-gray-600 max-w-3xl mx-auto mb-10 leading-relaxed md:px-12">
                    Sistem manajemen terintegrasi untuk monitoring produksi harian, pergerakan inventori, dan pelaporan yang membuat peternakan Anda semakin efisien.
                </p>
                
                <div class="flex justify-center">
                    @if (Route::has('login'))
                        @auth
                            <a href="{{ url('/dashboard') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all gap-2 transform hover:-translate-y-0.5">
                                Akses Dashboard <i class="fas fa-arrow-right"></i>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-4 text-base font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-xl shadow-sm transition-all gap-2 transform hover:-translate-y-0.5">
                                Mulai Sekarang <i class="fas fa-arrow-right"></i>
                            </a>
                        @endauth
                    @endif
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200 py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
            &copy; {{ date('Y') }} Hans Jaya Poultry. Hak Cipta Dilindungi.
        </div>
    </footer>
</body>
</html>
