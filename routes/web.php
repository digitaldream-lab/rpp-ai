<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\RppController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Web Routes - Aplikasi RPP Berbasis AI
|--------------------------------------------------------------------------
*/

// 1. RUTE SMART HOME (Dapat diakses publik)
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {

    // TAMBAHKAN RUTE JEMBATAN INI UNTUK MENGATASI ERROR ROUTE NOT FOUND
    Route::get('/dashboard', function () {
        // Jika yang login adalah admin, arahkan ke dashboard admin
        if (Auth::user()->email === 'admin@gmail.com') {
            return redirect()->route('admin.dashboard');
        }
        // Selain admin (berarti guru), arahkan ke dashboard guru
        return redirect()->route('guru.dashboard');
    })->name('dashboard');

    // Dashboard Utama Guru
    Route::get('/guru', [GuruController::class, 'dashboard'])->name('guru.dashboard');
    Route::post('/guru/kelas', [GuruController::class, 'storeKelas'])->name('guru.kelas.store');

    // A2: Buat Mata Pelajaran
    Route::post('/guru/mapel', [GuruController::class, 'storeMapel'])->name('guru.mapel.store');

    // A3: Upload, Update & Hapus Materi PDF
    Route::post('/guru/materi', [GuruController::class, 'storeMateri'])->name('guru.materi.store');
    Route::put('/guru/materi/{id}', [GuruController::class, 'updateMateri'])->name('guru.materi.update');
    Route::delete('/guru/materi/{id}', [GuruController::class, 'destroyMateri'])->name('guru.materi.destroy');

    // A4: AI Generation & Online Editor
    Route::post('/rpp/generate', [RppController::class, 'generate'])->name('rpp.generate');
    Route::post('/rpp/generate-image', [RppController::class, 'generateImage'])->name('rpp.image');
    Route::post('/rpp/edit-text', [RppController::class, 'editAiText'])->name('rpp.edit-text');
    Route::post('/rpp/save', [RppController::class, 'saveAndDownload'])->name('rpp.save');

    // ==========================================
    // ALUR SUPERADMIN (B1, B2)
    // ==========================================

    // Tampilan Dashboard Superadmin
    Route::get('/admin', [SuperAdminController::class, 'dashboard'])->name('admin.dashboard');

    // B1: Buat Batasan 4C
    Route::post('/admin/4c', [SuperAdminController::class, 'storeFourC'])->name('admin.4c.store');
    Route::put('/admin/4c/{id}', [SuperAdminController::class, 'updateFourC'])->name('admin.4c.update');
    Route::delete('/admin/4c/{id}', [SuperAdminController::class, 'destroyFourC'])->name('admin.4c.destroy');

    // B2: Kelola Database Dalil
    Route::post('/admin/dalil', [SuperAdminController::class, 'storeDalil'])->name('admin.dalil.store');

    // Kelola Akun Guru oleh Admin
    Route::post('/admin/guru', [SuperAdminController::class, 'storeGuru'])->name('admin.guru.store');
    Route::put('/admin/guru/{id}', [SuperAdminController::class, 'updateGuru'])->name('admin.guru.update');
    Route::delete('/admin/guru/{id}', [SuperAdminController::class, 'destroyGuru'])->name('admin.guru.destroy');

    // ==========================================
    // SISTEM LOGOUT AMAN
    // ==========================================
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout.get');
});

// 3. RUTE AUTHENTICATION BREEZE (Login, Register, Logout POST, dll)
require __DIR__ . '/auth.php';