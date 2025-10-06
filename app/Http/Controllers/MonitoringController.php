<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        // Ambil daftar nama dokumen unik
        $dokumenList = DB::table('jenis_dokumen')
            ->select('nama_dokumen')
            ->distinct()
            ->orderBy('nama_dokumen')
            ->pluck('nama_dokumen');

        // Ambil daftar tahun dari tabel jenis_dokumen
        $tahunList = DB::table('jenis_dokumen')
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        // Ambil filter dari request
        $selectedDokumen = $request->input('nama_dokumen', $dokumenList->first());
        $selectedTahun = $request->input('tahun', now()->year);

        if (!$selectedDokumen) {
            return view('pages.monitoring', [
                'dokumenList' => $dokumenList,
                'tahunList' => $tahunList,
                'selectedDokumen' => null,
                'selectedTahun' => $selectedTahun,
                'monitoring' => [],
            ]);
        }

        // Ambil semua jenis_dokumen dengan nama & tahun terpilih
        $jenisDokumen = DB::table('jenis_dokumen')
            ->where('nama_dokumen', $selectedDokumen)
            ->where('tahun', $selectedTahun)
            ->get();

        $monitoring = $this->getMonitoringData($jenisDokumen, $selectedTahun);

        return view('pages.monitoring', compact('dokumenList', 'tahunList', 'selectedDokumen', 'selectedTahun', 'monitoring'));
    }

    private function getMonitoringData($jenisDokumen, $tahun)
    {
        $ids = $jenisDokumen->pluck('id');
        $types = $jenisDokumen->pluck('periode_tipe')->unique();

        $periodeQuery = DB::table('periode')->where('tahun', $tahun);
        $bulan = $periodeQuery->clone()->where('tipe', 'bulanan')->orderBy('bulan')->get();
        $tahunPeriode = $periodeQuery->clone()->where('tipe', 'tahunan')->get();
        $triwulan = $periodeQuery->clone()->where('tipe', 'triwulanan')->orderBy('label')->get();

        $pegawai = DB::table('users')
            ->whereIn('role', ['pegawai', 'supervisor'])
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $uploads = DB::table('mandatory_uploads')
            ->whereIn('jenis_dokumen_id', $ids)
            ->join('users', 'users.id', '=', 'mandatory_uploads.user_id')
            ->join('periode', 'periode.id', '=', 'mandatory_uploads.periode_id')
            ->select('users.id as user_id', 'periode.id as periode_id', 'mandatory_uploads.is_uploaded')
            ->get();

        $tableData = [];
        foreach ($pegawai as $p) {
            $row = ['nama' => $p->name];

            if ($types->contains('triwulanan')) {
                foreach ($triwulan as $tw) {
                    $upload = $uploads->first(fn($u) => $u->user_id == $p->id && $u->periode_id == $tw->id);
                    $row[$tw->label] = $upload ? $upload->is_uploaded : 0;
                }
            } else {
                foreach ($bulan as $b) {
                    $upload = $uploads->first(fn($u) => $u->user_id == $p->id && $u->periode_id == $b->id);
                    $row[$b->bulan] = $upload ? $upload->is_uploaded : 0;
                }

                foreach ($tahunPeriode as $t) {
                    $upload = $uploads->first(fn($u) => $u->user_id == $p->id && $u->periode_id == $t->id);
                    $row['tahun'] = $upload ? $upload->is_uploaded : 0;
                }
            }

            $tableData[] = $row;
        }

        return [
            'bulan' => $bulan,
            'tahun' => $tahunPeriode,
            'triwulan' => $types->contains('triwulanan') ? $triwulan : [],
            'tabel' => $tableData
        ];
    }
}
