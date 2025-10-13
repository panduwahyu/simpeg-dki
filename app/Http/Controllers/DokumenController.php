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
                $q->where('tipe', $request->tipe);
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
            
            $mandatory = DB::table('mandatory_uploads')
                ->where('user_id', $request->nama_pegawai)
                ->where('jenis_dokumen_id', $request->jenis_dokumen_id)
                ->where('periode_id', $request->periode)
                ->first();
    
            if (!$mandatory) {
                return back()->withErrors(['msg' => 'Mandatory upload tidak ditemukan.']);
            }

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
            
            DB::transaction(function () use ($mandatory, $filePath, $request) {
                    DB::table('mandatory_uploads')
                        ->where('id', $mandatory->id)
                        ->update(['is_uploaded' => 1, 'updated_at' => now()]);
    
                    // DB::table('dokumen')->insert([
                    //     'penilai_id' => $request->penilai_id,
                    //     'path' => $filePath,
                    //     'user_id' => $mandatory->user_id,
                    //     'jenis_dokumen_id' => $mandatory->jenis_dokumen_id,
                    //     'periode_id' => $mandatory->periode_id,
                    //     'tanggal_unggah' => now(),
                    //     'created_at' => now(),
                    //     'updated_at' => now(),
                    // ]);
            });
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

}
