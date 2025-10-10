<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MonitoringController extends Controller
{
    public function index(Request $request)
    {
        $dokumenList = DB::table('jenis_dokumen')
            ->select('nama_dokumen')
            ->distinct()
            ->orderBy('nama_dokumen')
            ->pluck('nama_dokumen');

        $tahunList = DB::table('jenis_dokumen')
            ->select('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        $selectedDokumen = $request->input('nama_dokumen');
        $monitoring = null;
        $selectedTahun = null;
        $periodeList = collect();
        $selectedPeriode = null;

        if ($selectedDokumen) {
            $selectedTahun = $request->input('tahun', now()->year);

            $jenisDokumen = DB::table('jenis_dokumen')
                ->where('nama_dokumen', $selectedDokumen)
                ->where('tahun', $selectedTahun)
                ->get();

            $monitoring = $this->getMonitoringData($jenisDokumen, $selectedTahun);

            $periodeList = $jenisDokumen->pluck('periode_tipe')->unique();
            $selectedPeriode = $request->input('periode', $periodeList->first());
        }

        return view('pages.monitoring', compact(
            'dokumenList',
            'tahunList',
            'selectedDokumen',
            'selectedTahun',
            'monitoring',
            'periodeList',
            'selectedPeriode'
        ));
    }

    private function getMonitoringData($jenisDokumen, $tahun)
    {
        $ids = $jenisDokumen->pluck('id')->toArray();
        $types = $jenisDokumen->pluck('periode_tipe')->unique();

        $periodeQuery = DB::table('periode')->where('tahun', $tahun);
        $bulan = $periodeQuery->clone()->where('tipe', 'bulanan')->orderBy('bulan')->get();
        $tahunPeriode = $periodeQuery->clone()->where('tipe', 'tahunan')->get();
        $triwulan = $periodeQuery->clone()->where('tipe', 'triwulanan')->orderBy('label')->get();

        $uploads = DB::table('mandatory_uploads')
            ->whereIn('jenis_dokumen_id', $ids)
            ->select('user_id', 'periode_id', 'jenis_dokumen_id', 'is_uploaded', 'penilaian')
            ->get();

        $userIds = $uploads->pluck('user_id')->unique();

        $pegawai = DB::table('users')
            ->whereIn('id', $userIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        $uploadsMap = [];
        foreach ($uploads as $u) {
            $uploadsMap[$u->user_id][$u->periode_id] = [
                'jenis' => $u->jenis_dokumen_id,
                'is_uploaded' => (int)$u->is_uploaded,
                'penilaian' => (int)($u->penilaian ?? 0)
            ];
        }

        $tableData = [];
        foreach ($pegawai as $p) {
            $row = ['nama' => $p->name, 'user_id' => $p->id];

            // TRIWULAN
            if ($types->contains('triwulanan')) {
                foreach ($triwulan as $tw) {
                    $spaceLabel = preg_replace('/\s+\d{4}$/', '', $tw->label);
                    $underscoreLabel = str_replace(' ', '_', $spaceLabel);
                    $uploadEntry = $uploadsMap[$p->id][$tw->id] ?? null;
                    $row[$spaceLabel] = $uploadEntry ? $uploadEntry['is_uploaded'] : 0;
                    $row[$underscoreLabel . '_penilaian'] = $uploadEntry ? $uploadEntry['penilaian'] : 0;
                    $row[$underscoreLabel . '_periode_id'] = $tw->id;
                    $row[$underscoreLabel . '_jenis_id'] = $uploadEntry ? $uploadEntry['jenis'] : null;
                }
            }

            // BULANAN
            if ($types->contains('bulanan')) {
                foreach ($bulan as $idx => $b) {
                    $monthIndex = is_numeric($b->bulan) ? (int)$b->bulan : ($idx + 1);
                    $monthKey = (string)$monthIndex;
                    $uploadEntry = $uploadsMap[$p->id][$b->id] ?? null;
                    $row[$monthKey] = $uploadEntry ? $uploadEntry['is_uploaded'] : 0;
                    $row[$monthKey . '_penilaian'] = $uploadEntry ? $uploadEntry['penilaian'] : 0;
                    $row[$monthKey . '_periode_id'] = $b->id;
                    $row[$monthKey . '_jenis_id'] = $uploadEntry ? $uploadEntry['jenis'] : null;
                }
            }

            // TAHUNAN
            foreach ($tahunPeriode as $t) {
                $uploadEntry = $uploadsMap[$p->id][$t->id] ?? null;
                $row['tahun'] = $uploadEntry ? $uploadEntry['is_uploaded'] : 0;
                $row['tahun_penilaian'] = $uploadEntry ? $uploadEntry['penilaian'] : 0;
                $row['tahun_periode_id'] = $t->id;
                $row['tahun_jenis_id'] = $uploadEntry ? $uploadEntry['jenis'] : null;
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

    public function getMonitoringDataAjax(Request $request, $namaDokumen)
    {
        $jenisDokumen = DB::table('jenis_dokumen')
            ->where('nama_dokumen', $namaDokumen)
            ->get();

        $tahun = $jenisDokumen->first()?->tahun ?? '';
        $periode_tipe = $jenisDokumen->first()?->periode_tipe ?? '';

        $monitoring = $this->getMonitoringData($jenisDokumen, $tahun);

        // ===== Hitung progress bar =====
        $rows = $monitoring['tabel'];

        $totalCells = 0;
        $uploadedCells = 0;
        $signedCells = 0;

        foreach ($rows as $row) {
            foreach ($row as $key => $val) {
                if (in_array($key, ['nama','user_id'])) continue;

                $totalCells++;
                if ($val == 1) { // ceklis atau tanda seru
                    $uploadedCells++;

                    // cek penilaian untuk ceklis ✅
                    $penilaianKey = str_replace(' ','_', $key) . '_penilaian';
                    if(isset($row[$penilaianKey]) && $row[$penilaianKey] == 1){
                        $signedCells++;
                    }
                }
            }
        }

        // Keterangan "X dari Y dokumen"
        $progressUploadedText = "$uploadedCells dari $totalCells dokumen";
        $progressSignedText = $uploadedCells > 0 ? "$signedCells dari $uploadedCells dokumen" : "0 dari 0 dokumen";

        return response()->json([
            'tahun' => $tahun,
            'periode_tipe' => $periode_tipe,
            'monitoring' => $monitoring,
            'progressUploadedText' => $progressUploadedText,
            'progressSignedText' => $progressSignedText
        ]);
    }

    public function previewFile($userId, $jenisDokumenId, $periodeId)
    {
        $upload = DB::table('mandatory_uploads')
            ->where('user_id', $userId)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('periode_id', $periodeId)
            ->first();

        if (!$upload) {
            abort(404, 'File tidak ditemukan atau tidak wajib upload');
        }

        $dokumen = DB::table('dokumen')
            ->where('user_id', $userId)
            ->where('jenis_dokumen_id', $jenisDokumenId)
            ->where('periode_id', $periodeId)
            ->first();

        if (!$dokumen || !$dokumen->path) {
            abort(404, 'File tidak ditemukan di tabel dokumen');
        }

        $filePath = storage_path('app/private/' . $dokumen->path);
        if (!file_exists($filePath)) {
            abort(404, 'File tidak ditemukan di storage');
        }

        $mime = mime_content_type($filePath);
        $file = file_get_contents($filePath);

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . basename($filePath) . '"');
    }
}
