<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NamaPegawai extends Model
{
    protected $table = 'users';
    protected $fillable = ['name','role'];

    public function dokumen()
    {
        return $this->hasMany(Dokumen::class, 'user_id');
    }
}
