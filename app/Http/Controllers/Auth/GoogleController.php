<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class GoogleController extends Controller
{
    // Step 1: Redirect ke Google
    public function redirect()
    {
        return Socialite::driver('google')->redirect();
    }

    // Step 2: Handle callback dari Google
    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        // Selalu sinkronkan data user Google
        $user = User::updateOrCreate(
            ['email' => $googleUser->getEmail()], // cari berdasarkan email
            [
                'name'     => $googleUser->getName(),
                'password' => bcrypt(Str::random(16)), // supaya field password tidak null
                'role'     => 'Pegawai'               // isi default role kalau perlu
            ]
        );

        Auth::login($user);

        return redirect()->intended(route('dashboard'));
    }
}