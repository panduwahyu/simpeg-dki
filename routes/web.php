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
use Illuminate\Http\Request;
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
    Route::get('/dashboard', [MonitoringController::class, 'index'])->name('dashboard')->middleware('role:Admin,Supervisor');
    
    // Dokumen
    Route::get('/tables', [DokumenController::class, 'index'])->name('tables');
    Route::get('/dokumen', [DokumenController::class, 'index'])->name('dokumen.index');

    // Preview file PDF private
    Route::get('/dokumen/preview/{id}', [DokumenController::class, 'preview'])->name('dokumen.preview');

    // Static pages
    Route::get('notifications', fn() => view('pages.notifications'))->name('notifications');
    
    // User Management (Admin & Supervisor)
    Route::middleware('role:Admin,Supervisor')->group(function () {
        Route::get('user-management', [UserManagementController::class, 'index'])->name('user-management');
        Route::get('user-management/create', [UserManagementController::class, 'create'])->name('user-management.create');
        Route::post('user-management', [UserManagementController::class, 'store'])->name('user-management.store');

        // === Route untuk download template Excel ===
        Route::get('/user/template', function () {
            $path = storage_path('app/public/template/users_template.xlsx');
            return response()->download($path, 'template_user.xlsx');
        })->name('user.template');
        
        // Monitoring dokumen
        Route::get('/dashboard/filter', [DashboardController::class, 'filter'])->name('monitoring.filter');
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');
        Route::get('/monitoring/jenis-dokumen/{nama}', [MonitoringController::class, 'getDokumenDetail'])
            ->name('monitoring.dokumen.detail');
        Route::get('/monitoring/data/{namaDokumen}', [MonitoringController::class, 'getMonitoringDataAjax'])
            ->name('monitoring.ajax');
        Route::get('/monitoring/file-path/{user}/{periode}', [MonitoringController::class, 'getFilePath'])
            ->name('monitoring.file-path');
        Route::get('/monitoring/preview/{userId}/{jenisDokumenId}/{periodeId}', [MonitoringController::class, 'previewFile']);

        // Edit user
        Route::get('user-management/{user}/edit', [UserManagementController::class, 'edit'])->name('user-management.edit');
        Route::put('user-management/{user}', [UserManagementController::class, 'update'])->name('user-management.update');
        
        // Delete user
        Route::delete('user-management/{user}', [UserManagementController::class, 'destroy'])->name('user-management.destroy');

        // Form input pegawai
        Route::get('/form', [FormController::class, 'index'])->name('form.index');
        Route::post('/form', [FormController::class, 'store'])->name('form.store');
        Route::get('/form/{id}', [FormController::class, 'edit'])->name('form.edit');
        Route::put('/form/{id}', [FormController::class, 'update'])->name('form.update');

        // Edit & delete JenisDokumen
        Route::get('jenis-dokumen/{id}/edit', [FormController::class, 'edit'])->name('jenis-dokumen.edit');
        Route::put('jenis-dokumen/{jenisDokumen}', [FormController::class, 'update'])->name('jenis-dokumen.update');
        Route::delete('jenis-dokumen/destroy', [FormController::class, 'destroy'])->name('jenis-dokumen.destroy');

        Route::get('/jenis-dokumen/{id}/json', [FormController::class, 'getJenisDokumenJson'])
            ->name('jenis-dokumen.json');

        // Ekspor & impor data user (langsung ke controller, tanpa UsersExport/UsersImport)
        Route::get('user/export', [UserManagementController::class, 'export'])->name('user.export');
        Route::post('user/import', [UserManagementController::class, 'import'])->name('user.import');

        // -----------------------------
        // Tanda tangan PDF Supervisor & Admin
        // -----------------------------
        Route::get('/pdf/supervisoradmin', [PdfController::class, 'indexSupervisorAdmin'])
            ->name('pdf.sign.supervisor');

        Route::post('/pdf/supervisoradmin', [PdfController::class, 'signPdfSupervisor'])
            ->name('pdf.sign.supervisor.submit');

        // -----------------------------
        // AJAX untuk dependent dropdown
        // -----------------------------
        Route::get('/ajax-dokumen/{user}', [PdfController::class, 'getDokumenByUser'])
            ->name('ajax.dokumen');

        Route::get('/ajax-periode/{user}/{dokumen}', [PdfController::class, 'getPeriodeByUserDokumen'])
            ->name('ajax.periode');
    });
    
    // Contoh halaman profil user
    Route::get('user-profile', fn() => view('pages.laravel-examples.user-profile'))->name('user-profile');
    
    // Tanda tangan PDF
    Route::get('/sign-pdf', [PdfController::class, 'index'])->name('pdf.sign.form');
    Route::post('/sign-pdf', [PdfController::class, 'signPdf'])->name('pdf.sign');

    // AJAX untuk dropdown Dokumen pegawai
    Route::get('/pegawai/ajax-dokumen/{userId}', [PdfController::class, 'getDokumenPegawai'])
        ->name('pegawai.ajax.dokumen');

    // AJAX untuk dropdown Periode pegawai
    Route::get('/pegawai/ajax-periode/{userId}/{dokumenId}', [PdfController::class, 'getPeriode'])
        ->name('pegawai.ajax.periode');
});
