<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaTelur extends Model
{
    protected $table = 'harga_telur';

    protected $fillable = [
        'jenis_harga',
        'harga_per_kg',
        'harga_per_butir',
        'tanggal_berlaku',
        'tanggal_akhir',
        'status',
        'user_id',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_berlaku' => 'date',
        'tanggal_akhir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detailPenjualan()
    {
        return $this->hasMany(DetailPenjualan::class);
    }

    // Scope untuk harga aktif hari ini - prioritas harga paling baru
    public function scopeAktif($query)
    {
        return $query->where('status', 'aktif')
                    ->where(function($q) {
                        // Ambil harga yang berlaku hari ini atau di masa depan
                        $q->where('tanggal_berlaku', '<=', now()->toDateString())
                          ->where(function($subQ) {
                              $subQ->whereNull('tanggal_akhir')
                                    ->orWhere('tanggal_akhir', '>=', now()->toDateString());
                          });
                    })
                    // Order by tanggal_berlaku DESC untuk ambil yang PALING BARU
                    ->orderBy('tanggal_berlaku', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    // Check status harga
    public function isAktif()
    {
        return $this->status === 'aktif' &&
               $this->tanggal_berlaku->lte(now()->toDateString()) &&
               (!$this->tanggal_akhir || $this->tanggal_akhir->gte(now()->toDateString()));
    }

    public function isHangus()
    {
        return $this->status === 'hangus' || 
               ($this->tanggal_akhir && $this->tanggal_akhir->lt(now()->toDateString()));
    }

    // Scope untuk dapatkan harga yang berlaku pada tanggal dan jam tertentu
    // Gunakan untuk matching harga transaksi dengan waktu yang tepat
    public function scopeAktifPadaTanggalJam($query, $tanggal, $jam = null)
    {
        return $query->where('status', 'aktif')
                    ->where('tanggal_berlaku', '<=', $tanggal)
                    ->where(function($q) use ($tanggal) {
                        // Harga berlaku sampai sekarang atau hingga tanggal_akhir
                        $q->whereNull('tanggal_akhir')
                          ->orWhere('tanggal_akhir', '>=', $tanggal);
                    })
                    // Untuk hari yang sama dengan multiple harga, ambil yang dibuat paling akhir
                    // (membedakan berdasarkan created_at)
                    ->orderBy('tanggal_berlaku', 'desc')
                    ->orderBy('created_at', 'desc');
    }

    // Static method untuk helper
    public static function getHargaBerlakuPada($jenis, $tanggal, $jam = null)
    {
        return self::where('jenis_harga', $jenis)
                   ->aktifPadaTanggalJam($tanggal, $jam)
                   ->first();
    }
}
