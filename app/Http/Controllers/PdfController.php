<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log; // ✅ Tambahan ini
use Exception;

class PdfController extends Controller
{
    public function index()
    {
        $belumUpload = $this->getDokumenBelum();

        return view('pdf.sign', [
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
                'mandatory_uploads.id',
                'jenis_dokumen.nama_dokumen',
                'periode.periode_key'
            )
            ->orderBy('periode.periode_key')
            ->orderBy('jenis_dokumen.nama_dokumen')
            ->get();
    }

    public function signPdf(Request $request)
    {
        $request->validate([
            'mandatory_id' => 'required|exists:mandatory_uploads,id',
            'pdf' => 'required|file|mimes:pdf|max:10240',
            'signatures' => 'required|array|min:1',
            'signatures.*.page' => 'required|integer|min:1',
            'signatures.*.x' => 'required|numeric|min:0|max:1',
            'signatures.*.y' => 'required|numeric|min:0|max:1',
            'signatures.*.w' => 'required|numeric|min:0|max:1',
            'files' => 'required|array|min:1',
            'files.*' => 'required|file|image|mimes:png,jpg,jpeg|max:5120',
        ]);

        // Simpan PDF asli ke storage/app/public/tmp
        $pdfFile = $request->file('pdf');
        $pdfName = 'orig_' . time() . '_' . Str::random(6) . '.' . $pdfFile->getClientOriginalExtension();
        $pdfStoredPath = $pdfFile->storeAs('public/tmp', $pdfName);
        $pdfFullPath = Storage::path($pdfStoredPath);

        Log::info("📄 PDF asli disimpan: $pdfFullPath");

        // Siapkan FPDI
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfFullPath);

        // Simpan sementara file signature (juga ke public/tmp)
        $sigStoredPaths = [];
        $sigTempPaths = [];
        foreach ($request->file('files', []) as $i => $sigFile) {
            $sigName = 'sig_' . time() . '_' . Str::random(6) . '.' . $sigFile->getClientOriginalExtension();
            $sigStored = $sigFile->storeAs('public/tmp', $sigName);
            $sigStoredPaths[$i] = $sigStored;
            $sigTempPaths[$i] = Storage::path($sigStored);

            Log::info("🖊️ Signature ke-$i disimpan: " . $sigTempPaths[$i]);
        }

        $signatures = $request->input('signatures', []);

        try {
            // Tempelkan signature ke tiap halaman
            for ($p = 1; $p <= $pageCount; $p++) {
                $tplId = $pdf->importPage($p);
                $size = $pdf->getTemplateSize($tplId);
                $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
                $pdf->AddPage($orientation, [$size['width'], $size['height']]);
                $pdf->useTemplate($tplId);

                foreach ($signatures as $idx => $sig) {
                    if ((int)$sig['page'] === $p && isset($sigTempPaths[$idx])) {
                        $sigFullPath = $sigTempPaths[$idx];

                        $x = floatval($sig['x']) * $size['width'];
                        $y = floatval($sig['y']) * $size['height'];
                        $w = floatval($sig['w']) * $size['width'];

                        if (file_exists($sigFullPath)) {
                            Log::info("✔️ Tempel signature ke-$idx di halaman $p (x=$x, y=$y, w=$w)");
                            $pdf->Image($sigFullPath, $x, $y, $w);
                        }
                    }
                }
            }

            // Buat file output di public/tmp
            $outName = 'signed_' . time() . '_' . Str::random(6) . '.pdf';
            $outStored = 'public/tmp/' . $outName;
            $outFullPath = Storage::path($outStored);
            $pdf->Output($outFullPath, 'F');

            Log::info("✅ PDF hasil ditulis: $outFullPath");

            // Update DB
            DB::transaction(function () use ($request) {
                DB::table('mandatory_uploads')
                    ->where('id', $request->mandatory_id)
                    ->update([
                        'is_uploaded' => 1,
                        'updated_at' => now()
                    ]);
            });

            // Hapus file input sementara
            Storage::delete($pdfStoredPath);
            if (!empty($sigStoredPaths)) {
                Storage::delete($sigStoredPaths);
            }

            return response()->download($outFullPath, $outName)->deleteFileAfterSend(true);

        } catch (Exception $e) {
            Storage::delete($pdfStoredPath);
            if (!empty($sigStoredPaths)) {
                Storage::delete($sigStoredPaths);
            }
            if (isset($outFullPath) && file_exists($outFullPath)) {
                @unlink($outFullPath);
            }
            Log::error("❌ Error saat proses tanda tangan PDF: " . $e->getMessage());
            throw $e;
        }
    }
}
