<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\Dokumen;
use App\Models\JenisDokumen;
use App\Models\Periode;
use App\Models\NamaPegawai;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class DokumenController extends Controller
{
    /**
     * Tampilkan daftar dokumen
    */
    public function index(Request $request)
    {
        $query = Dokumen::query();
        $user = Auth::user();

        // Filter berdasarkan jenis dokumen
        if ($request->filled('jenis_dokumen_id')) {
            $query->where('jenis_dokumen_id', $request->jenis_dokumen_id);
        }
        
        // Filter berdasarkan tipe periode
        if ($request->filled('tipe')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('label', $request->tipe);
            });
        }
        
        // Filter berdasarkan tahun
        if ($request->filled('tahun')) {
            $query->whereHas('periode', function ($q) use ($request) {
                $q->where('tahun', $request->tahun);
            });
        }

            // Jika admin/supervisor → bisa lihat semua data, plus filter opsional
            if (in_array($user->role, ['Admin', 'Supervisor'])) {
                // Jika memilih filter user_id, maka tampilkan dokumen user itu
                if ($request->filled('user_id')) {
                    $query->where('user_id', $request->user_id);
                }
                // Ambil semua user untuk dropdown filter
                $pegawai = NamaPegawai::all();
            } 
            // Jika pegawai → hanya data miliknya
            else {
                $query->where('user_id', $user->id);
                $pegawai = collect(); // kosong
            }
        // Ambil data dengan relasi
        $dokumen = $query->with(['jenisDokumen', 'periode', 'user',])
                         ->paginate(10)
                         ->withQueryString();

        // Untuk dropdown filter
        $jenisDokumen = JenisDokumen::all();
        $periode = Periode::select('tipe', 'tahun')->distinct()->get();
        $pegawai = NamaPegawai::all();

        return view('pages.tables', compact('dokumen', 'jenisDokumen', 'periode', 'user', 'pegawai'));
    }

    public function searchPegawai(Request $request)
    {
        $search = $request->input('search');
        $page = $request->input('page', 1);
        $perPage = 20;

        $query = User::query();

        if ($search) {
            $query->where('name', 'LIKE', "%{$search}%");
        }

        $total = $query->count();
        $pegawai = $query->skip(($page - 1) * $perPage)
                        ->take($perPage)
                        ->get(['id', 'name']);

        return response()->json([
            'items' => $pegawai,
            'has_more' => ($page * $perPage) < $total
        ]);
    }

    /**
     * Preview dokumen PDF dari storage private
     */
    public function preview($id)
    {
        $dokumen = Dokumen::findOrFail($id);

        $path = storage_path('app/private/' . $dokumen->path);

        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan di storage. Dicek: ' . $path);
        }

        $mime = mime_content_type($path);
        $file = file_get_contents($path);

        return response($file, 200)
            ->header('Content-Type', $mime)
            ->header('Content-Disposition', 'inline; filename="' . basename($path) . '"');
    }

    /**
     * Menyimpan dokumen ke database
     */
    public function store(Request $request)
    {
        // Validasi input
        $validated = $request->validate([
            'nama_pegawai' => 'nullable|exists:users,id',
            'jenis_dokumen_id' => 'required|exists:jenis_dokumen,id',
            'periode' => 'required',
            'tipe' => 'required',
            'penilai_id' => 'nullable',
            'pdf_file' => 'required|file|mimes:pdf|max:5120', // max 5MB
        ], [
            'jenis_dokumen_id.required' => 'Nama dokumen harus dipilih',
            'jenis_dokumen_id.exists' => 'Nama dokumen tidak valid',
            'periode.required' => 'Tahun harus dipilih',
            'tipe.required' => 'Periode harus dipilih',
            // 'penilai_id.required' => 'Penandatangan harus dipilih',
            'file.required' => 'File PDF harus diupload',
            'file.mimes' => 'File harus berformat PDF',
            'file.max' => 'Ukuran file maksimal 5MB',
        ]);

        
        try {
            // Tentukan user_id berdasarkan role
            if (in_array(Auth::user()->role, ['Admin', 'Supervisor'])) {
                $userId = $request->nama_pegawai;
            } else {
                $userId = Auth::user()->id;
            }
            
            // Upload file
            $file = $request->file('pdf_file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('private/uploads', $fileName);

            // Ambil periode_id berdasarkan tahun dan tipe
            $periode = Periode::where('tahun', $request->periode)
            ->where('tipe', $request->tipe)
            ->first();
            
            if (!$periode) {
                return back()->with('error', 'Periode tidak ditemukan')->withInput();
            }
            
            // $mandatory = DB::table('mandatory_uploads')
            //     ->where('user_id', $request->nama_pegawai)
            //     ->where('jenis_dokumen_id', $request->jenis_dokumen_id)
            //     ->where('periode_id', $request->periode)
            //     ->first();
    
            // if (!$mandatory) {
            //     return back()->withErrors(['msg' => 'Mandatory upload tidak ditemukan.']);
            // }

            // Simpan ke database
            $dokumen = Dokumen::create([
                'penilai_id' => $request->penilai_id,
                'path' => $filePath,
                'user_id' => $userId,
                'jenis_dokumen_id' => trim($request->jenis_dokumen_id), // trim untuk menghapus spasi
                'periode_id' => $periode->id,
                // 'file_name' => $fileName,
                // 'status' => 'pending', // atau sesuai kebutuhan
                'tanggal_unggah' => now(),
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Dokumen berhasil disimpan!',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage(),
            ], 500);
        }

    }
    public function downloadMultiple(Request $request)
    {
        try {
            $request->validate([
                'dokumen_ids' => 'required|array',
                'dokumen_ids.*' => 'exists:dokumen,id'
            ]);

            $dokumenIds = $request->dokumen_ids;
            $dokumen = Dokumen::with(['pegawai', 'jenisDokumen', 'periode'])
                            ->whereIn('id', $dokumenIds)
                            ->get();

            if ($dokumen->isEmpty()) {
                return response()->json(['error' => 'Tidak ada dokumen yang ditemukan'], 404);
            }

            // Jika hanya 1 file, download langsung
            if ($dokumen->count() === 1) {
                $file = $dokumen->first();
                $filePath = storage_path('app/private/' . $file->path);
                
                Log::info('Mencoba download file: ' . $filePath);
                
                if (!file_exists($filePath)) {
                    Log::error('File tidak ditemukan: ' . $filePath);
                    return response()->json(['error' => 'File tidak ditemukan di server'], 404);
                }

                return response()->download($filePath);
            }

            // Jika lebih dari 1 file, buat ZIP
            $zipFileName = 'dokumen_' . date('Ymd_His') . '.zip';
            $zipPath = storage_path('app/temp/' . $zipFileName);

            // Buat folder temp jika belum ada
            $tempDir = storage_path('app/temp');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }

            $zip = new ZipArchive;

            if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) === TRUE) {
                $fileCount = 0;
                
                foreach ($dokumen as $index => $dok) {
                    $filePath = storage_path('app/private/' . $dok->path);
                    
                    Log::info('Menambahkan file ke ZIP: ' . $filePath);
                    
                    if (file_exists($filePath)) {
                        // Buat nama file yang unik
                        $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                        $fileName = $dok->pegawai->name . '_' . 
                                $dok->jenisDokumen->nama_dokumen . '_' . 
                                $dok->periode->tipe . '_' . 
                                $dok->periode->tahun . '_' . 
                                ($index + 1) . '.' . $extension;
                        
                        // Bersihkan nama file dari karakter tidak valid
                        $fileName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $fileName);
                        
                        $zip->addFile($filePath, $fileName);
                        $fileCount++;
                    } else {
                        Log::warning('File tidak ditemukan: ' . $filePath);
                    }
                }
                
                $zip->close();

                if ($fileCount === 0) {
                    Log::error('Tidak ada file yang berhasil ditambahkan ke ZIP');
                    if (file_exists($zipPath)) {
                        unlink($zipPath);
                    }
                    return response()->json(['error' => 'Tidak ada file yang ditemukan'], 404);
                }

                Log::info('ZIP berhasil dibuat dengan ' . $fileCount . ' file');

                // Download dan hapus file temp
                return response()->download($zipPath)->deleteFileAfterSend(true);
            }

            Log::error('Gagal membuat ZIP file');
            return response()->json(['error' => 'Gagal membuat file ZIP'], 500);

        } catch (\Exception $e) {
            Log::error('Error saat download multiple: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan: ' . $e->getMessage()], 500);
        }
    }
    public function deleteMultiple(Request $request)
    {
        try {
            $request->validate([
                'dokumen_ids' => 'required|array',
                'dokumen_ids.*' => 'exists:dokumen,id'
            ]);

            $dokumenIds = $request->dokumen_ids;
            
            // Ambil data dokumen yang akan dihapus
            $dokumen = Dokumen::whereIn('id', $dokumenIds)->get();

            if ($dokumen->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak ada dokumen yang ditemukan'
                ], 404);
            }

            DB::beginTransaction();
            
            try {
                $deletedCount = 0;
                $failedFiles = [];

                foreach ($dokumen as $dok) {
                    // Hapus file fisik dari server
                    $filePath = storage_path('app/private/uploads/' . $dok->path);
                    
                    if (file_exists($filePath)) {
                        if (unlink($filePath)) {
                            Log::info('File berhasil dihapus: ' . $filePath);
                        } else {
                            Log::warning('Gagal menghapus file: ' . $filePath);
                            $failedFiles[] = $dok->path;
                        }
                    } else {
                        Log::warning('File tidak ditemukan: ' . $filePath);
                    }

                    // Hapus record dari database
                    $dok->delete();
                    $deletedCount++;
                }

                DB::commit();

                $message = "Berhasil menghapus {$deletedCount} dokumen";
                
                if (!empty($failedFiles)) {
                    $message .= ", namun " . count($failedFiles) . " file tidak dapat dihapus dari server";
                }

                Log::info('Delete multiple success: ' . $deletedCount . ' dokumen dihapus');

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'deleted_count' => $deletedCount,
                    'failed_files' => $failedFiles
                ]);

            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error saat delete multiple: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

}
