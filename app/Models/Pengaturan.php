<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $fillable = [
        'kunci',
        'nilai',
        'tipe_data',
        'keterangan',
    ];

    public const CREATED_AT = null;
    public const UPDATED_AT = 'updated_at';
}
