<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserManagementController extends Controller
{
    /**
     * Tampilkan daftar semua user
     */
    public function index()
    {
        // Ambil semua data user
        $users = User::all();

        // Kirim data ke view
        return view('pages.laravel-examples.user-management', compact('users'));
    }

    /**
     * Tampilkan form untuk tambah user baru
     */
    public function create()
    {
        // Tampilkan form create user
        return view('pages.laravel-examples.user-create');
    }

    /**
     * Simpan user baru ke database
     */
    public function store(Request $request)
    {
        // Validasi inputan
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
            'role' => 'required|string|max:50'
        ]);

        // Simpan ke tabel users
        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
        ]);

        // Redirect balik ke halaman user-management dengan pesan sukses
        return redirect()
            ->route('user-management')
            ->with('status', 'User baru berhasil ditambahkan!');
    }
}
