<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    //
    use HasFactory;

    protected $table = 'dokumen';
    protected $fillable = [
        'jenis_dokumen_id',
        'periode_id',
        'tanggal_unggah'
    ];

    // Relation database
    public function jenisDokumen()
    {
        return $this->belongsTo(JenisDokumen::class, 'jenis_dokumen_id');
    }

    public function periode()
    {
        return $this->belongsTo(Periode::class, 'periode_id');
    }
}
