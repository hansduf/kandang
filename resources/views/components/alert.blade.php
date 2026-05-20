<!-- resources/views/components/alert.blade.php -->
<div class="mb-6 p-4 rounded-lg {{ match($type) {
    'success' => 'bg-green-50 border border-green-200 text-green-800',
    'warning' => 'bg-yellow-50 border border-yellow-200 text-yellow-800',
    default => 'bg-red-50 border border-red-200 text-red-800'
} }}">
    <div class="flex items-start">
        <div class="flex-shrink-0 text-lg mr-3 mt-0.5">
            @switch($type)
                @case('success')
                    <i class="fas fa-check-circle text-green-600"></i>
                @break
                @case('warning')
                    <i class="fas fa-exclamation-triangle text-yellow-600"></i>
                @break
                @default
                    <i class="fas fa-circle-xmark text-red-600"></i>
            @endswitch
        </div>
        <div class="flex-1">
            <p class="font-medium">{{ match($type) {
                'success' => 'Sukses!',
                'warning' => 'Peringatan',
                default => 'Terjadi Kesalahan'
            } }}</p>
            <p class="text-sm mt-1">{{ $message }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.style.display='none'" class="text-lg hover:opacity-70" title="Tutup">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>
