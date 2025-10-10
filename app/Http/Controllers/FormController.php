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
            }])
            ->orderByDesc('tahun') // urutkan berdasarkan kolom tahun di tabel jenis_dokumen
            ->get();

        // Ambil hanya pegawai (exclude Admin & Supervisor)
        $pegawaiList = User::select('id', 'name', 'email', 'nip')
            ->where('role', 'Pegawai')
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

    /**
     * Tampilkan form edit jenis dokumen
     */
    public function edit($id)
    {
        $jenisDokumen = JenisDokumen::with('mandatoryUploads')->findOrFail($id);
        $pegawaiList = User::where('role', 'Pegawai')->get();

        // Ambil pegawai yang sudah dicentang di pivot table
        $selectedPegawaiIds = $jenisDokumen->mandatoryUploads->pluck('id')->toArray();

        return view('pages.jenis-dokumen.edit-form', [
            'jenisDokumen' => $jenisDokumen,
            'pegawaiList' => $pegawaiList,
            'selectedPegawaiIds' => $selectedPegawaiIds,
            'edit' => true,
        ]);
    }

    /**
     * Update data jenis dokumen
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_dokumen' => 'required|string|max:255',
            'pegawai_ids'  => 'required|array|min:1',
        ]);

        DB::beginTransaction();
        try {
            $jenisDokumen = JenisDokumen::findOrFail($id);

            // Update nama dokumen
            $jenisDokumen->update([
                'nama_dokumen' => $request->nama_dokumen,
            ]);

            // Ambil semua periode terkait dokumen ini
            $periodeIds = $jenisDokumen->periode->pluck('id')->toArray();

            // Ambil semua pegawai sebelumnya di pivot table
            $existingPegawai = DB::table('mandatory_uploads')
                ->where('jenis_dokumen_id', $id)
                ->pluck('user_id', 'periode_id'); // key = periode_id, value = user_id

            $newPegawaiIds = $request->pegawai_ids;

            foreach ($periodeIds as $periodeId) {
                // Hapus baris yang tidak ada di newPegawaiIds
                DB::table('mandatory_uploads')
                    ->where('jenis_dokumen_id', $id)
                    ->where('periode_id', $periodeId)
                    ->whereNotIn('user_id', $newPegawaiIds)
                    ->delete();

                // Tambah baris baru jika belum ada
                foreach ($newPegawaiIds as $userId) {
                    $existing = DB::table('mandatory_uploads')
                        ->where('jenis_dokumen_id', $id)
                        ->where('periode_id', $periodeId)
                        ->where('user_id', $userId)
                        ->first();

                    if (!$existing) {
                        // hanya buat baru kalau belum ada
                        DB::table('mandatory_uploads')->insert([
                            'jenis_dokumen_id' => $id,
                            'periode_id' => $periodeId,
                            'user_id' => $userId,
                            'is_uploaded' => 0,
                        ]);
                    }
                }
            }

            DB::commit();
            return redirect()->route('form.index')->with('success', 'Data berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui data: ' . $e->getMessage());
        }
    }

    /**
     * Endpoint JSON untuk AJAX (fetch data edit)
     */
    public function getJenisDokumenJson($id)
    {
        $jenisDokumen = JenisDokumen::with('mandatoryUploads')->findOrFail($id);

        return response()->json([
            'id' => $jenisDokumen->id,
            'nama_dokumen' => $jenisDokumen->nama_dokumen,
            'periode_tipe' => $jenisDokumen->periode_tipe,
            'tahun' => $jenisDokumen->tahun,
            'pegawai_ids' => $jenisDokumen->mandatoryUploads->pluck('id')->toArray(),
        ]);
    }
}
