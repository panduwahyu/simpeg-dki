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
        // id jenis dokumen relevan (mis. untuk nama dokumen yang dipilih & tahun yang dipilih)
        $ids = $jenisDokumen->pluck('id')->toArray();
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

        // Ambil semua mandatory_uploads untuk jenis_dokumen yang relevan
        $uploads = DB::table('mandatory_uploads')
            ->whereIn('jenis_dokumen_id', $ids)
            ->select('user_id', 'periode_id', 'jenis_dokumen_id', 'is_uploaded')
            ->get();

        // Ambil daftar user_id yang ada di mandatory_uploads
        $userIds = $uploads->pluck('user_id')->unique();

        // Ambil pegawai hanya yang ada di mandatory_uploads
        $pegawai = DB::table('users')
            ->whereIn('id', $userIds)
            ->select('id', 'name')
            ->orderBy('name')
            ->get();

        // Build map untuk lookup cepat: $uploadsMap[user_id][periode_id] = ['jenis' => ..., 'is_uploaded' => ...]
        $uploadsMap = [];
        foreach ($uploads as $u) {
            $uploadsMap[$u->user_id][$u->periode_id] = [
                'jenis' => $u->jenis_dokumen_id,
                'is_uploaded' => (int)$u->is_uploaded
            ];
        }

        $tableData = [];
        foreach ($pegawai as $p) {
            $row = ['nama' => $p->name, 'user_id' => $p->id];

            if ($types->contains('triwulanan')) {
                foreach ($triwulan as $idx => $tw) {
                    $spaceLabel = preg_replace('/\s+\d{4}$/', '', $tw->label); // fix: hapus tahun di akhir label
                    $underscoreLabel = str_replace(' ', '_', $spaceLabel);

                    $uploadEntry = $uploadsMap[$p->id][$tw->id] ?? null;
                    $isUploaded = $uploadEntry ? $uploadEntry['is_uploaded'] : 0;
                    $jenisId = $uploadEntry ? $uploadEntry['jenis'] : null;

                    $row[$spaceLabel] = $isUploaded;
                    $row[$underscoreLabel . '_periode_id'] = $tw->id;
                    $row[$underscoreLabel . '_jenis_id'] = $jenisId;
                }
            } else {
                // BULANAN
                foreach ($bulan as $idx => $b) {
                    $monthIndex = is_numeric($b->bulan) ? (int)$b->bulan : ($idx + 1);
                    $monthKey = (string)$monthIndex;

                    $uploadEntry = $uploadsMap[$p->id][$b->id] ?? null;
                    $isUploaded = $uploadEntry ? $uploadEntry['is_uploaded'] : 0;
                    $jenisId = $uploadEntry ? $uploadEntry['jenis'] : null;

                    $row[$monthKey] = $isUploaded;
                    $row[$monthKey . '_periode_id'] = $b->id;
                    $row[$monthKey . '_jenis_id'] = $jenisId;
                }
            }

            // 🧩 TAHUNAN (DIPINDAH KE LUAR IF)
            foreach ($tahunPeriode as $t) {
                $uploadEntry = $uploadsMap[$p->id][$t->id] ?? null;
                $isUploaded = $uploadEntry ? $uploadEntry['is_uploaded'] : 0;
                $jenisId = $uploadEntry ? $uploadEntry['jenis'] : null;

                $row['tahun'] = $isUploaded;
                $row['tahun_periode_id'] = $t->id;
                $row['tahun_jenis_id'] = $jenisId;
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

    // NOTE: terima param tahun via query string (opsional). Pastikan kita pakai jenis_dokumen untuk tahun yang benar.
    public function getMonitoringDataAjax(Request $request, $namaDokumen)
    {
        // jika client mengirim tahun, pakai itu; jika tidak, ambil tahun terbesar untuk nama dokumen ini
        $tahunParam = $request->input('tahun');

        if ($tahunParam) {
            $tahun = (int)$tahunParam;
        } else {
            $tahun = DB::table('jenis_dokumen')
                ->where('nama_dokumen', $namaDokumen)
                ->max('tahun') ?? now()->year;
        }

        // ambil jenis dokumen yang sesuai nama + tahun
        $jenisDokumen = DB::table('jenis_dokumen')
            ->where('nama_dokumen', $namaDokumen)
            ->where('tahun', $tahun)
            ->get();

        $periode_tipe = $jenisDokumen->first()?->periode_tipe ?? '';

        $monitoring = $this->getMonitoringData($jenisDokumen, $tahun);

        return response()->json([
            'tahun' => $tahun,
            'periode_tipe' => $periode_tipe,
            'monitoring' => $monitoring
        ]);
    }

    // Preview tetap cek mandatory_uploads (validasi wajib upload), tetapi file path diambil dari table dokumen
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
