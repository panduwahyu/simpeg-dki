<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';

    protected $fillable = [
        'periode_key',
        'tahun',
        'bulan',
        'triwulan',
        'tipe',
        'label',
    ];

    /**
     * Relasi many-to-many ke JenisDokumen via pivot table mandatory_uploads
     */
    public function jenisDokumen()
    {
        return $this->belongsToMany(JenisDokumen::class, 'mandatory_uploads')
                    ->withPivot('is_uploaded');
    }
}
