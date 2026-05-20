<x-guest-layout>
    <!-- Session Status -->
    @if ($status = session('status'))
        <div class="mb-4 bg-green-50 border border-green-200 rounded-lg p-3">
            <p class="text-sm text-green-800">{{ $status }}</p>
        </div>
    @endif

    <div class="text-center mb-6">
        <h2 class="text-base font-medium text-gray-600">Masuk ke Sistem Manajemen</h2>
        <p class="text-xs text-gray-500 mt-1">Produksi dan Penjualan Telur</p>
    </div>

    <!-- Login Form directly in the layout slot -->
    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email/Username Address -->
        <div>
            <label for="email" class="block text-sm font-medium text-gray-700">Email atau Username</label>
            <input id="email" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3 sm:text-sm" 
                type="text" name="email" :value="old('email')" required autofocus />
            @error('email')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-sm font-medium text-gray-700">Kata Sandi</label>
            <input id="password" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 py-2.5 px-3 sm:text-sm"
                type="password" name="password" required autocomplete="current-password" />
            @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <!-- Remember Me & Forgot Password -->
        <div class="flex items-center justify-between">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <!-- Submit Button -->
        <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2.5 rounded-lg hover:bg-blue-700 font-medium transition duration-200 mt-2">
            Masuk
        </button>
    </form>

    <!-- Demo Accounts Info (Compact Grid) -->
    <div class="mt-6 bg-blue-50/50 border border-blue-100 rounded-lg p-4">
        <p class="text-xs font-semibold text-blue-900 mb-3 flex items-center">
            <i class="fas fa-info-circle text-blue-600 mr-1.5"></i> Test Accounts
        </p>
        
        <div class="grid grid-cols-2 gap-3">
            <!-- Pemilik Card -->
            <div class="bg-white rounded-md p-2.5 border border-blue-100 shadow-sm">
                <div class="text-xs font-semibold text-blue-700 mb-1 truncate">
                    <i class="fas fa-crown text-yellow-500 w-4"></i>Pemilik
                </div>
                <div class="text-[11px] text-gray-600 leading-relaxed">
                    u: <span class="font-mono font-medium text-gray-900">pemilik</span><br>
                    p: <span class="font-mono font-medium text-gray-900">password</span>
                </div>
            </div>

            <!-- Kandang 1 Card -->
            <div class="bg-white rounded-md p-2.5 border border-orange-100 shadow-sm">
                <div class="text-xs font-semibold text-orange-700 mb-1 truncate">
                    <i class="fas fa-egg text-orange-500 w-4"></i>Kandang 1
                </div>
                <div class="text-[11px] text-gray-600 leading-relaxed">
                    u: <span class="font-mono font-medium text-gray-900">kandang1</span><br>
                    p: <span class="font-mono font-medium text-gray-900">password</span>
                </div>
            </div>

            <!-- Kandang 2 Card -->
            <div class="bg-white rounded-md p-2.5 border border-orange-100 shadow-sm">
                <div class="text-xs font-semibold text-orange-700 mb-1 truncate">
                    <i class="fas fa-egg text-orange-500 w-4"></i>Kandang 2
                </div>
                <div class="text-[11px] text-gray-600 leading-relaxed">
                    u: <span class="font-mono font-medium text-gray-900">kandang2</span><br>
                    p: <span class="font-mono font-medium text-gray-900">password</span>
                </div>
            </div>

            <!-- Kandang 3 Card -->
            <div class="bg-white rounded-md p-2.5 border border-orange-100 shadow-sm">
                <div class="text-xs font-semibold text-orange-700 mb-1 truncate">
                    <i class="fas fa-egg text-orange-500 w-4"></i>Kandang 3
                </div>
                <div class="text-[11px] text-gray-600 leading-relaxed">
                    u: <span class="font-mono font-medium text-gray-900">kandang3</span><br>
                    p: <span class="font-mono font-medium text-gray-900">password</span>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
