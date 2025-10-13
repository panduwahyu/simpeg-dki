<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class PegawaiController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Ambil data untuk filter dropdown
        $periodeOptions = DB::table('periode')->orderBy('periode_key', 'desc')->get();
        $jenisDokumenOptions = DB::table('jenis_dokumen')->orderBy('nama_dokumen')->get();

        // Query dasar
        $query = DB::table('mandatory_uploads')
            ->join('jenis_dokumen', 'mandatory_uploads.jenis_dokumen_id', '=', 'jenis_dokumen.id')
            ->join('periode', 'mandatory_uploads.periode_id', '=', 'periode.id')
            ->leftJoin('dokumen', function ($join) use ($userId) {
                $join->on('dokumen.jenis_dokumen_id', '=', 'mandatory_uploads.jenis_dokumen_id')
                     ->on('dokumen.periode_id', '=', 'mandatory_uploads.periode_id')
                     ->where('dokumen.user_id', '=', $userId);
            })
            ->where('mandatory_uploads.user_id', $userId)
            ->select(
                'jenis_dokumen.id as jenis_dokumen_id',
                'jenis_dokumen.nama_dokumen',
                'periode.id as periode_id',
                'periode.periode_key',
                'mandatory_uploads.is_uploaded',
                'mandatory_uploads.penilaian', // <-- PERUBAHAN 1: Tambahkan kolom penilaian
                'dokumen.tanggal_unggah as tanggal_upload',
                'dokumen.id as dokumen_id',
                'dokumen.path'
            );

        // Filter by Periode
        $query->when($request->filled('periode_id'), function ($q) use ($request) {
            return $q->where('periode.id', $request->periode_id);
        });

        // Filter by Jenis Dokumen
        $query->when($request->filled('jenis_dokumen_id'), function ($q) use ($request) {
            return $q->where('jenis_dokumen.id', $request->jenis_dokumen_id);
        });

        // <--  logika filter status
        $query->when($request->filled('status'), function ($q) use ($request) {
            switch ($request->status) {
                case 'belum': // Belum Upload
                    return $q->where('mandatory_uploads.is_uploaded', 0);
                case 'menunggu': // Menunggu Persetujuan
                    return $q->where('mandatory_uploads.is_uploaded', 1)
                             ->where('mandatory_uploads.penilaian', 0);
                case 'selesai': // Selesai
                    return $q->where('mandatory_uploads.is_uploaded', 1)
                             ->where('mandatory_uploads.penilaian', 1);
            }
        });

        // Fitur Pencarian
        $query->when($request->filled('search'), function ($q) use ($request) {
            return $q->where('jenis_dokumen.nama_dokumen', 'like', '%' . $request->search . '%');
        });

        // Ambil data sebelum paginasi untuk ringkasan
        $allUploadsForSummary = $query->clone()->get(); // Gunakan clone() agar tidak mempengaruhi query paginasi

        // <-- logika ringkasan
        $total = $allUploadsForSummary->count();
        $selesai = $allUploadsForSummary->where('is_uploaded', 1)->count();
        $ringkasan = [
            'total' => $total,
            'sudah' => $selesai, // 'sudah' sekarang berarti sudah selesai (dinilai)
            'belum' => $total - $selesai,
        ];
        
        // Paginasi
        $perPage = $request->input('per_page', 10);
        $uploads = $query->orderBy('periode.periode_key', 'desc')
                          ->orderBy('jenis_dokumen.nama_dokumen')
                          ->paginate($perPage)
                          ->withQueryString();

        return view('dashboard.pegawai_dashboard', [
            'uploads' => $uploads,
            'ringkasan' => $ringkasan,
            'periodeOptions' => $periodeOptions,
            'jenisDokumenOptions' => $jenisDokumenOptions,
        ]);
    }
}
