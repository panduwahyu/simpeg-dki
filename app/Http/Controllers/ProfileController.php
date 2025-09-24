<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Laravel\Facades\Image; // Laravel 11 + Intervention Image v3

class ProfileController extends Controller
{
    /**
     * Tampilkan halaman profil
     */
    public function create()
    {
        return view('pages.profile');
    }

    /**
     * Update profil user
     */
    public function update(Request $request)
    {
        $user = $request->user();

        // Validasi input
        $attributes = $request->validate([
            'email'    => 'required|email|unique:users,email,' . $user->id,
            'name'     => 'required|string|max:255',
            'phone'    => 'required|digits_between:10,15',
            'about'    => 'nullable|string|max:150',
            'location' => 'nullable|string|max:255',
            'photo'    => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        // Jika ada upload photo baru
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($user->photo && Storage::disk('public')->exists($user->photo)) {
                Storage::disk('public')->delete($user->photo);
            }

            $photo = $request->file('photo');
            $filename = uniqid('profile_') . '.' . $photo->getClientOriginalExtension();
            $path = 'profile-photos/' . $filename;

            // Baca gambar, crop 1:1 dan resize 300x300
            $img = Image::read($photo)->cover(300, 300);

            // Simpan ke storage/app/public/profile-photos
            $img->save(storage_path('app/public/' . $path));

            // Simpan path ke database
            $attributes['photo'] = $path;
        }

        // Update user
        $user->update($attributes);

        return back()->with('status', 'Profile successfully updated.');
    }
}
