<x-app-layout>
    <div class="space-y-6">
        <!-- Header -->
        <div class="flex justify-between items-center">
            <h1 class="text-3xl font-bold text-gray-900">Pengaturan Aplikasi</h1>
            <p class="text-sm text-gray-600">Kelola konfigurasi sistem</p>
        </div>

        <!-- Alert Messages -->
        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-xl p-4 text-green-800">
                <p class="font-semibold text-sm"><i class="fas fa-check-circle text-green-600 mr-2"></i>{{ session('success') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-xl p-4 text-red-800">
                <p class="font-semibold text-sm"><i class="fas fa-exclamation-circle text-red-600 mr-2"></i>{{ session('error') }}</p>
            </div>
        @endif

        <!-- Settings Table -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700">Pengaturan</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700">Tipe</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700">Nilai Saat Ini</th>
                        <th class="px-6 py-3 text-left text-xs font-bold text-gray-700">Diubah</th>
                        <th class="px-6 py-3 text-center text-xs font-bold text-gray-700">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($settings as $setting)
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-6 py-4">
                            <div>
                                <p class="font-bold text-gray-900 text-sm">{{ ucwords(str_replace('_', ' ', $setting->kunci)) }}</p>
                                <p class="text-xs text-gray-600 mt-1">{{ Str::limit($setting->keterangan, 50) }}</p>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-1 rounded text-xs font-medium
                                @switch($setting->tipe_data)
                                    @case('string') bg-blue-100 text-blue-700 @break
                                    @case('integer') bg-green-100 text-green-700 @break
                                    @case('float') bg-purple-100 text-purple-700 @break
                                    @case('boolean') bg-orange-100 text-orange-700 @break
                                    @default bg-gray-100 text-gray-700
                                @endswitch
                            ">
                                @switch($setting->tipe_data)
                                    @case('string') Teks @break
                                    @case('integer') Angka @break
                                    @case('float') Desimal @break
                                    @case('boolean') Bool @break
                                    @default {{ $setting->tipe_data }}
                                @endswitch
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-mono text-sm font-bold text-gray-900">
                                @if($setting->tipe_data === 'boolean')
                                    @if($setting->nilai == 1)
                                        <i class="fas fa-check-circle text-green-600 mr-1"></i>Ya
                                    @else
                                        <i class="fas fa-times-circle text-red-600 mr-1"></i>Tidak
                                    @endif
                                @else
                                    {{ $setting->nilai }}
                                @endif
                            </p>
                        </td>
                        <td class="px-6 py-4 text-xs text-gray-500">
                            {{ $setting->updated_at ? $setting->updated_at->diffForHumans() : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <a href="{{ route('pengaturan.edit', $setting) }}" 
                                class="inline-block px-3 py-1.5 text-xs font-medium text-blue-600 bg-blue-50 rounded hover:bg-blue-100 transition">
                                Edit
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <p class="text-lg font-medium text-gray-500">📭 Belum ada pengaturan</p>
                            <p class="text-sm text-gray-400 mt-1">Tidak ada data pengaturan aplikasi yang tersedia saat ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Info Box -->
        <div class="bg-blue-50 border border-blue-200 rounded-xl p-5">
            <p class="text-sm font-bold text-blue-900 mb-3"><i class="fas fa-info-circle mr-2"></i>Panduan Pengaturan</p>
            <ul class="text-xs text-blue-800 space-y-2 list-disc list-inside">
                <li><strong>Edit:</strong> Klik tombol "Edit" pada kartu pengaturan yang ingin diubah</li>
                <li><strong>Validasi:</strong> Pastikan nilai sesuai dengan tipe data yang ditentukan</li>
                <li><strong>Berlaku Instan:</strong> Perubahan langsung berlaku ke seluruh sistem</li>
                <li><strong>Konversi Butir/KG:</strong> Standar 16 butir = 1 kg</li>
            </ul>
        </div>
    </div>
</x-app-layout>
