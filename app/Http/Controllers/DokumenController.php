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
    public function index(Request $request)
    {
        $query = Dokumen::query();

        // Filter berdasarkan jenis dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }

        // Filter berdasarkan tipe periode
        if ($request->filled('tipe')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('tipe', $request->tipe);
            });
        }

        // Filter berdasarkan tahun
        if ($request->filled('tahun')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

        // Filter berdasarkan tanggal unggah
        if ($request->filled('tanggal')) {
            $query->whereDate('tanggal_unggah', $request->tanggal);
        }

        // Ambil data dengan relasi
        $dokumen = $query->with(['jenisDokumen', 'periode'])
                         ->paginate(10)
                         ->withQueryString();

        // Untuk dropdown filter
        $jenisDokumen = JenisDokumen::all();
        $periode = Periode::select('tipe', 'tahun')->distinct()->get();

        return view('pages.tables', compact('dokumen', 'jenisDokumen', 'periode'));
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
