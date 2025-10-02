<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisDokumen;
use App\Models\Periode;

class FormController extends Controller
{
    public function index()
    {
        $jenisDokumen = JenisDokumen::all();
        return view('pages.form', compact('jenisDokumen'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
            'tahun' => 'required|integer',
            'periode_tipe' => 'required|in:bulanan,tahunan,triwulanan',
        ]);

        $tahun = $request->tahun;
        $tipe  = $request->periode_tipe;

        if ($tipe === 'bulanan') {
            for ($bulan = 1; $bulan <= 12; $bulan++) {
                Periode::create([
                    'periode_key' => $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT),
                    'tahun' => $tahun,
                    'bulan' => $bulan,
                    'tipe' => $tipe,
                    'label' => date('F', mktime(0, 0, 0, $bulan, 1)) . ' ' . $tahun,
                ]);
            }
        } elseif ($tipe === 'triwulanan') {
            for ($q = 1; $q <= 4; $q++) {
                Periode::create([
                    'periode_key' => $tahun . '-Q' . $q,
                    'tahun' => $tahun,
                    'triwulan' => $q,
                    'tipe' => $tipe,
                    'label' => 'Triwulan ' . $q . ' ' . $tahun,
                ]);
            }
        } elseif ($tipe === 'tahunan') {
            Periode::create([
                'periode_key' => $tahun,
                'tahun' => $tahun,
                'tipe' => $tipe,
                'label' => 'Tahun ' . $tahun,
            ]);
        }

        return redirect()->route('form.index')->with('success', 'Periode berhasil dibuat!');
    }
}
