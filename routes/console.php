<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Models\HargaTelur;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule untuk auto-update status harga
Schedule::call(function () {
    // Auto-mark hangus jika tanggal akhir sudah terlewat
    HargaTelur::where('status', 'aktif')
              ->where('tanggal_akhir', '<', now()->toDateString())
              ->update(['status' => 'hangus']);
    
    // Auto-mark aktif jika tanggal berlaku sudah tiba
    HargaTelur::where('status', 'aktif')
              ->where('tanggal_berlaku', '<=', now()->toDateString())
              ->whereNull('tanggal_akhir')
              ->update([]); // Status tetap aktif, tidak ada perubahan
})->dailyAt('00:01'); // Jalankan setiap hari pukul 00:01 waktu Indonesia (UTC+7)
