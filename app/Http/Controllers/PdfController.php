<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Exception;

class PdfController extends Controller
{
    // =======================
    // Halaman Pegawai
    // =======================
    public function index()
    {
        $userId = Auth::id();
        $belumUpload = $this->getDokumenBelum();

        return view('pdf.sign', [
            'id_user' => $userId,
            'belumUpload' => $belumUpload
        ]);
    }

    private function getDokumenBelum()
    {
        $userId = Auth::id();

        return DB::table('mandatory_uploads')
            ->join('jenis_dokumen', 'jenis_dokumen.id', '=', 'mandatory_uploads.jenis_dokumen_id')
            ->join('periode', 'periode.id', '=', 'mandatory_uploads.periode_id')
            ->where('mandatory_uploads.user_id', $userId)
            ->where('mandatory_uploads.is_uploaded', 0)
            ->select(
                'jenis_dokumen.id as jenis_dokumen_id',
                'jenis_dokumen.nama_dokumen',
                'periode.id as periode_id',
                'periode.periode_key'
            )
            ->distinct()
            ->orderBy('periode.periode_key')
            ->orderBy('jenis_dokumen.nama_dokumen')
            ->get();
    }

    public function signPdf(Request $request)
    {
        $request->validate([
            'user_id'            => 'required|exists:users,id',
            'jenis_dokumen_id'   => 'required|exists:jenis_dokumen,id',
            'periode_id'         => 'required|exists:periode,id',
            'pdf'                => 'required|file|mimes:pdf|max:10240',
            'signatures'         => 'required|array|min:1',
            'signatures.*.page'  => 'required|integer|min:1',
            'signatures.*.x'     => 'required|numeric|min:0|max:1',
            'signatures.*.y'     => 'required|numeric|min:0|max:1',
            'signatures.*.w'     => 'required|numeric|min:0|max:1',
            'files'              => 'required|array|min:1',
            'files.*'            => 'required|file|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        $mandatory = DB::table('mandatory_uploads')
            ->where('user_id', $request->user_id)
            ->where('jenis_dokumen_id', $request->jenis_dokumen_id)
            ->where('periode_id', $request->periode_id)
            ->first();

        if (!$mandatory) {
            return back()->withErrors(['msg' => 'Mandatory upload tidak ditemukan.']);
        }

        // Simpan PDF sementara
        $pdfFile = $request->file('pdf');
        $pdfName = 'orig_' . time() . '_' . Str::random(6) . '.' . $pdfFile->getClientOriginalExtension();
        $pdfStoredPath = $pdfFile->storeAs('public/tmp', $pdfName);
        $pdfFullPath = Storage::path($pdfStoredPath);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfFullPath);

        // Simpan file signature sementara
        $sigStoredPaths = [];
        $sigTempPaths = [];
        foreach ($request->file('files', []) as $i => $sigFile) {
            $sigName = 'sig_' . time() . '_' . Str::random(6) . '.' . $sigFile->getClientOriginalExtension();
            $sigStored = $sigFile->storeAs('public/tmp', $sigName);
            $sigStoredPaths[$i] = $sigStored;
            $sigTempPaths[$i] = Storage::path($sigStored);
        }

        $signatures = $request->input('signatures', []);

        try {
            for ($p = 1; $p <= $pageCount; $p++) {
                $tplId = $pdf->importPage($p);
                $size = $pdf->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);

                foreach ($signatures as $idx => $sig) {
                    if ((int)$sig['page'] === $p && isset($sigTempPaths[$idx])) {
                        $x = floatval($sig['x']) * $size['width'];
                        $y = floatval($sig['y']) * $size['height'];
                        $w = floatval($sig['w']) * $size['width'];
                        $pdf->Image($sigTempPaths[$idx], $x, $y, $w);
                    }
                }
            }

            $jenisDokumen = DB::table('jenis_dokumen')->where('id', $request->jenis_dokumen_id)->first();
            $periode = DB::table('periode')->where('id', $request->periode_id)->first();

            $outName = 'Ditandatangani_' . $jenisDokumen->nama_dokumen . '_' . $periode->periode_key . '.pdf';
            $outStored = 'uploads/' . $outName;
            $outFullPath = Storage::path($outStored);

            $pdf->Output($outFullPath, 'F');

            DB::transaction(function () use ($mandatory, $outStored) {
                DB::table('mandatory_uploads')
                    ->where('id', $mandatory->id)
                    ->update(['is_uploaded' => 1, 'updated_at' => now()]);

                DB::table('dokumen')->insert([
                    'path' => $outStored,
                    'user_id' => $mandatory->user_id,
                    'jenis_dokumen_id' => $mandatory->jenis_dokumen_id,
                    'periode_id' => $mandatory->periode_id,
                    'tanggal_unggah' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            });

            Storage::delete($pdfStoredPath);
            if (!empty($sigStoredPaths)) Storage::delete($sigStoredPaths);

            return response()->download($outFullPath, $outName)->deleteFileAfterSend(false);

        } catch (Exception $e) {
            Storage::delete($pdfStoredPath);
            if (!empty($sigStoredPaths)) Storage::delete($sigStoredPaths);
            if (isset($outFullPath) && file_exists($outFullPath)) @unlink($outFullPath);
            Log::error("Error PDF pegawai: " . $e->getMessage());
            throw $e;
        }
    }

    // =======================
    // AJAX: Dokumen untuk Pegawai (unik)
    // =======================
    public function getDokumenPegawai($userId)
    {
        $dokumen = DB::table('mandatory_uploads')
            ->join('jenis_dokumen', 'mandatory_uploads.jenis_dokumen_id', '=', 'jenis_dokumen.id')
            ->where('mandatory_uploads.user_id', $userId)
            ->where('mandatory_uploads.is_uploaded', 0)
            ->select('jenis_dokumen.id as jenis_dokumen_id', 'jenis_dokumen.nama_dokumen')
            ->groupBy('jenis_dokumen.id', 'jenis_dokumen.nama_dokumen') // <-- pastikan unik
            ->get();

        return response()->json($dokumen);
    }


    // =======================
    // AJAX: Periode untuk Pegawai (sudah ada)
    // =======================
    public function getPeriode($userId, $dokumenId)
    {
        $periode = DB::table('periode')
            ->join('mandatory_uploads', 'periode.id', '=', 'mandatory_uploads.periode_id')
            ->where('mandatory_uploads.user_id', $userId)
            ->where('mandatory_uploads.jenis_dokumen_id', $dokumenId)
            ->where('mandatory_uploads.is_uploaded', 0)
            ->select('periode.id', 'periode.periode_key')
            ->distinct()
            ->get();

        return response()->json($periode);
    }


    

    // =======================
    // Halaman Supervisor/Admin
    // =======================
    public function indexSupervisorAdmin()
    {
        $semuaDokumen = DB::table('mandatory_uploads')
            ->join('jenis_dokumen', 'jenis_dokumen.id', '=', 'mandatory_uploads.jenis_dokumen_id')
            ->join('periode', 'periode.id', '=', 'mandatory_uploads.periode_id')
            ->join('users', 'users.id', '=', 'mandatory_uploads.user_id')
            ->where('mandatory_uploads.penilaian', 0)
            ->select(
                'mandatory_uploads.id as mandatory_id',
                'mandatory_uploads.penilaian',
                'users.id as user_id',
                'jenis_dokumen.id as jenis_dokumen_id',
                'periode.id as periode_id',
                'jenis_dokumen.nama_dokumen',
                'periode.periode_key',
                'users.name as nama_pegawai',
                'users.email as email_pegawai'
            )
            ->orderBy('periode.periode_key')
            ->orderBy('jenis_dokumen.nama_dokumen')
            ->get();

        return view('pdf.sign-supervisoradmin', [
            'semuaDokumen' => $semuaDokumen
        ]);
    }

    public function signPdfSupervisor(Request $request)
    {
        $userLogin = Auth::user();

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
            'periode_id' => 'required|exists:periode,id',
            'pdf' => 'required|file|mimes:pdf|max:10240',
            'signatures' => 'required|array|min:1',
            'signatures.*.page' => 'required|integer|min:1',
            'signatures.*.x' => 'required|numeric|min:0|max:1',
            'signatures.*.y' => 'required|numeric|min:0|max:1',
            'signatures.*.w' => 'required|numeric|min:0|max:1',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        $mandatory = DB::table('mandatory_uploads')
            ->where('user_id', $request->user_id)
            ->where('jenis_dokumen_id', $request->jenis_dokumen_id)
            ->where('periode_id', $request->periode_id)
            ->where('penilaian', 0)
            ->first();

        if (!$mandatory) return back()->withErrors(['msg' => 'Mandatory upload tidak ditemukan.']);

        // Simpan PDF sementara
        $pdfFile = $request->file('pdf');
        $pdfName = 'orig_' . time() . '_' . Str::random(6) . '.' . $pdfFile->getClientOriginalExtension();
        $pdfStoredPath = $pdfFile->storeAs('public/tmp', $pdfName);
        $pdfFullPath = Storage::path($pdfStoredPath);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfFullPath);

        $sigStoredPaths = [];
        $sigTempPaths = [];
        foreach ($request->file('files', []) as $i => $sigFile) {
            $sigName = 'sig_' . time() . '_' . Str::random(6) . '.' . $sigFile->getClientOriginalExtension();
            $sigStored = $sigFile->storeAs('public/tmp', $sigName);
            $sigStoredPaths[$i] = $sigStored;
            $sigTempPaths[$i] = Storage::path($sigStored);
        }

        $signatures = $request->input('signatures', []);

        try {
            for ($p = 1; $p <= $pageCount; $p++) {
                $tplId = $pdf->importPage($p);
                $size = $pdf->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);

                foreach ($signatures as $idx => $sig) {
                    if ((int)$sig['page'] === $p && isset($sigTempPaths[$idx])) {
                        $x = floatval($sig['x']) * $size['width'];
                        $y = floatval($sig['y']) * $size['height'];
                        $w = floatval($sig['w']) * $size['width'];
                        $pdf->Image($sigTempPaths[$idx], $x, $y, $w);
                    }
                }
            }

            $pegawai = DB::table('users')->where('id', $request->user_id)->first();
            $jenisDokumen = DB::table('jenis_dokumen')->where('id', $request->jenis_dokumen_id)->first();
            $periode = DB::table('periode')->where('id', $request->periode_id)->first();

            $outName = 'Disetujui_' . $pegawai->name . '_' . $jenisDokumen->nama_dokumen . '_' . $periode->periode_key . '.pdf';
            $outStored = 'uploads/' . $outName;
            $outFullPath = Storage::path($outStored);

            $pdf->Output($outFullPath, 'F');

            DB::transaction(function () use ($mandatory, $outStored, $userLogin) {
                // Update mandatory_uploads
                DB::table('mandatory_uploads')
                    ->where('id', $mandatory->id)
                    ->update(['penilaian' => 1, 'is_uploaded' => 1, 'updated_at' => now()]);

                // Insert/update dokumen
                $existing = DB::table('dokumen')
                    ->where('user_id', $mandatory->user_id)
                    ->where('jenis_dokumen_id', $mandatory->jenis_dokumen_id)
                    ->where('periode_id', $mandatory->periode_id)
                    ->first();

                if ($existing) {
                    if (Storage::exists($existing->path)) Storage::delete($existing->path);
                    DB::table('dokumen')->where('id', $existing->id)->update([
                        'path' => $outStored,
                        'penilai_id' => $userLogin->id,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('dokumen')->insert([
                        'path' => $outStored,
                        'penilai_id' => $userLogin->id,
                        'user_id' => $mandatory->user_id,
                        'jenis_dokumen_id' => $mandatory->jenis_dokumen_id,
                        'periode_id' => $mandatory->periode_id,
                        'tanggal_unggah' => now(),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            });

            Storage::delete($pdfStoredPath);
            if (!empty($sigStoredPaths)) Storage::delete($sigStoredPaths);

            return response()->download($outFullPath, $outName)->deleteFileAfterSend(false);

        } catch (Exception $e) {
            Storage::delete($pdfStoredPath);
            if (!empty($sigStoredPaths)) Storage::delete($sigStoredPaths);
            if (isset($outFullPath) && file_exists($outFullPath)) @unlink($outFullPath);
            Log::error("Error PDF supervisor/admin: " . $e->getMessage());
            throw $e;
        }
    }

    // =======================
    // Helper AJAX untuk Supervisor/Admin
    // =======================
    // Dropdown Dokumen
    public function getDokumenByUser($userId)
    {
        $dokumen = DB::table('mandatory_uploads')
            ->join('jenis_dokumen', 'jenis_dokumen.id', '=', 'mandatory_uploads.jenis_dokumen_id')
            ->where('mandatory_uploads.user_id', $userId)
            ->where('mandatory_uploads.penilaian', 0) // <-- filter di sini
            ->select('jenis_dokumen.id', 'jenis_dokumen.nama_dokumen')
            ->distinct()
            ->get();

        return response()->json($dokumen);
    }

    // Dropdown Periode
    public function getPeriodeByUserDokumen($userId, $dokumenId)
    {
        $periode = DB::table('periode')
            ->join('mandatory_uploads', 'periode.id', '=', 'mandatory_uploads.periode_id')
            ->where('mandatory_uploads.user_id', $userId)
            ->where('mandatory_uploads.jenis_dokumen_id', $dokumenId)
            ->where('mandatory_uploads.penilaian', 0) // <-- filter di sini
            ->select('periode.id', 'periode.periode_key')
            ->distinct()
            ->get();

        return response()->json($periode);
    }

     public function updatePdf(Request $request, $dokumen_id)
    {
        // Validasi input
        $request->validate([
            'pdf'               => 'sometimes|file|mimes:pdf|max:10240', // sometimes = tidak wajib
            'signatures'        => 'nullable|array',
            'signatures.*.page' => 'required_with:signatures|integer|min:1',
            'signatures.*.x'    => 'required_with:signatures|numeric|min:0|max:1',
            'signatures.*.y'    => 'required_with:signatures|numeric|min:0|max:1',
            'signatures.*.w'    => 'required_with:signatures|numeric|min:0|max:1',
            'files'             => 'nullable|array',
            'files.*'           => 'required_with:files|file|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        // Cari dokumen yang akan diupdate
        $dokumen = DB::table('dokumen')->where('id', $dokumen_id)->first();
        if (!$dokumen) {
            return response()->json(['success' => false, 'message' => 'Dokumen tidak ditemukan.'], 404);
        }

        // Jika tidak ada file PDF baru DAN tidak ada signature baru, tidak ada yang perlu dilakukan
        if (!$request->hasFile('pdf') && !$request->has('signatures')) {
            return response()->json(['success' => true, 'message' => 'Tidak ada perubahan yang disimpan.']);
        }

        DB::beginTransaction();
        try {
            $pdfPathToProcess = Storage::path($dokumen->path);
            $newPdfUploaded = false;

            // 1. Jika ada PDF baru diupload
            if ($request->hasFile('pdf')) {
                $pdfFile = $request->file('pdf');
                $pdfName = 'temp_update_' . time() . '.pdf';
                $storedPath = $pdfFile->storeAs('public/tmp', $pdfName);
                $pdfPathToProcess = Storage::path($storedPath);
                $newPdfUploaded = true;
            }

            // 2. Proses penambahan signature (jika ada)
            $pdf = new Fpdi();
            $pageCount = $pdf->setSourceFile($pdfPathToProcess);

            // (Logika untuk menyimpan file signature sementara sama seperti di signPdf)
            // ...
            $sigStoredPaths = [];
            $sigTempPaths = [];
            foreach ($request->file('files', []) as $i => $sigFile) {
                $sigName = 'sig_' . time() . '_' . Str::random(6) . '.' . $sigFile->getClientOriginalExtension();
                $sigStored = $sigFile->storeAs('public/tmp', $sigName);
                $sigStoredPaths[$i] = $sigStored;
                $sigTempPaths[$i] = Storage::path($sigStored);
            }

            $signatures = $request->input('signatures', []);
            
            for ($p = 1; $p <= $pageCount; $p++) {
                $tplId = $pdf->importPage($p);
                $size = $pdf->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);

                // (Logika untuk menempelkan signature sama seperti di signPdf)
                // ...
                foreach ($signatures as $idx => $sig) {
                    if ((int)$sig['page'] === $p && isset($sigTempPaths[$idx])) {
                        $x = floatval($sig['x']) * $size['width'];
                        $y = floatval($sig['y']) * $size['height'];
                        $w = floatval($sig['w']) * $size['width'];
                        $pdf->Image($sigTempPaths[$idx], $x, $y, $w);
                    }
                }
            }

            // 3. Hapus file PDF lama dari storage
            if (Storage::exists($dokumen->path)) {
                Storage::delete($dokumen->path);
            }

            // 4. Simpan file PDF baru yang sudah ditandatangani
            $jenisDokumen = DB::table('jenis_dokumen')->where('id', $dokumen->jenis_dokumen_id)->first();
            $periode = DB::table('periode')->where('id', $dokumen->periode_id)->first();
            
            $outName = 'Ditandatangani_' . Str::slug($jenisDokumen->nama_dokumen) . '_' . $periode->periode_key . '.pdf';
            $outStoredRelativePath = 'uploads/' . $outName; // Simpan path relatif untuk DB
            $outFullPath = Storage::path($outStoredRelativePath);
            
            $pdf->Output($outFullPath, 'F');

            // 5. Update record di database
            DB::table('dokumen')
                ->where('id', $dokumen->id)
                ->update([
                    'path' => $outStoredRelativePath, // Simpan path relatif
                    'tanggal_unggah' => now(),
                    'updated_at' => now()
                ]);

            // Hapus file temp jika ada
            if ($newPdfUploaded) {
                Storage::delete($storedPath);
            }
            // (Hapus juga file signature temp)

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Dokumen berhasil diperbarui.']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Error saat update PDF: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }
    }
}
