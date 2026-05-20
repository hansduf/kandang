<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kandang extends Model
{
    protected $table = 'kandangs';

    protected $fillable = [
        'nama_kandang',
        'jumlah_ayam',
        'keterangan',
        'status',
        'pic_id',
    ];

    public function produksiTelur()
    {
        return $this->hasMany(ProduksiTelur::class);
    }

    public function pekerja()
    {
        return $this->hasMany(User::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }
}
