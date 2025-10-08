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
        // Ambil semua dokumen beserta periode-nya, urut berdasarkan tahun/bulan desc
        $jenisDokumen = JenisDokumen::with(['periode' => function($query) {
            $query->orderByDesc('tahun')->orderByDesc('bulan');
        }])->get();

        // Ambil hanya pegawai (exclude Admin & Supervisor)
        $pegawaiList = User::select('id', 'name', 'email', 'nip')
            ->where('role', 'Pegawai') // hanya Pegawai
            ->get();

        return view('pages.form', compact('jenisDokumen', 'pegawaiList'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2000',
            'periode_tipe' => 'required|in:bulanan,triwulanan,tahunan',
            'pegawai_type' => 'required|in:all,specific',
            'pegawai_ids' => 'required|array|min:1',
            'pegawai_ids.*' => 'integer|exists:users,id'
        ], [
            'pegawai_ids.required' => 'Pilih minimal 1 pegawai.'
        ]);

        DB::transaction(function() use ($request) {

            // Buat jenis dokumen baru
            $jenisDokumen = JenisDokumen::create([
                'nama_dokumen' => $request->nama_dokumen,
                'periode_tipe' => $request->periode_tipe,
                'tahun' => $request->tahun,
            ]);

            $tahun = $request->tahun;
            $tipe  = $request->periode_tipe;
            $pegawaiIds = $request->pegawai_ids;

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

        return redirect()->route('form.index')->with('success', 'Dokumen berhasil dibuat!');
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'nama_dokumen' => 'required|string',
            'periode_tipe' => 'required|string',
            'tahun' => 'required|integer',
        ]);

        $deleted = JenisDokumen::where('nama_dokumen', $request->nama_dokumen)
            ->where('periode_tipe', $request->periode_tipe)
            ->where('tahun', $request->tahun)
            ->delete();

        return redirect()->route('form.index')
                        ->with('success', "$deleted dokumen berhasil dihapus!");
    }

    // Tampilkan form edit
    public function edit($id)
    {
        $jenisDokumen = JenisDokumen::with('mandatoryUploads')->findOrFail($id);
        
        // Ambil ID pegawai yang sudah di-checklist
        $selectedPegawaiIds = $jenisDokumen->mandatoryUploads->pluck('user_id')->toArray();

        // Daftar semua pegawai
        $pegawaiList = User::all();

        return view('form.edit', compact('jenisDokumen', 'pegawaiList', 'selectedPegawaiIds'));
    }

    // Update jenis dokumen + pivot table
    public function update(Request $request, JenisDokumen $jenisDokumen)
    {
        $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'tahun' => 'required|integer|min:2000',
            'periode_tipe' => 'required|in:bulanan,triwulanan,tahunan',
            'pegawai_ids' => 'required|array|min:1',
            'pegawai_ids.*' => 'integer|exists:users,id'
        ], [
            'pegawai_ids.required' => 'Pilih minimal 1 pegawai.'
        ]);

        DB::transaction(function() use ($request, $jenisDokumen) {
            // Update kolom di tabel jenis_dokumen
            $jenisDokumen->update([
                'nama_dokumen' => $request->nama_dokumen,
                'tahun' => $request->tahun,
                'periode_tipe' => $request->periode_tipe,
            ]);

            $newPegawaiIds = $request->pegawai_ids;

            // Ambil user_id lama dari pivot
            $oldPegawaiIds = DB::table('mandatory_uploads')
                ->where('jenis_dokumen_id', $jenisDokumen->id)
                ->pluck('user_id')
                ->toArray();

            // Hapus yang sudah tidak dicentang
            $toDelete = array_diff($oldPegawaiIds, $newPegawaiIds);
            if (!empty($toDelete)) {
                DB::table('mandatory_uploads')
                    ->where('jenis_dokumen_id', $jenisDokumen->id)
                    ->whereIn('user_id', $toDelete)
                    ->delete();
            }

            // Tambahkan yang baru dicentang tapi belum ada
            $toInsert = array_diff($newPegawaiIds, $oldPegawaiIds);
            foreach ($toInsert as $userId) {
                DB::table('mandatory_uploads')->insert([
                    'jenis_dokumen_id' => $jenisDokumen->id,
                    'user_id' => $userId,
                    'is_uploaded' => 0
                ]);
            }
        });

        return redirect()->route('form.index')->with('success', 'Dokumen berhasil diperbarui!');
    }
}
