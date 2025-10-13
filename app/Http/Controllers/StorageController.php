<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class StorageController extends Controller
{
    public function refresh()
    {
        try {
            // Ambil semua path yang ada di tabel dokumen
            $dbPaths = DB::table('dokumen')->pluck('path')->toArray();

            // Ambil semua file yang ada di storage/app/private/uploads
            $storageFiles = Storage::disk('private')->files('uploads');

            $deletedFiles = [];

            // Bandingkan, hapus yang tidak ada di database
            foreach ($storageFiles as $file) {
                if (!in_array($file, $dbPaths)) {
                    Storage::disk('private')->delete($file);
                    $deletedFiles[] = $file;
                }
            }

            return back()->with('success', 'Penyimpanan telah diperbarui. ' . count($deletedFiles) . ' file dihapus.');
        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
