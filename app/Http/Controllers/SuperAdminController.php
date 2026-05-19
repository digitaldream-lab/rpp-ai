<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\FourC;
use App\Models\Dalil;
use Inertia\Inertia;

class SuperAdminController extends Controller
{
    /**
     * Menampilkan Dashboard Pengaturan Superadmin.
     */
    public function dashboard()
    {
        return Inertia::render('SuperAdmin/Dashboard', [
            'fourCs' => FourC::all(),
            'dalils' => Dalil::all()
        ]);
    }

    // B1: Buat Batasan 4C
    public function storeFourC(Request $request) {
        $request->validate([
            'kategori' => 'required|in:Creativity,Critical Thinking,Communication,Collaboration', 
            'batasan_deskripsi' => 'required|string'
        ]);
        
        FourC::create($request->all());
        return redirect()->back()->with('success', 'Batasan 4C berhasil disimpan.');
    }

    // B2: Kelola Dalil
    public function storeDalil(Request $request) {
        $data = $request->validate([
            'kategori' => 'required|in:Al-Quran,Hadis', 
            'referensi' => 'required|string|max:150', 
            'arti' => 'required|string', 
            'deskripsi' => 'nullable|string', 
            'keyword' => 'required|string|max:100',
            'gambar' => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar_path'] = $request->file('gambar')->store('dalils', 'public');
        }

        Dalil::create($data);
        return redirect()->back()->with('success', 'Dalil berhasil disimpan.');
    }
}