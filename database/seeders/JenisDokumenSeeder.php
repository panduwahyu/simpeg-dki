<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class JenisDokumenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('jenis_dokumen')->insert([
            ['nama_dokumen' => 'SPT', 'periode_tipe' => 'tahunan', 'deskripsi' => 'Laporan SPT Tahunan'],
            ['nama_dokumen' => 'SKP', 'periode_tipe' => 'bulanan', 'deskripsi' => 'Laporan SKP Bulanan']
        ]);
    }
}
