<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PeriodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        DB::table('periode')->insert([
            ['periode_key' => '2024', 'tahun' => 2024, 'bulan' => null, 'tipe' => 'tahunan', 'label' => 'Tahun 2024'],
            ['periode_key' => '2024-01', 'tahun' => 2024, 'bulan' => 1, 'tipe' => 'bulanan', 'label' => 'Januari 2024']
        ]);
    }
}
