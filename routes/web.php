<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\UserManagementController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\FormController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;
use App\Models\Dokumen;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Redirect root ke sign-in
Route::get('/', function () {
    return redirect('sign-in');
})->middleware('guest');

// === Auth routes ===
Route::get('sign-up', [RegisterController::class, 'create'])->middleware('guest')->name('register');
Route::post('sign-up', [RegisterController::class, 'store'])->middleware('guest');

Route::get('sign-in', [SessionsController::class, 'create'])->middleware('guest')->name('login');
Route::post('sign-in', [SessionsController::class, 'store'])->middleware('guest');

Route::post('verify', [SessionsController::class, 'show'])->middleware('guest');
Route::post('reset-password', [SessionsController::class, 'update'])->middleware('guest')->name('password.update');

Route::get('verify', function () {
    return view('sessions.password.verify');
})->middleware('guest')->name('verify');

Route::get('/reset-password/{token}', function ($token) {
    return view('sessions.password.reset', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('sign-out', [SessionsController::class, 'destroy'])->middleware('auth')->name('logout');

// === Google SSO routes ===
Route::get('/auth/google', [GoogleController::class, 'redirect'])
    ->middleware('guest')
    ->name('google.login');

Route::get('/auth/google/callback', [GoogleController::class, 'callback'])
    ->middleware('guest')
    ->name('google.callback');

// === Profile routes ===
Route::get('profile', [ProfileController::class, 'create'])->middleware('auth')->name('profile');
Route::post('user-profile', [ProfileController::class, 'update'])->middleware('auth');


// === Group routes yang butuh login ===
Route::middleware('auth')->group(function () {
    
    // Dashboard
    Route::get('pegawai/dashboard', [PegawaiController::class, 'index'])->name('pegawai-dashboard')->middleware('role:Pegawai');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    //Dokumen
    Route::get('/tables', [DokumenController::class, 'index'])->name('tables', 'dokumen.index');
    Route::get('/dokumen', [DokumenController::class, 'index'])->name('dokumen.index');


    // === Tambahan: preview file PDF private ===
    Route::get('/dokumen/preview/{id}', [DokumenController::class, 'preview'])->name('dokumen.preview');

    // Static pages
    Route::get('billing', fn() => view('pages.billing'))->name('billing');
    Route::get('rtl', fn() => view('pages.rtl'))->name('rtl');
    Route::get('virtual-reality', fn() => view('pages.virtual-reality'))->name('virtual-reality');
    Route::get('notifications', fn() => view('pages.notifications'))->name('notifications');
    Route::get('static-sign-in', fn() => view('pages.static-sign-in'))->name('static-sign-in');
    Route::get('static-sign-up', fn() => view('pages.static-sign-up'))->name('static-sign-up');
    
    // User Management (Admin & Supervisor)
    Route::middleware('role:Admin,Supervisor')->group(function () {
        Route::get('user-management', [UserManagementController::class, 'index'])->name('user-management');
        Route::get('user-management/create', [UserManagementController::class, 'create'])->name('user-management.create');
        Route::post('user-management', [UserManagementController::class, 'store'])->name('user-management.store');
        
        // Monitoring dokumen
        Route::get('/dashboard/filter', [DashboardController::class, 'filter'])->name('monitoring.filter');

        // Edit user
        Route::get('user-management/{user}/edit', [UserManagementController::class, 'edit'])->name('user-management.edit');
        Route::put('user-management/{user}', [UserManagementController::class, 'update'])->name('user-management.update');
        
        // Delete user
        Route::delete('user-management/{user}', [UserManagementController::class, 'destroy'])->name('user-management.destroy');

        // === Tambahan: route untuk form input ke pegawai (buat supervisor dan admin) ===
        Route::get('/form', [FormController::class, 'index'])->name('form.index');
        Route::post('/form', [FormController::class, 'store'])->name('form.store');

        // Route untuk edit dan menghapus JenisDokumen beserta periode terkait
        Route::get('jenis-dokumen/{id}/edit', [FormController::class, 'edit'])->name('jenis-dokumen.edit');
        Route::delete('jenis-dokumen/{id}', [FormController::class, 'destroy'])->name('jenis-dokumen.destroy');
    });
    
    // Contoh halaman profil user
    Route::get('user-profile', fn() => view('pages.laravel-examples.user-profile'))->name('user-profile');
    
    // === Tambahan: route untuk tanda tangan PDF (butuh login) ===
    Route::get('/sign-pdf', [PdfController::class, 'index'])->name('pdf.sign.form');
    Route::post('/sign-pdf', [PdfController::class, 'signPdf'])->name('pdf.sign');
});
