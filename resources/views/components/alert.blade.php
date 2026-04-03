<!-- resources/views/components/alert.blade.php -->
<div class="mb-6 p-4 rounded-lg {{ match($type) {
    'success' => 'bg-green-50 border border-green-200 text-green-800',
    'warning' => 'bg-yellow-50 border border-yellow-200 text-yellow-800',
    default => 'bg-red-50 border border-red-200 text-red-800'
} }}">
    <div class="flex items-start">
        <div class="flex-shrink-0 text-2xl mr-3">
            {{ match($type) {
                'success' => '✅',
                'warning' => '⚠️',
                default => '❌'
            } }}
        </div>
        <div class="flex-1">
            <p class="font-medium">{{ match($type) {
                'success' => 'Sukses!',
                'warning' => 'Peringatan',
                default => 'Terjadi Kesalahan'
            } }}</p>
            <p class="text-sm mt-1">{{ $message }}</p>
        </div>
        <button onclick="this.parentElement.parentElement.style.display='none'" class="text-lg hover:opacity-70">
            ✕
        </button>
    </div>
</div>
