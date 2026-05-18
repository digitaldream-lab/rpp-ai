<?php

use App\Http\Controllers\SuperAdminController;
use App\Http\Controllers\GuruController;
use App\Http\Controllers\RppController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

// 1. RUTE SMART HOME
Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// 2. GRUP RUTE MEMBUTUHKAN AUTENTIKASI
Route::middleware(['auth'])->group(function () {
    
    // Jembatan Penentu Role
    Route::get('/dashboard', function () {
        if (Auth::user()->email === 'admin@gmail.com') { 
            return redirect()->route('admin.dashboard');
        }
        return redirect()->route('guru.dashboard');
    })->name('dashboard');

    // Rute Layanan Guru
    Route::get('/guru', [GuruController::class, 'dashboard'])->name('guru.dashboard');
    Route::post('/guru/kelas', [GuruController::class, 'storeKelas'])->name('guru.kelas.store');
    Route::post('/guru/mapel', [GuruController::class, 'storeMapel'])->name('guru.mapel.store');
    Route::post('/guru/materi', [GuruController::class, 'storeMateri'])->name('guru.materi.store');
    
    // Rote Pemrosesan RPP
    Route::post('/rpp/generate', [RppController::class, 'generate'])->name('rpp.generate');
    Route::post('/rpp/generate-image', [RppController::class, 'generateImage'])->name('rpp.image');
    Route::post('/rpp/edit-text', [RppController::class, 'editAiText'])->name('rpp.edit-text');
    Route::post('/rpp/save', [RppController::class, 'saveAndDownload'])->name('rpp.save');

    // Rute Layanan SuperAdmin
    Route::get('/admin', [SuperAdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::post('/admin/4c', [SuperAdminController::class, 'storeFourC'])->name('admin.4c.store');
    Route::post('/admin/dalil', [SuperAdminController::class, 'storeDalil'])->name('admin.dalil.store');

    // Sistem Logout Berbasis GET
    Route::get('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect()->route('login');
    })->name('logout.get');
});

// 3. RUTE AUTHENTICATION BREEZE
require __DIR__.'/auth.php';