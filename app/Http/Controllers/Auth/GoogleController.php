<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;

class GoogleController extends Controller
{
    // Step 1: Redirect ke Google (scope email + profile)
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->redirect();
    }

    // Step 2: Handle callback dari Google
    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->stateless()->user();

            // Sinkronisasi data user Google ke tabel users
            $user = User::updateOrCreate(
                ['email' => $googleUser->getEmail()], // cari berdasarkan email
                [
                    'name'     => $googleUser->getName(),
                    'photo'    => $googleUser->getAvatar(),      // simpan foto ke kolom photo
                    'password' => bcrypt(Str::random(16)),       // password random
                    'role'     => 'Admin'                       // default role = Pegawai
                ]
            );

            // Login user
            Auth::login($user);

            return redirect()->intended(route('dashboard'));
        } catch (\Exception $e) {
            return redirect()->route('login')
                ->with('error', 'Google login failed: ' . $e->getMessage());
        }
    }
}
