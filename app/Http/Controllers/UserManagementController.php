<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UserManagementController extends Controller
{
    /** Tampilkan daftar semua user */
    public function index()
    {
        $users = User::orderBy('nama_gelar')
            ->paginate(10)
            ->onEachSide(1); // hanya tampil 3 nomor halaman

        return view('pages.laravel-examples.user-management', compact('users'));
    }

    /** Tampilkan form untuk tambah user baru */
    public function create()
    {
        return view('pages.laravel-examples.user-create');
    }

    /** Simpan user baru ke database */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_gelar' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|string|max:50',
            'nip_bps' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50',
            'wilayah' => 'nullable|string|max:255',
            'keaktifan' => 'required|string|max:100',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'golongan' => 'nullable|string|max:255',
        ]);

        $role = ucfirst(strtolower($validated['role']));
        $pangkatMap = $this->getPangkatMap();
        $pangkat = $pangkatMap[$validated['golongan']] ?? null;

        User::create([
            'name' => $validated['nama_gelar'], // untuk SSO
            'nama_gelar' => $validated['nama_gelar'],
            'email' => $validated['email'],
            'role' => $role,
            'nip_bps' => $validated['nip_bps'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'wilayah' => $validated['wilayah'] ?? null,
            'unit_kerja' => $validated['unit_kerja'] ?? null,
            'jabatan' => $validated['jabatan'] ?? null,
            'golongan' => $validated['golongan'] ?? null,
            'pangkat' => $pangkat,
            'password' => bcrypt('password'),
        ]);

        return redirect()->route('user-management')->with('status', 'User baru berhasil ditambahkan!');
    }

    /** Edit user */
    public function edit(User $user)
    {
        return view('pages.laravel-examples.user-edit', compact('user'));
    }

    /** Update user */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama_gelar' => 'required|string|max:255',
            'email' => ['required', 'email', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|max:50',
            'nip_bps' => 'nullable|string|max:50',
            'nip' => 'nullable|string|max:50',
            'keaktifan' => 'required|string|max:100',
            'wilayah' => 'nullable|string|max:255',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan' => 'nullable|string|max:255',
            'golongan' => 'nullable|string|max:255',
        ]);

        $role = ucfirst(strtolower($validated['role']));
        $pangkatMap = $this->getPangkatMap();
        $pangkat = $pangkatMap[$validated['golongan']] ?? null;

        $user->update([
            'name' => $validated['nama_gelar'],
            'nama_gelar' => $validated['nama_gelar'],
            'email' => $validated['email'],
            'role' => $role,
            'nip_bps' => $validated['nip_bps'] ?? null,
            'nip' => $validated['nip'] ?? null,
            'keaktifan' => $validated['keaktifan'],
            'wilayah' => $validated['wilayah'] ?? null,
            'unit_kerja' => $validated['unit_kerja'] ?? null,
            'jabatan' => $validated['jabatan'] ?? null,
            'golongan' => $validated['golongan'] ?? null,
            'pangkat' => $pangkat,
        ]);

        return redirect()->route('user-management')->with('status', 'User berhasil diupdate!');
    }

    /** Hapus user */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('user-management')->with('status', 'User berhasil dihapus!');
    }

    /** Export users ke Excel */
    public function export()
    {
        $users = User::all([
            'email', 'nama_gelar', 'nip_bps', 'nip', 'role', 'wilayah', 'unit_kerja', 'jabatan', 'golongan'
        ]);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray([
            'Email', 'Nama', 'NIP BPS', 'NIP', 'Status', 'Wilayah', 'Unit Kerja', 'Jabatan', 'Golongan',
        ], null, 'A1');

        $rowNumber = 2;
        foreach ($users as $user) {
            $sheet->fromArray([
                $user->email,
                $user->nama_gelar,
                $user->nip_bps,
                $user->nip,
                $user->role,
                $user->wilayah,
                $user->unit_kerja,
                $user->jabatan,
                $user->golongan,
            ], null, 'A' . $rowNumber);
            $rowNumber++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $filename = 'users_export_' . now()->format('Ymd_His') . '.xlsx';
        $writer = new Xlsx($spreadsheet);
        $writer->save($filename);

        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /** Import users dari Excel / CSV dengan response JSON */
    public function import(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:xlsx,xls,csv,txt']);

        $file = $request->file('file');
        $rows = [];

        $ext = $file->getClientOriginalExtension();
        if (in_array($ext, ['csv', 'txt'])) {
            $handle = fopen($file, 'r');
            while (($data = fgetcsv($handle)) !== false) $rows[] = $data;
            fclose($handle);
        } else {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $rows = $spreadsheet->getActiveSheet()->toArray();
        }

        if (count($rows) <= 1) {
            return response()->json(['rows' => []]);
        }

        unset($rows[0]);
        $pangkatMap = $this->getPangkatMap();
        $responseRows = [];

        foreach ($rows as $index => $r) {
            $rowNumber = $index + 2;
            $email      = trim($r[0] ?? '');
            $nama_gelar = trim($r[1] ?? '');
            $nip_bps    = trim($r[2] ?? '');
            $nip        = trim($r[3] ?? '');
            $roleInput  = strtolower(trim($r[4] ?? ''));
            $wilayah    = trim($r[5] ?? '');
            $unit_kerja = trim($r[6] ?? '');
            $jabatan    = trim($r[7] ?? '');
            $golongan   = strtoupper(trim($r[8] ?? ''));
            $pangkat    = $pangkatMap[$golongan] ?? null;

            $warnings = [];

            if (empty($email)) {
                $responseRows[] = [
                    'row' => $rowNumber - 1,
                    'status' => 'error',
                    'message' => 'Email kosong'
                ];
                continue;
            }

            if (User::where('email', $email)->exists()) {
                $responseRows[] = [
                    'row' => $rowNumber - 1,
                    'status' => 'error',
                    'message' => 'Email sudah ada'
                ];
                continue;
            }

            $validRoles = ['admin', 'supervisor'];
            if (in_array($roleInput, $validRoles)) {
                $role = ucfirst($roleInput);
            } else {
                $role = 'Pegawai';
                $warnings[] = "kolom status otomatis dibuat Pegawai karena role kosong atau salah input";
            }

            if (!empty($golongan) && !isset($pangkatMap[$golongan])) {
                $golongan = null;
                $pangkat = null;
                $warnings[] = "kolom golongan dikosongkan karena salah input";
            }

            User::create([
                'name' => $nama_gelar,
                'nama_gelar' => $nama_gelar,
                'email' => $email,
                'nip_bps' => $nip_bps ?: null,
                'nip' => $nip ?: null,
                'role' => $role,
                'keaktifan' => 'Aktif',
                'wilayah' => $wilayah ?: null,
                'unit_kerja' => $unit_kerja ?: null,
                'jabatan' => $jabatan ?: null,
                'golongan' => $golongan ?: null,
                'pangkat' => $pangkat ?: null,
                'password' => bcrypt('password'),
            ]);

            if (!empty($warnings)) {
                $responseRows[] = [
                    'row' => $rowNumber - 1,
                    'status' => 'warning',
                    'message' => "Tetap input baris " . ($rowNumber - 1) . "; email: $email; " . implode("; ", $warnings)
                ];
            } else {
                $responseRows[] = [
                    'row' => $rowNumber - 1,
                    'status' => 'success',
                    'message' => "Berhasil diimpor"
                ];
            }
        }

        return response()->json(['rows' => $responseRows]);
    }

    /** Mapping pangkat otomatis berdasarkan golongan */
    private function getPangkatMap()
    {
        return [
            'I/A' => 'Juru Muda', 'I/B' => 'Juru Muda Tingkat I', 'I/C' => 'Juru', 'I/D' => 'Juru Tingkat I',
            'II/A' => 'Pengatur Muda', 'II/B' => 'Pengatur Muda Tingkat I', 'II/C' => 'Pengatur', 'II/D' => 'Pengatur Tingkat I',
            'III/A' => 'Penata Muda', 'III/B' => 'Penata Muda Tingkat I', 'III/C' => 'Penata', 'III/D' => 'Penata Tingkat I',
            'IV/A' => 'Pembina', 'IV/B' => 'Pembina Tingkat I', 'IV/C' => 'Pembina Utama Muda',
            'IV/D' => 'Pembina Utama Madya', 'IV/E' => 'Pembina Utama',
            'I' => 'Pemula', 'II' => 'Terampil', 'III' => 'Mahir', 'IV' => 'Penyelia',
            'V' => 'Ahli Pertama', 'VI' => 'Ahli Muda', 'VII' => 'Ahli Madya', 'VIII' => 'Ahli Utama',
            'IX' => 'Fungsional Tingkat Lanjut I', 'X' => 'Fungsional Tingkat Lanjut II', 'XI' => 'Fungsional Tingkat Lanjut III',
            'XII' => 'Koordinator', 'XIII' => 'Pengawas', 'XIV' => 'Pejabat Fungsional Utama',
            'XV' => 'Pejabat Pimpinan Tinggi Pratama', 'XVI' => 'Pejabat Pimpinan Tinggi Madya', 'XVII' => 'Pejabat Pimpinan Tinggi Utama',
        ];
    }

    /** Pencarian AJAX user */
    public function search(Request $request)
    {
        $keyword = $request->get('keyword', '');
        $users = User::where('nama_gelar', 'like', "%{$keyword}%")
            ->orWhere('email', 'like', "%{$keyword}%")
            ->orWhere('role', 'like', "%{$keyword}%")
            ->orWhere('nip_bps', 'like', "%{$keyword}%")
            ->orWhere('nip', 'like', "%{$keyword}%")
            ->orderBy('nama_gelar')
            ->paginate(10)
            ->onEachSide(1); // batasi 3 halaman juga

        return view('pages.laravel-examples.user-search-result', compact('users'));
    }

    public function deleteAllPegawai()
    {
        try {
            $deleted = \App\Models\User::where('role', 'Pegawai')->delete();

            if ($deleted > 0) {
                return response()->json([
                    'message' => "Berhasil menghapus {$deleted} pegawai."
                ]);
            } else {
                return response()->json([
                    'message' => "Tidak ada pegawai yang ditemukan."
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }
}
