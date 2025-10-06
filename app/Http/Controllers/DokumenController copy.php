<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Dokumen;
use App\Models\JenisDokumen;
use App\Models\Periode;

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
        $jenisDokumen = JenisDokumen::all();
        $periode = Periode::all();
        
        $query = Dokumen::query();
        $dokumen = $query->paginate(10)->withQueryString();; // bisa pakai paginate
        return view('pages.tables', compact('dokumens', 'jenisDokumen', 'periode', 'dokumen'));
        
        // filter berdasarkan jenis_dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }
        
        // filter berdasarkan periode
        if ($request->filled('periode_id')) {
            $query->where('periode_id', $request->periode_id);
        }
        
        // filter berdasarkan keyword (misal nama file atau keterangan)
        if ($request->filled('search')) {
            $query->where('nama_file', 'like', '%' . $request->search . '%');
        }
        
        
        // return view('pages.tables', compact('dokumen'));
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
