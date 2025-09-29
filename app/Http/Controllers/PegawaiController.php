<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function index()
    {
        $userId = Auth::id(); // Ambil ID user yang login

        // Ambil semua data upload pegawai langsung dari mandatory_uploads
        $uploads = DB::table('mandatory_uploads')
            ->join('jenis_dokumen', 'mandatory_uploads.jenis_dokumen_id', '=', 'jenis_dokumen.id')
            ->join('periode', 'mandatory_uploads.periode_id', '=', 'periode.id')
            ->where('mandatory_uploads.user_id', $userId)
            ->select(
                'jenis_dokumen.id as dokumen_id',
                'jenis_dokumen.nama_dokumen',
                'periode.id as periode_id',
                'periode.periode_key',
                'mandatory_uploads.is_uploaded',
                'mandatory_uploads.updated_at'
            )
            ->orderBy('periode.periode_key')
            ->orderBy('jenis_dokumen.nama_dokumen')
            ->get();

        // Hitung file yang belum diupload
        $belumUpload = $uploads->where('is_uploaded', 0);

        // Hitung ringkasan
        $ringkasan = [
            'total' => $uploads->count(),
            'sudah' => $uploads->where('is_uploaded', 1)->count(),
            'belum' => $belumUpload->count(),
        ];

        return view('dashboard.pegawai_dashboard', [
            'uploads' => $uploads,
            'belumUpload' => $belumUpload,
            'ringkasan' => $ringkasan
        ]);
    }
}
