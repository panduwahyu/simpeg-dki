<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $table = 'dokumen';
    protected $fillable = [
        'jenis_dokumen_id',
        'periode_id',
        'tanggal_unggah',
    ];
}
