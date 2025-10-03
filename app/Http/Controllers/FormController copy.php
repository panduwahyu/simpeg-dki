<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisDokumen;
use App\Models\Periode;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function index()
    {
        return view('pages.form');
    }

    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'periode_tipe' => 'required|in:bulanan,triwulanan,tahunan',
        ]);

        DB::transaction(function() use ($request) {

            // 1. Buat JenisDokumen baru
            $jenisDokumen = JenisDokumen::create([
                'nama_dokumen' => $request->nama_dokumen,
                'deskripsi' => $request->deskripsi,
                'periode_tipe' => $request->periode_tipe, // diambil dari input Tipe Periode
            ]);

            $tahun = $request->tahun;
            $tipe  = $request->periode_tipe;

            // 2. Buat periode terkait
            if ($tipe === 'bulanan') {
                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    $label = Carbon::createFromDate($tahun, $bulan, 1)
                        ->locale('id')
                        ->translatedFormat('F Y');

                    Periode::create([
                        'jenis_dokumen_id' => $jenisDokumen->id,
                        'tahun' => $tahun,
                        'bulan' => $bulan,
                        'tipe' => $tipe,
                        'label' => $label,
                        'periode_key' => $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT),
                    ]);
                }
            } elseif ($tipe === 'triwulanan') {
                for ($q = 1; $q <= 4; $q++) {
                    Periode::create([
                        'jenis_dokumen_id' => $jenisDokumen->id,
                        'tahun' => $tahun,
                        'triwulan' => $q,
                        'tipe' => $tipe,
                        'label' => 'Triwulan ' . $q . ' ' . $tahun,
                        'periode_key' => $tahun . '-Q' . $q,
                    ]);
                }
            } elseif ($tipe === 'tahunan') {
                Periode::create([
                    'jenis_dokumen_id' => $jenisDokumen->id,
                    'tahun' => $tahun,
                    'tipe' => $tipe,
                    'label' => 'Tahun ' . $tahun,
                    'periode_key' => $tahun,
                ]);
            }
        });

        return redirect()->route('form.index')->with('success', 'Dokumen & Periode berhasil dibuat!');
    }
}
