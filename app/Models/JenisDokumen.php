<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisDokumen extends Model
{
    protected $table = 'jenis_dokumen';
    protected $fillable = ['nama_dokumen', 'periode_tipe', 'tahun'];

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

    public function mandatoryUploads()
    {
        return $this->belongsToMany(
            \App\Models\User::class,       // Model yang di-relasikan
            'mandatory_uploads',           // Nama pivot table
            'jenis_dokumen_id',            // Foreign key di pivot untuk JenisDokumen
            'user_id'                      // Foreign key di pivot untuk User
        );
    }
}
