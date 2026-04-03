<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KandangController;
use App\Http\Controllers\ProduksiTelurController;
use App\Http\Controllers\HargaTelurController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// Dashboard untuk semua role yang login
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Route khusus PEMILIK
Route::middleware(['auth', 'verified', 'role:pemilik'])->group(function () {
    Route::resource('kandang', KandangController::class);
    Route::resource('harga', HargaTelurController::class, ['names' => 'harga']);
    Route::resource('penjualan', PenjualanController::class);
    Route::get('/penjualan-harga-by-date', [PenjualanController::class, 'getHargaByDate'])->name('penjualan.hargaByDate');
    Route::get('/api/stok', [PenjualanController::class, 'getStok'])->name('api.stok');
    Route::resource('pengaturan', PengaturanController::class);
    Route::resource('users', UserController::class);
    
    // Laporan
    Route::get('/laporan/produksi', [LaporanController::class, 'produksi'])->name('laporan.produksi');
    Route::get('/laporan/penjualan', [LaporanController::class, 'penjualan'])->name('laporan.penjualan');
    
    // Export
    Route::get('/laporan/produksi/export-pdf', [LaporanController::class, 'exportProduksiPdf'])->name('laporan.exportProduksiPdf');
    Route::get('/laporan/produksi/export-excel', [LaporanController::class, 'exportProduksiExcel'])->name('laporan.exportProduksiExcel');
    Route::get('/laporan/penjualan/export-pdf', [LaporanController::class, 'exportPenjualanPdf'])->name('laporan.exportPenjualanPdf');
    Route::get('/laporan/penjualan/export-excel', [LaporanController::class, 'exportPenjualanExcel'])->name('laporan.exportPenjualanExcel');
});

// Route khusus PEKERJA
Route::middleware(['auth', 'verified', 'role:pekerja'])->group(function () {
    Route::resource('produksi', ProduksiTelurController::class)->only(['index', 'create', 'store', 'show']);
});

require __DIR__.'/auth.php';
