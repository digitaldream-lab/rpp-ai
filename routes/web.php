<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\RppController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// 1. RUTE SMART HOME
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    
    // RUTE JEMBATAN
    Route::get('/dashboard', function () {
        if (Auth::user()->email === 'admin@gmail.com') { 
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('guru.dashboard');
    })->name('dashboard');

    // ==========================================
    // ALUR GURU (A1, A2, A3, A4)
    // ==========================================
    Route::get('/guru', [GuruController::class, 'dashboard'])->name('guru.dashboard');
    
    // Kelas
    Route::post('/guru/kelas', [GuruController::class, 'storeKelas'])->name('guru.kelas.store');
    Route::put('/guru/kelas/{id}', [GuruController::class, 'updateKelas'])->name('guru.kelas.update');
    Route::delete('/guru/kelas/{id}', [GuruController::class, 'destroyKelas'])->name('guru.kelas.destroy');
    
    // Mata Pelajaran
    Route::post('/guru/mapel', [GuruController::class, 'storeMapel'])->name('guru.mapel.store');
    Route::put('/guru/mapel/{id}', [GuruController::class, 'updateMapel'])->name('guru.mapel.update');
    Route::delete('/guru/mapel/{id}', [GuruController::class, 'destroyMapel'])->name('guru.mapel.destroy');
    
    // Materi
    Route::post('/guru/materi', [GuruController::class, 'storeMateri'])->name('guru.materi.store');
    Route::post('/guru/materi/{id}', [GuruController::class, 'updateMateri'])->name('guru.materi.update'); // Pakai POST untuk upload file Inertia method spoofing
    Route::delete('/guru/materi/{id}', [GuruController::class, 'destroyMateri'])->name('guru.materi.destroy');
    
    // Generate RPP
    Route::post('/rpp/generate', [RppController::class, 'generate'])->name('rpp.generate');
    Route::post('/rpp/generate-image', [RppController::class, 'generateImage'])->name('rpp.image');
    Route::post('/rpp/edit-text', [RppController::class, 'editAiText'])->name('rpp.edit-text');
    Route::post('/rpp/save', [RppController::class, 'saveAndDownload'])->name('rpp.save');

    // ==========================================
    // ALUR SUPERADMIN
    // ==========================================
    Route::get('/admin', [SuperAdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/4c', [SuperAdminController::class, 'storeFourC'])->name('admin.4c.store');
    Route::put('/admin/4c/{id}', [SuperAdminController::class, 'updateFourC'])->name('admin.4c.update');
    Route::delete('/admin/4c/{id}', [SuperAdminController::class, 'destroyFourC'])->name('admin.4c.destroy');
    Route::post('/admin/dalil', [SuperAdminController::class, 'storeDalil'])->name('admin.dalil.store');
    Route::post('/admin/guru', [SuperAdminController::class, 'storeGuru'])->name('admin.guru.store');
    Route::put('/admin/guru/{id}', [SuperAdminController::class, 'updateGuru'])->name('admin.guru.update');
    Route::delete('/admin/guru/{id}', [SuperAdminController::class, 'destroyGuru'])->name('admin.guru.destroy');

    // LOGOUT
    Route::get('/logout', function (\Illuminate\Http\Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout.get');
});

require __DIR__.'/auth.php';