<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $fillable = ['tipe'];

    // Relasi ke dokumen
    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'periode_id');
    }
}
