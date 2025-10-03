<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\JenisDokumen;
use App\Models\Periode;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class FormController extends Controller
{
    public function index()
    {
        $jenisDokumen = JenisDokumen::with(['periode' => function($query) {
            $query->orderByDesc('tahun')->orderByDesc('bulan');
        }])->orderByDesc('id')->get();

        $pegawaiList = User::select('id', 'name', 'email', 'nip')->get();

        return view('pages.form', compact('jenisDokumen', 'pegawaiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'deskripsi' => 'nullable|string',
            'tahun' => 'required|integer|min:2000|max:' . date('Y'),
            'periode_tipe' => 'required|in:bulanan,triwulanan,tahunan',
            'pegawai_type' => 'required|in:all,specific',
            'pegawai_ids' => 'required|array|min:1',
            'pegawai_ids.*' => 'integer|exists:users,id'
        ], [
            'pegawai_ids.required' => 'Pilih minimal 1 pegawai.'
        ]);

        DB::transaction(function() use ($request) {

            $jenisDokumen = JenisDokumen::create([
                'nama_dokumen' => $request->nama_dokumen,
                'deskripsi' => $request->deskripsi,
                'periode_tipe' => $request->periode_tipe,
            ]);

            $tahun = $request->tahun;
            $tipe  = $request->periode_tipe;
            $pegawaiIds = $request->pegawai_ids; // Hanya yang diceklis dikirim

            if ($tipe === 'bulanan') {
                for ($bulan = 1; $bulan <= 12; $bulan++) {
                    $periodeKey = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
                    $label = Carbon::createFromDate($tahun, $bulan, 1)
                        ->locale('id')
                        ->translatedFormat('F Y');

                    $periode = Periode::firstOrCreate(
                        ['periode_key' => $periodeKey, 'tipe' => 'bulanan', 'tahun' => $tahun, 'bulan' => $bulan],
                        ['label' => $label]
                    );

                    foreach ($pegawaiIds as $userId) {
                        DB::table('mandatory_uploads')->updateOrInsert(
                            [
                                'jenis_dokumen_id' => $jenisDokumen->id,
                                'periode_id' => $periode->id,
                                'user_id' => $userId
                            ],
                            ['is_uploaded' => 0]
                        );
                    }
                }
            } elseif ($tipe === 'triwulanan') {
                for ($q = 1; $q <= 4; $q++) {
                    $periodeKey = $tahun . '-Q' . $q;
                    $label = 'Triwulan ' . $q . ' ' . $tahun;

                    $periode = Periode::firstOrCreate(
                        ['periode_key' => $periodeKey, 'tipe' => 'triwulanan', 'tahun' => $tahun, 'triwulan' => $q],
                        ['label' => $label]
                    );

                    foreach ($pegawaiIds as $userId) {
                        DB::table('mandatory_uploads')->updateOrInsert(
                            [
                                'jenis_dokumen_id' => $jenisDokumen->id,
                                'periode_id' => $periode->id,
                                'user_id' => $userId
                            ],
                            ['is_uploaded' => 0]
                        );
                    }
                }
            } elseif ($tipe === 'tahunan') {
                $periodeKey = $tahun;
                $label = 'Tahun ' . $tahun;

                $periode = Periode::firstOrCreate(
                    ['periode_key' => $periodeKey, 'tipe' => 'tahunan', 'tahun' => $tahun],
                    ['label' => $label]
                );

                foreach ($pegawaiIds as $userId) {
                    DB::table('mandatory_uploads')->updateOrInsert(
                        [
                            'jenis_dokumen_id' => $jenisDokumen->id,
                            'periode_id' => $periode->id,
                            'user_id' => $userId
                        ],
                        ['is_uploaded' => 0]
                    );
                }
            }
        });

        return redirect()->route('form.index')->with('success', 'Dokumen berhasil dibuat dan terhubung ke periode & pegawai!');
    }

    public function destroy(JenisDokumen $jenisDokumen)
    {
        DB::transaction(function() use ($jenisDokumen) {
            DB::table('mandatory_uploads')->where('jenis_dokumen_id', $jenisDokumen->id)->delete();
            $jenisDokumen->delete();
        });

        return redirect()->route('form.index')->with('success', 'Dokumen berhasil dihapus!');
    }
}
