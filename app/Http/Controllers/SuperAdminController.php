<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use App\Models\FourC;
use App\Models\Dalil;
use App\Models\User;
use Inertia\Inertia;

class SuperAdminController extends Controller
{
    public function dashboard()
    {
        // Ambil semua user kecuali akun admin utama
        $gurus = User::where('email', '!=', 'admin@gmail.com')->orderBy('created_at', 'desc')->get();

        return Inertia::render('SuperAdmin/Dashboard', [
            'fourCs' => FourC::all(),
            'dalils' => Dalil::all(),
            'gurus' => $gurus
        ]);
    }

    // ==========================================
    // LOGIKA KELOLA GURU
    // ==========================================

    public function storeGuru(Request $request) {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'jabatan' => 'nullable|string|max:100',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'jabatan' => $request->jabatan ?? 'Guru Mata Pelajaran',
        ]);

        return redirect()->back()->with('success', 'Akun Guru berhasil didaftarkan.');
    }

    public function updateGuru(Request $request, $id) {
        $guru = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($guru->id)],
            'password' => 'nullable|string|min:8',
            'jabatan' => 'nullable|string|max:100',
        ]);

        $guru->name = $request->name;
        $guru->email = $request->email;
        $guru->jabatan = $request->jabatan ?? 'Guru Mata Pelajaran';
        
        if ($request->filled('password')) {
            $guru->password = Hash::make($request->password);
        }
        
        $guru->save();

        return redirect()->back()->with('success', 'Data Guru berhasil diperbarui.');
    }

    public function destroyGuru($id) {
        $guru = User::findOrFail($id);
        $guru->delete();
        
        return redirect()->back()->with('success', 'Akun Guru berhasil dihapus.');
    }

    // ==========================================
    // LOGIKA BATASAN 4C
    // ==========================================

    public function storeFourC(Request $request) {
        $request->validate([
            'kategori' => 'required|string', 
            'batasan_deskripsi' => 'required|string'
        ]);
        
        FourC::create($request->all());
        return redirect()->back()->with('success', 'Batasan 4C berhasil disimpan.');
    }

    // FUNGSI BARU: Update Batasan 4C
    public function updateFourC(Request $request, $id) {
        $request->validate([
            'batasan_deskripsi' => 'required|string'
        ]);

        $fourC = FourC::findOrFail($id);
        $fourC->update([
            'batasan_deskripsi' => $request->batasan_deskripsi
        ]);

        return redirect()->back()->with('success', 'Batasan 4C berhasil diperbarui.');
    }

    // FUNGSI BARU: Hapus Batasan 4C
    public function destroyFourC($id) {
        $fourC = FourC::findOrFail($id);
        $fourC->delete();

        return redirect()->back()->with('success', 'Batasan 4C berhasil dihapus.');
    }

    // ==========================================
    // LOGIKA KELOLA DALIL
    // ==========================================

    public function storeDalil(Request $request) {
        $data = $request->validate([
            'kategori' => 'required',
            'referensi' => 'required',
            'arti' => 'required',
            'deskripsi' => 'nullable',
            'keyword' => 'required',
            'gambar' => 'nullable|image' 
        ]);

        if ($request->hasFile('gambar')) {
            $data['gambar_path'] = $request->file('gambar')->store('dalils', 'public');
        }

        unset($data['gambar']);

        Dalil::create($data);
        
        return redirect()->back()->with('success', 'Dalil berhasil disimpan.');
    }
}