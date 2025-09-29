<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

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
            'signatures.*.file' => 'required|file|image|mimes:png,jpg,jpeg|max:5120',
            'signatures.*.page' => 'required|integer|min:1',
            'signatures.*.x' => 'required|numeric|min:0|max:1',
            'signatures.*.y' => 'required|numeric|min:0|max:1',
            'signatures.*.w' => 'required|numeric|min:0|max:1',
        ]);

        $pdfFile = $request->file('pdf');
        $pdfName = 'orig_' . time() . '_' . Str::random(6) . '.' . $pdfFile->getClientOriginalExtension();
        $pdfPath = $pdfFile->storeAs('private/tmp', $pdfName);
        $pdfFullPath = storage_path('app/' . $pdfPath);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfFullPath);

        $signatures = $request->input('signatures', []);
        $sigTempPaths = [];

        // simpan sementara semua signature
        foreach ($request->file('signatures', []) as $i => $sigFile) {
            $sigName = 'sig_' . time() . '_' . Str::random(6) . '.' . $sigFile->getClientOriginalExtension();
            $sigPath = $sigFile->storeAs('private/tmp', $sigName);
            $sigTempPaths[$i] = storage_path('app/' . $sigPath);
        }

        // import semua halaman dan tempel tanda tangan
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            foreach ($signatures as $idx => $sig) {
                if ((int)$sig['page'] === $i) {
                    $sigFullPath = $sigTempPaths[$idx];
                    $xMm = $sig['x'] * $size['width'];
                    $yMm = $sig['y'] * $size['height'];
                    $wMm = $sig['w'] * $size['width'];

                    $pdf->Image($sigFullPath, $xMm, $yMm, $wMm);
                }
            }
        }

        $outName = 'signed_' . time() . '_' . Str::random(6) . '.pdf';
        $outPath = storage_path('app/private/tmp/' . $outName);
        $pdf->Output($outPath, 'F');

        // hapus sementara file orig & signature
        @unlink($pdfFullPath);
        foreach ($sigTempPaths as $p) { @unlink($p); }

        // update status upload
        DB::table('mandatory_uploads')
            ->where('id', $request->mandatory_id)
            ->update([
                'is_uploaded' => 1,
                'updated_at' => now()
            ]);

        return response()->download($outPath, $outName)->deleteFileAfterSend(true);
    }
}
