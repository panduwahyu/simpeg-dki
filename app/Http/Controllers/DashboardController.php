<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {   
        $user = Auth::user();

        if ($user->role == 'Pegawai') {
            return redirect()->route('pegawai-dashboard');
        }

        // jumlah user bulan ini
        $currentMonth = User::whereMonth('created_at', Carbon::now()->month)
                            ->whereYear('created_at', Carbon::now()->year)
                            ->count();

        // jumlah user bulan lalu
        $lastMonth = User::whereMonth('created_at', Carbon::now()->subMonth()->month)
                        ->whereYear('created_at', Carbon::now()->subMonth()->year)
                        ->count();

        // hitung persentase perubahan
        $growth = 0;
        if ($lastMonth > 0) {
            $growth = (($currentMonth - $lastMonth) / $lastMonth) * 100;
        }

        // total user keseluruhan
        $totalUsers = User::count();

        // Ambil semua dokumen & periode
        $dokumenList = DB::table('jenis_dokumen')->get();
        $periodeList = DB::table('periode')->get();

        // Default filter: dokumen pertama, periode pertama
        $dokumenId = null;
        $periodeId = null;

        // Ambil data progres awal
        $progressSummary = $this->getProgressSummary($dokumenId, $periodeId);
        $pegawaiData     = $this->getPegawaiData($dokumenId, $periodeId);
        $summaryData     = $this->getSummaryData($dokumenId, $periodeId);

        // 🔹 return view sekali saja dengan semua variabel
        return view('dashboard.index', compact(
            'totalUsers',
            'growth',
            'dokumenList',
            'periodeList',
            'progressSummary',
            'pegawaiData',
            'summaryData'
        ));
    }


    public function filter(Request $request)
    {
        $dokumenId = $request->get('dokumen_id');
        $periodeId = $request->get('periode_id');

        $progressSummary = $this->getProgressSummary($dokumenId, $periodeId);
        $pegawaiData = $this->getPegawaiData($dokumenId, $periodeId);
        $summaryData = $this->getSummaryData($dokumenId, $periodeId);

        return response()->json([
            'progressSummary' => $progressSummary,
            'pegawaiData' => $pegawaiData,
            'summaryData' => $summaryData
        ]);
    }

    private function getProgressSummary($dokumenId, $periodeId)
    {
        // Hitung total pegawai
        $total = DB::table('users')
            ->where('role', 'pegawai')
            ->count();

        // Hitung yang sudah upload dokumen
        $done = DB::table('mandatory_uploads')
            ->join('users', 'users.id', '=', 'mandatory_uploads.user_id')
            ->whereIn('users.role', ['pegawai', 'supervisor']) // pegawai atau supervisor
            ->where('mandatory_uploads.jenis_dokumen_id', $dokumenId)
            ->where('mandatory_uploads.periode_id', $periodeId)
            ->where('mandatory_uploads.is_uploaded', 1)
            ->count();

        // Hitung persentase
        $percent = $total > 0 ? round(($done / $total) * 100, 2) : 0;

        return [
            'total' => $total,
            'done' => $done,
            'percent' => $percent
        ];
    }

    private function getPegawaiData($dokumenId, $periodeId)
    {
        return DB::table('users')
        ->whereIn('users.role', ['pegawai', 'supervisor'])
        ->leftJoin('mandatory_uploads', function ($join) use ($dokumenId, $periodeId) {
            $join->on('users.id', '=', 'mandatory_uploads.user_id')
                ->where('mandatory_uploads.jenis_dokumen_id', $dokumenId)
                ->where('mandatory_uploads.periode_id', $periodeId);
        })
        ->leftJoin('dokumen', function ($join) use ($dokumenId, $periodeId) {
            $join->on('users.id', '=', 'dokumen.user_id')
                ->where('dokumen.jenis_dokumen_id', $dokumenId)
                ->where('dokumen.periode_id', $periodeId);
        })
        ->select(
            'users.id',
            'users.name as nama',
            'users.unit_kerja as unit_kerja',
            'mandatory_uploads.is_uploaded',
            'dokumen.tanggal_unggah as tanggal_upload'
        )
        ->get()
        ->map(function ($item) {
            $item->tanggal_upload = $item->tanggal_upload
                ? \Carbon\Carbon::parse($item->tanggal_upload)->format('d/m/Y')
                : null;
            return $item;
        });

    }

    /*** RINGKASAN DOKUMEN & PERIODE SESUAI KONDISI FILTER ***/
    private function getSummaryData($dokumenId, $periodeId)
    {
        $query = DB::table('jenis_dokumen')
            ->crossJoin('periode')
            ->select(
                'jenis_dokumen.id as dokumen_id',
                'jenis_dokumen.nama_dokumen',
                'periode.id as periode_id',
                'periode.periode_key as periode'
            )
            ->orderBy('jenis_dokumen.nama_dokumen')
            ->orderBy('periode.periode_key');

        // Kondisi filter:
        // 1. Jika tidak ada filter dokumen → semua dokumen & semua periode
        // 2. Jika hanya dokumen → dokumen tsb & semua periode
        // 3. Jika dokumen + periode → hanya kombinasi itu
        if ($dokumenId) {
            $query->where('jenis_dokumen.id', $dokumenId);
        }
        if ($periodeId) {
            $query->where('periode.id', $periodeId);
        }

        $data = $query->get();

        // Hitung total & progress tiap kombinasi
        return $data->map(function ($item) {
            $totalPegawai = DB::table('users')->whereIn('users.role', ['pegawai', 'supervisor'])->count();

            $done = DB::table('mandatory_uploads')
                ->join('users', 'users.id', '=', 'mandatory_uploads.user_id')
                ->whereIn('users.role', ['pegawai', 'supervisor'])
                ->where('mandatory_uploads.jenis_dokumen_id', $item->dokumen_id)
                ->where('mandatory_uploads.periode_id', $item->periode_id)
                ->where('mandatory_uploads.is_uploaded', 1)
                ->count();

            $percent = $totalPegawai > 0 ? round(($done / $totalPegawai) * 100, 2) : 0;

            return [
                'dokumen_id'    => $item->dokumen_id,
                'nama_dokumen'  => $item->nama_dokumen,
                'periode_id'    => $item->periode_id,
                'periode'       => $item->periode,
                'done'          => $done,
                'total'         => $totalPegawai,
                'percent'       => $percent
            ];
        });
    }
}
