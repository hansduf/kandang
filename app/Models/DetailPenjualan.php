<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPenjualan extends Model
{
    protected $table = 'detail_penjualan';

    protected $fillable = [
        'penjualan_id',
        'harga_telur_id',
        'satuan_jual',
        'jumlah_jual',
        'jumlah_butir',
        'jumlah_kg',
        'harga_satuan',
        'subtotal',
        'harga_per_butir_saat_jual',
        'harga_per_kg_saat_jual',
        'tanggal_penjualan',
        'jam_penjualan',
    ];

    public const TIMESTAMPS = false;

    protected $casts = [
        'tanggal_penjualan' => 'date',
        'jam_penjualan' => 'string',
    ];

    public function penjualan()
    {
        return $this->belongsTo(Penjualan::class);
    }

    public function hargaTelur()
    {
        return $this->belongsTo(HargaTelur::class);
    }
}
