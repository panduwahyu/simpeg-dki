<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
            'password'   => 'required|string|min:6|confirmed',
            'role'       => 'required|string|max:50',

            // field tambahan:
            'nip'        => 'nullable|string|max:50',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan'    => 'nullable|string|max:255',
            'pangkat'    => 'nullable|string|max:255',
            'golongan'   => 'nullable|string|max:255',
        ]);

        User::create([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'password'   => $validated['password'],
            'role'       => $validated['role'],
            'nip'        => $validated['nip'] ?? null,
            'unit_kerja' => $validated['unit_kerja'] ?? null,
            'jabatan'    => $validated['jabatan'] ?? null,
            'pangkat'    => $validated['pangkat'] ?? null,
            'golongan'   => $validated['golongan'] ?? null,
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
            'password'   => 'nullable|string|min:6|confirmed',
            'role'       => 'required|string|max:50',

            // field tambahan:
            'nip'        => 'nullable|string|max:50',
            'unit_kerja' => 'nullable|string|max:255',
            'jabatan'    => 'nullable|string|max:255',
            'pangkat'    => 'nullable|string|max:255',
            'golongan'   => 'nullable|string|max:255',
        ]);

        // update field dasar
        $user->name       = $validated['name'];
        $user->email      = $validated['email'];
        $user->role       = $validated['role'];
        $user->nip        = $validated['nip'] ?? null;
        $user->unit_kerja = $validated['unit_kerja'] ?? null;
        $user->jabatan    = $validated['jabatan'] ?? null;
        $user->pangkat    = $validated['pangkat'] ?? null;
        $user->golongan   = $validated['golongan'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = $validated['password'];
        }

        $user->save();

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
}
