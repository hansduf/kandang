<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProduksiTelur extends Model
{
    protected $table = 'produksi_telur';

    protected $fillable = [
        'kandang_id',
        'user_id',
        'tanggal_produksi',
        'satuan_input',
        'jumlah_input',
        'jumlah_butir',
        'jumlah_kg',
        'ayam_mati',
        'catatan',
        'ayam_hidup',
        'hdp',
        'hhp',
        'mortality',
    ];

    protected $casts = [
        'tanggal_produksi' => 'date',
    ];

    public function kandang()
    {
        return $this->belongsTo(Kandang::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
