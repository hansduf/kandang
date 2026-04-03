<!-- resources/views/components/navbar.blade.php -->
<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="flex items-center justify-between px-6 py-4">
        <!-- Left: Page Title -->
        <div>
            <h2 class="text-2xl font-bold text-gray-800">
                {{ $title ?? 'Dashboard' }}
            </h2>
            <p class="text-sm text-gray-500">{{ $subtitle ?? '' }}</p>
        </div>

        <!-- Right: User Info & Logout -->
        <div class="flex items-center gap-4">
            <!-- Notification Bell (future) -->
            <button class="relative text-gray-500 hover:text-gray-700">
                🔔
            </button>

            <!-- User Dropdown -->
            <div class="flex items-center gap-3 pl-4 border-l border-gray-300">
                <div class="text-right">
                    <p class="text-sm font-medium text-gray-800">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-500">{{ ucfirst(auth()->user()->role) }}</p>
                </div>
                
                <!-- Profile Avatar -->
                <div class="w-10 h-10 bg-blue-600 text-white rounded-full flex items-center justify-center font-bold">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
            </div>
        </div>
    </div>
</header>
