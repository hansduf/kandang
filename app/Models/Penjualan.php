<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    protected $table = 'penjualan';
    protected $fillable = [
        'user_id',
        'tanggal_jual',
        'nama_pembeli',
        'total_harga',
        'keterangan',
    ];

    protected $casts = [
        'tanggal_jual' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function detail()
    {
        return $this->hasMany(DetailPenjualan::class);
    }
}
