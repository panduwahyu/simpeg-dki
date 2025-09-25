<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    /**
     * Tampilkan halaman sign-up
     */
    public function create()
    {
        return view('register.create');
    }

    /**
     * Proses registrasi user baru
     */
    public function store(Request $request)
    {
        // Validasi input
        $attributes = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|min:5|max:255',
            // 'email_verified_at' => now(),
            // 'password' => 'required|string|min:5|confirmed', // harus ada password_confirmation
        ]);

        // Set role default 'Pegawai'
        $attributes['role'] = 'Pegawai';

        // Simpan user
        $user = User::create($attributes);

        // Login otomatis
        auth()->login($user);

        return redirect('/dashboard')->with('success', 'Account created successfully!');
    } 
}
