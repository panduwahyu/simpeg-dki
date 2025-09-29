<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MandatoryUploadsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $users = DB::table('users')->where('role', 'pegawai')->get();
        $jenisDokumen = DB::table('jenis_dokumen')->get();
        $periode = DB::table('periode')->get();
        $dokumen = DB::table('dokumen')->get();

        $data = [];

        foreach ($users as $user) {
            foreach ($jenisDokumen as $doc) {
                foreach ($periode as $per) {
                    $uploaded = $dokumen->where('user_id', $user->id)
                        ->where('jenis_dokumen_id', $doc->id)
                        ->where('periode_id', $per->id)
                        ->first();

                    $data[] = [
                        'user_id' => $user->id,
                        'jenis_dokumen_id' => $doc->id,
                        'periode_id' => $per->id,
                        'is_uploaded' => $uploaded ? true : false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }

        DB::table('mandatory_uploads')->insert($data);
    }
}
