<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Password;
use App\Models\User;

class SessionsController extends Controller
{
    /**
     * Tampilkan halaman login
     */
    public function create()
    {
        return view('sessions.create');
    }

    /**
     * Proses login
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Ambil status Remember Me dari checkbox
        $remember = $request->has('rememberMe');

        // Login dengan Remember Me jika dicentang
        if (! auth()->attempt($credentials, $remember)) {
            throw ValidationException::withMessages([
                'email' => 'Your provided credentials could not be verified.'
            ]);
        }

        // Regenerate session
        $request->session()->regenerate();

        // Atur durasi Remember Me 14 hari jika dicentang
        if ($remember) {
            $rememberDuration = 20160; // menit = 14 hari
            cookie()->queue(
                auth()->guard()->getRecallerName(),
                cookie()->get(auth()->guard()->getRecallerName()),
                $rememberDuration
            );
        }

        return redirect('/dashboard');
    }

    /**
     * Kirim link reset password
     */
    public function show(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors(['email' => __($status)]);
    }

    /**
     * Proses reset password
     */
    public function update(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, $password) {
                // Hash password sebelum disimpan
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status)]]);
    }

    /**
     * Logout user
     */
    public function destroy(Request $request)
    {
        auth()->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/sign-in');
    }
}