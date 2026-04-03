<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokTelur extends Model
{
    protected $table = 'stok_telur';

    protected $fillable = [
        'stok_butir',
        'stok_kg',
    ];

    public const CREATED_AT = null;
}
