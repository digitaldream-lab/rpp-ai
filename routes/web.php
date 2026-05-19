<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\RppController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Halaman utama otomatis mengarah ke login
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware(['auth'])->group(function () {
    
    // Dashboard Utama Guru
    Route::get('/guru', [GuruController::class, 'dashboard'])->name('guru.dashboard');
    Route::post('/guru/kelas', [GuruController::class, 'storeKelas'])->name('guru.kelas.store');
    Route::post('/guru/mapel', [GuruController::class, 'storeMapel'])->name('guru.mapel.store');
    Route::post('/guru/materi', [GuruController::class, 'storeMateri'])->name('guru.materi.store');
    
    // Logika Pemrosesan RPP
    Route::post('/rpp/generate', [RppController::class, 'generate'])->name('rpp.generate');
    Route::post('/rpp/generate-image', [RppController::class, 'generateImage'])->name('rpp.image');
    Route::post('/rpp/edit-text', [RppController::class, 'editAiText'])->name('rpp.edit-text');
    Route::post('/rpp/save', [RppController::class, 'saveAndDownload'])->name('rpp.save');

    // Dashboard Utama Superadmin
    Route::get('/admin', [SuperAdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/4c', [SuperAdminController::class, 'storeFourC'])->name('admin.4c.store');
    Route::post('/admin/dalil', [SuperAdminController::class, 'storeDalil'])->name('admin.dalil.store');
});

require __DIR__.'/auth.php';