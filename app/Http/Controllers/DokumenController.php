<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Dokumen;

class DokumenController extends Controller
{
    /**
     * Tampilkan daftar dokumen
     */
    public function index()
    {
        $dokumens = Dokumen::with(['jenisDokumen', 'periode'])
                           ->orderBy('tanggal_unggah', 'desc')
                           ->get();

        return view('pages.tables', compact('dokumens'));
    }

    /**
     * Preview dokumen PDF dari storage private
     */
    public function preview($id)
    {
        $dokumen = Dokumen::findOrFail($id);

        $path = storage_path('app/private/' . $dokumen->path);

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan di storage. Dicek: ' . $path);
        }

        $mime = mime_content_type($path);
        $file = file_get_contents($path);

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"');
    }

}
