<!-- resources/views/components/sidebar.blade.php -->
<div class="h-full flex flex-col w-full bg-blue-900 text-white">
    <!-- Logo & Minimize Button -->
    <div class="p-6 border-b border-blue-700 flex items-center justify-between min-h-fit"
         :class="sidebarOpen ? 'px-6 py-6' : 'px-2 py-6'">
        <div :class="sidebarOpen ? 'flex-1 opacity-100' : 'flex-1 opacity-0 w-0 overflow-hidden'" class="transition-all duration-300">
            <h1 class="text-2xl font-bold whitespace-nowrap">Hans Jaya</h1>
            <p class="text-blue-300 text-sm whitespace-nowrap">Poultry Farm</p>
        </div>
        <button @click="sidebarOpen = !sidebarOpen; localStorage.setItem('sidebar-open', sidebarOpen)"
                class="p-2 rounded hover:bg-blue-700 transition flex-shrink-0 text-lg"
                :title="sidebarOpen ? 'Minimize' : 'Expand'">
            <i x-show="sidebarOpen" class="fas fa-chevron-left"></i>
            <i x-show="!sidebarOpen" class="fas fa-chevron-right"></i>
        </button>
    </div>

    <!-- Navigation Menu -->
    <nav class="flex-1 px-2 py-6 space-y-2 overflow-y-auto sidebar-scroll" :class="sidebarOpen ? 'px-4' : 'px-2'">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}"
           class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('dashboard') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
           :class="sidebarOpen ? 'justify-start' : 'justify-center'"
           :title="!sidebarOpen && 'Dashboard'">
            <i class="fas fa-chart-line w-6 h-6 flex items-center justify-center"></i>
            <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Dashboard</span>
        </a>

        @role('pemilik')
            <!-- Kandang Management -->
            <a href="{{ route('kandang.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('kandang.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
               :class="sidebarOpen ? 'justify-start' : 'justify-center'"
               :title="!sidebarOpen && 'Kandang'">
                <i class="fas fa-warehouse w-6 h-6 flex items-center justify-center"></i>
                <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Kandang</span>
            </a>

            <!-- Harga Management -->
            <a href="{{ route('harga.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('harga.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
               :class="sidebarOpen ? 'justify-start' : 'justify-center'"
               :title="!sidebarOpen && 'Harga Telur'">
                <i class="fas fa-tags w-6 h-6 flex items-center justify-center"></i>
                <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Harga Telur</span>
            </a>

            <!-- Penjualan -->
            <a href="{{ route('penjualan.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('penjualan.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
               :class="sidebarOpen ? 'justify-start' : 'justify-center'"
               :title="!sidebarOpen && 'Penjualan'">
                <i class="fas fa-bag-shopping w-6 h-6 flex items-center justify-center"></i>
                <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Penjualan</span>
            </a>

            <!-- Laporan -->
            <div :class="sidebarOpen ? 'pt-4 border-t border-blue-700' : 'pt-2 border-t border-blue-700 mt-2'">
                <p :class="sidebarOpen ? 'opacity-100 max-h-6' : 'opacity-0 max-h-0'" class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase transition-all duration-300 overflow-hidden">Laporan</p>
                <a href="{{ route('laporan.produksi') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('laporan.produksi') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
                   :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                   :title="!sidebarOpen && 'Produksi'">
                    <i class="fas fa-chart-column w-6 h-6 flex items-center justify-center"></i>
                    <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Produksi</span>
                </a>
                <a href="{{ route('laporan.penjualan') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('laporan.penjualan') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
                   :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                   :title="!sidebarOpen && 'Laporan Penjualan'">
                    <i class="fas fa-chart-bar w-6 h-6 flex items-center justify-center"></i>
                    <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Penjualan</span>
                </a>
            </div>

            <!-- Settings -->
            <div :class="sidebarOpen ? 'pt-4 border-t border-blue-700' : 'pt-2 border-t border-blue-700 mt-2'">
                <p :class="sidebarOpen ? 'opacity-100 max-h-6' : 'opacity-0 max-h-0'" class="px-4 py-2 text-xs font-semibold text-blue-300 uppercase transition-all duration-300 overflow-hidden">Admin</p>
                <a href="{{ route('users.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('users.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
                   :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                   :title="!sidebarOpen && 'Manajemen User'">
                    <i class="fas fa-users w-6 h-6 flex items-center justify-center"></i>
                    <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Manajemen User</span>
                </a>
                <a href="{{ route('pengaturan.index') }}"
                   class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('pengaturan.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
                   :class="sidebarOpen ? 'justify-start' : 'justify-center'"
                   :title="!sidebarOpen && 'Pengaturan'">
                    <i class="fas fa-gear w-6 h-6 flex items-center justify-center"></i>
                    <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Pengaturan</span>
                </a>
            </div>
        @endrole

        @role('pekerja')
            <!-- Produksi -->
            <a href="{{ route('produksi.index') }}"
               class="flex items-center px-4 py-3 rounded-lg transition {{ request()->routeIs('produksi.*') ? 'bg-blue-700' : 'hover:bg-blue-700' }}"
               :class="sidebarOpen ? 'justify-start' : 'justify-center'"
               :title="!sidebarOpen && 'Input Produksi'">
                <i class="fas fa-egg w-6 h-6 flex items-center justify-center"></i>
                <span :class="sidebarOpen ? 'ml-3 opacity-100' : 'ml-0 opacity-0 w-0 hidden'" class="transition-all duration-300 whitespace-nowrap">Input Produksi</span>
            </a>
        @endrole
    </nav>

    <!-- User Profile Bottom -->
    <div class="p-4 border-t border-blue-700" :class="sidebarOpen ? 'px-4 py-4' : 'px-2 py-4'">
        <div class="flex items-center" :class="sidebarOpen ? 'justify-between gap-3' : 'justify-center'">
            <div :class="sidebarOpen ? 'opacity-100 flex-1 min-w-0' : 'opacity-0 w-0 overflow-hidden'" class="transition-all duration-300">
                <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-blue-300 truncate">{{ ucfirst(auth()->user()->role) }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="inline flex-shrink-0" :title="!sidebarOpen && 'Logout'">
                @csrf
                <button type="submit" class="text-blue-300 hover:text-white p-1 rounded hover:bg-blue-700 transition">
                    <i class="fas fa-right-from-bracket w-5 h-5"></i>
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Mobile Menu Button -->
<div class="md:hidden fixed top-4 left-4 z-40">
    <button id="mobile-menu-btn" class="bg-blue-900 text-white p-2 rounded">☰</button>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Alpine === 'undefined') {
            const script = document.createElement('script');
            script.src = 'https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js';
            script.defer = true;
            script.onload = function() {
                Alpine.start();
            };
            document.head.appendChild(script);
        }
    });
</script>
@endpush
