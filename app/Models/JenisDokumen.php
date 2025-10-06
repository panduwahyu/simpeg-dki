<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    protected $table = 'jenis_dokumen';
    protected $fillable = ['nama_dokumen', 'deskripsi', 'periode_tipe', 'tahun'];

    /**
     * Relasi many-to-many ke periode lewat pivot table mandatory_uploads
     */
    public function periode()
    {
        return $this->belongsToMany(Periode::class, 'mandatory_uploads')
                    ->withPivot('is_uploaded');
    }

    /**
     * Relasi ke dokumen (jika tetap ada tabel Dokumen)
     */
    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'jenis_dokumen_id');
    }
}
