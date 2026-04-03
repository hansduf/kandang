<x-app-layout>
    <div class="space-y-6">
        <h1 class="text-3xl font-bold text-gray-900">Pengaturan Aplikasi</h1>

        <div class="space-y-4">
            @forelse($settings as $setting)
                <div class="bg-white rounded-xl shadow-sm p-6 border-l-4 border-blue-500 hover:shadow-md transition">
                    <div class="flex justify-between items-start">
                        <div class="flex-1">
                            <h3 class="text-lg font-bold text-gray-900">{{ ucwords(str_replace('_', ' ', $setting->kunci)) }}</h3>
                            <p class="text-sm text-gray-500 mt-1">{{ $setting->keterangan }}</p>
                            <div class="bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg p-4 border border-gray-200 mt-4">
                                <p class="font-mono text-sm font-bold text-gray-900">{{ $setting->nilai }}</p>
                                <span class="text-xs text-gray-500">
                                    @switch($setting->tipe_data)
                                        @case('string')
                                            <span class="inline-block mt-2 px-2 py-1 bg-blue-100 text-blue-700 rounded">Teks</span>
                                            @break
                                        @case('integer')
                                            <span class="inline-block mt-2 px-2 py-1 bg-green-100 text-green-700 rounded">Angka Bulat</span>
                                            @break
                                        @case('float')
                                            <span class="inline-block mt-2 px-2 py-1 bg-purple-100 text-purple-700 rounded">Angka Desimal</span>
                                            @break
                                        @case('boolean')
                                            <span class="inline-block mt-2 px-2 py-1 bg-orange-100 text-orange-700 rounded">Ya/Tidak</span>
                                            @break
                                        @default
                                            <span class="inline-block mt-2 px-2 py-1 bg-gray-100 text-gray-700 rounded">{{ $setting->tipe_data }}</span>
                                    @endswitch
                                </span>
                            </div>
                        </div>
                        <a href="{{ route('pengaturan.edit', $setting) }}" class="ml-4 bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition font-medium whitespace-nowrap flex-shrink-0">
                            Edit
                        </a>
                    </div>
                </div>
            @empty
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 border border-amber-200 rounded-xl p-6 text-amber-800">
                    <p class="text-lg font-medium">Belum ada pengaturan</p>
                    <p class="text-sm mt-1">Tidak ada data pengaturan aplikasi yang tersedia saat ini.</p>
                </div>
            @endforelse
        </div>
    </div>
</x-app-layout>
