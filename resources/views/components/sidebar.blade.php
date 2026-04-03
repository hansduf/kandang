<!-- resources/views/components/sidebar.blade.php -->
<div class="hidden md:flex flex-col w-64 bg-blue-900 text-white">
    <!-- Logo -->
    <div class="p-6 border-b border-blue-700">
        <h1 class="text-2xl font-bold">Hans Jaya</h1>
        <p class="text-blue-300 text-sm">Poultry Farm</p>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
            <span class="mr-3">📊</span>
            <span>Dashboard</span>
        </a>

        @role('pemilik')
            <!-- Kandang Management -->
            <a href="{{ route('kandang.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('kandang.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                <span class="mr-3">🏠</span>
                <span>Kandang</span>
            </a>

            <!-- Harga Management -->
            <a href="{{ route('harga.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('harga.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                <span class="mr-3">💰</span>
                <span>Harga Telur</span>
            </a>

            <!-- Penjualan -->
            <a href="{{ route('penjualan.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('penjualan.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                <span class="mr-3">📦</span>
                <span>Penjualan</span>
            </a>

            <!-- Laporan -->
            <div class="pt-4 border-t border-blue-700">
                <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Laporan</p>
                <a href="{{ route('laporan.produksi') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('laporan.produksi') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                    <span class="mr-3">📈</span>
                    <span>Produksi</span>
                </a>
                <a href="{{ route('laporan.penjualan') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('laporan.penjualan') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                    <span class="mr-3">📉</span>
                    <span>Penjualan</span>
                </a>
            </div>

            <!-- Settings -->
            <div class="pt-4 border-t border-blue-700">
                <p class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase">Admin</p>
                <a href="{{ route('users.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('users.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                    <span class="mr-3">👥</span>
                    <span>Manajemen User</span>
                </a>
                <a href="{{ route('pengaturan.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('pengaturan.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                    <span class="mr-3">⚙️</span>
                    <span>Pengaturan</span>
                </a>
            </div>
        @endrole

        @role('pekerja')
            <!-- Produksi -->
            <a href="{{ route('produksi.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('produksi.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}">
                <span class="mr-3">🐔</span>
                <span>Input Produksi</span>
            </a>
        @endrole
    </nav>

    <!-- User Profile Bottom -->
    <div class="p-4 border-t border-blue-700">
        <div class="flex items-center justify-between">
            <div class="flex-1">
                <p class="text-sm font-medium">{{ auth()->user()->name }}</p>
                <p class="text-xs text-blue-300">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" title="Logout" class="text-blue-300 hover:text-white">
                    🚪
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Menu Button (untuk future implementation) -->
<div class="md:hidden fixed top-4 left-4 z-40">
    <button id="mobile-menu-btn" class="bg-blue-900 text-white p-2 rounded">☰</button>
</div>
