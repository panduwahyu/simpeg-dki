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
    public function index(){
       // Panggil method untuk ambil semua dokumen yang belum diupload user
        $belumUpload = $this->getDokumenBelum();

        return view('pdf.sign', [
            'belumUpload' => $belumUpload
        ]);
    }

    private function getDokumenBelum()
    {
        $userId = Auth::id(); // Ambil ID user yang login

       // Ambil semua dokumen-periode yang belum diupload
        $belumUpload = DB::table('mandatory_uploads')
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

        // Ambil hanya yang belum diupload
        return $belumUpload;
    }

    public function signPdf(Request $request)
    {
        $request->validate([
            'mandatory_id' => 'required|exists:mandatory_uploads,id',
            'pdf' => 'required|file|mimes:pdf|max:10240', // max 10MB
            'signature' => 'required|image|mimes:png,jpg,jpeg|max:5120', // max 5MB
            'page' => 'required|integer|min:1',
            'x_percent' => 'required|numeric|min:0|max:1',
            'y_percent' => 'required|numeric|min:0|max:1',
            'width_percent' => 'required|numeric|min:0|max:1',
        ]);

        // Simpan sementara
        $pdfFile = $request->file('pdf');
        $sigFile = $request->file('signature');

        $pdfName = 'orig_' . time() . '_' . Str::random(6) . '.' . $pdfFile->getClientOriginalExtension();
        $sigName = 'sig_' . time() . '_' . Str::random(6) . '.' . $sigFile->getClientOriginalExtension();

        $pdfPath = $pdfFile->storeAs('tmp', $pdfName);
        $sigPath = $sigFile->storeAs('tmp', $sigName);

        $pdfFullPath = storage_path('app/private/' . $pdfPath);
        $sigFullPath = storage_path('app/private/' . $sigPath);

        // Ambil nilai relative dari request
        $pageNumber = (int) $request->input('page', 1);
        $xPercent = (float) $request->input('x_percent');
        $yPercent = (float) $request->input('y_percent');
        $wPercent = (float) $request->input('width_percent');

        // Mulai proses FPDI
        $pdf = new Fpdi();

        // set source
        $pageCount = $pdf->setSourceFile($pdfFullPath);

        // safety: jika pageNumber > pageCount, set ke pageCount
        if ($pageNumber > $pageCount) {
            $pageNumber = $pageCount;
        }

        // import semua halaman, dan tandai halaman dimana akan ditempel tanda tangan
        for ($i = 1; $i <= $pageCount; $i++) {
            $tplId = $pdf->importPage($i);
            $size = $pdf->getTemplateSize($tplId);
            $orientation = ($size['width'] > $size['height']) ? 'L' : 'P';

            // buat page baru dengan ukuran sama
            $pdf->AddPage($orientation, [$size['width'], $size['height']]);
            $pdf->useTemplate($tplId);

            // kalau halaman target -> gambar signature
            if ($i === $pageNumber) {
                // ukuran halaman (mm)
                $pageW = $size['width'];
                $pageH = $size['height'];

                // hitung posisi mm berdasarkan percent
                $xMm = $xPercent * $pageW;
                $yMm = $yPercent * $pageH;
                $sigWidthMm = $wPercent * $pageW;

                // menempel gambar; hanya set width supaya height skala otomatis
                $pdf->Image($sigFullPath, $xMm, $yMm, $sigWidthMm);
            }
        }

        // buat file output
        $outName = 'signed_' . time() . '_' . Str::random(6) . '.pdf';
        $outPath = storage_path('app/private/tmp/' . $outName);
        // simpan ke file
        $pdf->Output($outPath, 'F');

        // hapus file input sementara (orig + sig), biar tidak menumpuk
        try {
            @unlink($pdfFullPath);
            @unlink($sigFullPath);
        } catch (\Throwable $e) { /* ignore */ }

        // download & hapus file hasil setelah dikirim
        return response()->download($outPath, $outName)->deleteFileAfterSend(false);

         // Update status upload
        DB::table('mandatory_uploads')
            ->where('id', $request->mandatory_id)
            ->update([
                'is_uploaded' => 1,
                'updated_at' => now()
            ]);
    }
}