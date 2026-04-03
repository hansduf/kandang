<x-guest-layout>
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900">🐔 Hans Jaya Poultry</h1>
        <p class="text-gray-600 mt-2">Sistem Manajemen Produksi dan Penjualan Telur</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf

        <!-- Email/Username Address -->
        <div>
            <x-input-label for="email" :value="'Email atau Username'" />
            <x-text-input id="email" class="block mt-1 w-full" type="text" name="email" :value="old('email')" required autofocus />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="'Kata Sandi'" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remember">
                <span class="ms-2 text-sm text-gray-600">Ingat saya</span>
            </label>
        </div>

        <div class="flex items-center justify-between gap-4 mt-6">
            <x-primary-button class="w-full">
                Masuk
            </x-primary-button>
        </div>

        <!-- Demo Info -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
            <p class="text-xs text-blue-700 font-semibold mb-3">📝 Test Accounts:</p>
            <div class="space-y-2">
                <div>
                    <p class="text-xs text-blue-600 font-medium">👑 Pemilik (Owner):</p>
                    <p class="text-xs text-blue-600 ml-3">Username: <strong>pemilik</strong></p>
                    <p class="text-xs text-blue-600 ml-3">Password: <strong>password</strong></p>
                </div>
                <div>
                    <p class="text-xs text-blue-600 font-medium">🐔 Kandang 1:</p>
                    <p class="text-xs text-blue-600 ml-3">Username: <strong>kandang1</strong></p>
                    <p class="text-xs text-blue-600 ml-3">Password: <strong>password</strong></p>
                </div>
                <div>
                    <p class="text-xs text-blue-600 font-medium">🐔 Kandang 2:</p>
                    <p class="text-xs text-blue-600 ml-3">Username: <strong>kandang2</strong></p>
                    <p class="text-xs text-blue-600 ml-3">Password: <strong>password</strong></p>
                </div>
                <div>
                    <p class="text-xs text-blue-600 font-medium">🐔 Kandang 3:</p>
                    <p class="text-xs text-blue-600 ml-3">Username: <strong>kandang3</strong></p>
                    <p class="text-xs text-blue-600 ml-3">Password: <strong>password</strong></p>
                </div>
            </div>
        </div>
    </form>
</x-guest-layout>
