<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
         // Ambil contoh data user, jenis dokumen, periode
        $user = DB::table('users')->where('role', 'pegawai')->first();
        $jenisDokumen = DB::table('jenis_dokumen')->where('nama_dokumen', 'SPT')->first();
        $periode = DB::table('periode')->where('tahun', 2024)->where('tipe', 'tahunan')->first();

        // Masukkan 1 contoh dokumen yang sudah diunggah
        DB::table('dokumen')->insert([
            'path' => 'uploads/SPT_2024_' . $user->NIP . '.pdf',
            'user_id' => $user->id,
            'jenis_dokumen_id' => $jenisDokumen->id,
            'periode_id' => $periode->id,
            'tanggal_unggah' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
