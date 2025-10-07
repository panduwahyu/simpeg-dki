<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Validation\Rule;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Log;

class UserManagementController extends Controller
{
    /**
     * Tampilkan daftar semua user
     */
    public function index()
    {
        $users = User::all();
        return view('pages.laravel-examples.user-management', compact('users'));
    }

    /**
     * Tampilkan form untuk tambah user baru
     */
    public function create()
    {
        return view('pages.laravel-examples.user-create');
    }

    /**
     * Simpan user baru ke database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'role'       => 'required|string|max:50',
            'nip'        => 'nullable|string|max:50',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan'    => 'nullable|string|max:255',
            'pangkat'    => 'nullable|string|max:255',
            'golongan'   => 'nullable|string|max:255',
        ]);

        // Normalisasi role
        $role = strtolower($validated['role']);
        if ($role === 'admin') {
            $role = 'Admin';
        } elseif ($role === 'supervisor') {
            $role = 'Supervisor';
        } else {
            $role = 'Pegawai';
        }

        User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'role'       => $role,
            'nip'        => $validated['nip'] ?? null,
            'unit_kerja' => $validated['unit_kerja'] ?? null,
            'jabatan'    => $validated['jabatan'] ?? null,
            'pangkat'    => $validated['pangkat'] ?? null,
            'golongan'   => $validated['golongan'] ?? null,
            'password'   => bcrypt('password'), // hashed default
        ]);

        return redirect()->route('user-management')
            ->with('status', 'User baru berhasil ditambahkan!');
    }

    /**
     * Edit user
     */
    public function edit(User $user)
    {
        return view('pages.laravel-examples.user-edit', compact('user'));
    }

    /**
     * Update user
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => ['required','email', Rule::unique('users')->ignore($user->id)],
            'role'       => 'required|string|max:50',
            'nip'        => 'nullable|string|max:50',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan'    => 'nullable|string|max:255',
            'pangkat'    => 'nullable|string|max:255',
            'golongan'   => 'nullable|string|max:255',
        ]);

        // Normalisasi role
        $role = strtolower($validated['role']);
        if ($role === 'admin') {
            $role = 'Admin';
        } elseif ($role === 'supervisor') {
            $role = 'Supervisor';
        } else {
            $role = 'Pegawai';
        }

        $user->update([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'role'       => $role,
            'nip'        => $validated['nip'] ?? null,
            'unit_kerja' => $validated['unit_kerja'] ?? null,
            'jabatan'    => $validated['jabatan'] ?? null,
            'pangkat'    => $validated['pangkat'] ?? null,
            'golongan'   => $validated['golongan'] ?? null,
        ]);

        return redirect()->route('user-management')
            ->with('status', 'User berhasil diupdate!');
    }

    /**
     * Hapus user
     */
    public function destroy(User $user)
    {
        $user->delete();
        return redirect()->route('user-management')
            ->with('status', 'User berhasil dihapus!');
    }

    /**
     * Export users ke Excel (.xlsx)
     */
    public function export()
    {
        $users = User::all(['id','name','email','role','nip','unit_kerja','jabatan','pangkat','golongan']);

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Header
        $sheet->fromArray(['ID','Name','Email','Role','NIP','Unit Kerja','Jabatan','Pangkat','Golongan'], NULL, 'A1');

        // Data
        $rowNumber = 2;
        foreach ($users as $user) {
            $sheet->fromArray([
                $user->id,
                $user->name,
                $user->email,
                $user->role,
                $user->nip,
                $user->unit_kerja,
                $user->jabatan,
                $user->pangkat,
                $user->golongan,
            ], NULL, 'A'.$rowNumber);
            $rowNumber++;
        }

        $filename = 'users.xlsx';
        $writer = new Xlsx($spreadsheet);

        $writer->save($filename);
        return response()->download($filename)->deleteFileAfterSend(true);
    }

    /**
     * Import users dari CSV atau Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt,xlsx',
        ]);

        $file = $request->file('file');
        $extension = $file->getClientOriginalExtension();

        Log::info("Mulai import user dari file: {$file->getClientOriginalName()}");

        if (in_array($extension, ['csv','txt'])) {
            $handle = fopen($file, 'r');
            $header = fgetcsv($handle);

            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($header, $row);
                $email = trim($data['Email'] ?? '');

                if (empty($email)) {
                    Log::warning("Baris dilewati: email kosong");
                    continue;
                }

                if (User::where('email', $email)->exists()) {
                    Log::info("Skip: email sudah ada ($email)");
                    continue;
                }

                $roleInput = strtolower(trim($data['Role'] ?? ''));
                if ($roleInput === 'admin') {
                    $role = 'Admin';
                } elseif ($roleInput === 'supervisor') {
                    $role = 'Supervisor';
                } else {
                    $role = 'Pegawai';
                }

                try {
                    User::create([
                        'name'       => $data['Name'],
                        'email'      => $email,
                        'role'       => $role,
                        'nip'        => $data['NIP'] ?? null,
                        'unit_kerja' => $data['Unit Kerja'] ?? null,
                        'jabatan'    => $data['Jabatan'] ?? null,
                        'pangkat'    => $data['Pangkat'] ?? null,
                        'golongan'   => $data['Golongan'] ?? null,
                        'password'   => bcrypt('password'),
                    ]);
                    Log::info("User berhasil diimport: {$email}");
                } catch (\Exception $e) {
                    Log::error("Gagal import user {$email}: ".$e->getMessage());
                }
            }
            fclose($handle);
        } elseif ($extension === 'xlsx') {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray();

            $header = $rows[0];
            unset($rows[0]);

            foreach ($rows as $row) {
                $data = array_combine($header, $row);
                $email = trim($data['Email'] ?? '');

                if (empty($email)) {
                    Log::warning("Baris dilewati: email kosong");
                    continue;
                }

                if (User::where('email', $email)->exists()) {
                    Log::info("Skip: email sudah ada ($email)");
                    continue;
                }

                $roleInput = strtolower(trim($data['Role'] ?? ''));
                if ($roleInput === 'admin') {
                    $role = 'Admin';
                } elseif ($roleInput === 'supervisor') {
                    $role = 'Supervisor';
                } else {
                    $role = 'Pegawai';
                }

                try {
                    User::create([
                        'name'       => $data['Name'],
                        'email'      => $email,
                        'role'       => $role,
                        'nip'        => $data['NIP'] ?? null,
                        'unit_kerja' => $data['Unit Kerja'] ?? null,
                        'jabatan'    => $data['Jabatan'] ?? null,
                        'pangkat'    => $data['Pangkat'] ?? null,
                        'golongan'   => $data['Golongan'] ?? null,
                        'password'   => bcrypt('password'),
                    ]);
                    Log::info("User berhasil diimport: {$email}");
                } catch (\Exception $e) {
                    Log::error("Gagal import user {$email}: ".$e->getMessage());
                }
            }
        }

        Log::info("Import user selesai.");
        return redirect()->route('user-management')
            ->with('status', 'Users imported successfully! (lihat log untuk detail)');
    }
}
