<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use Spatie\PdfToText\Pdf;
use Inertia\Inertia;

class GuruController extends Controller
{
    /**
     * Menampilkan Dashboard Guru dengan data Kelas, Mapel, dan Materi.
     */
    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        // Ambil kelas milik guru yang sedang login beserta mapel di dalamnya
        $kelas = Kelas::where('user_id', $userId)
            ->with('mataPelajarans')
            ->get();

        // Ambil semua mapel yang dimiliki guru melalui kelasnya
        $kelasIds = $kelas->pluck('id');
        $mapels = MataPelajaran::whereIn('kelas_id', $kelasIds)->get();

        // Ambil semua materi yang sudah diupload
        $materis = Materi::whereIn('mata_pelajaran_id', $mapels->pluck('id'))
            ->with('mataPelajaran.kelas')
            ->get();

        return Inertia::render('Guru/Dashboard', [
            'kelas' => $kelas,
            'mapels' => $mapels,
            'materis' => $materis,
        ]);
    }

    // A1: Buat Kelas
    public function storeKelas(Request $request) {
        $request->validate([
            'nama_jenjang' => 'required|string|max:50'
        ]);

        $request->user()->kelas()->create([
            'nama_jenjang' => $request->nama_jenjang
        ]);

        return redirect()->back()->with('success', 'Kelas berhasil dibuat!');
    }

    //A1.1 mengelola kelas
    public function updateKelas(Request $request, $id) {
        $request->validate(['nama_jenjang' => 'required']);
        $kelas = \App\Models\Kelas::findOrFail($id);
        $kelas->update(['nama_jenjang' => $request->nama_jenjang]);
        return redirect()->back()->with('success', 'Kelas berhasil diperbarui.');
        }

    public function destroyKelas($id) {
        $kelas = \App\Models\Kelas::findOrFail($id);
        $kelas->delete();
        return redirect()->back()->with('success', 'Kelas berhasil dihapus.');
    }

    // A2: Buat Mapel
    public function storeMapel(Request $request) {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id', 
            'nama' => 'required|string|max:100', 
            'is_agama' => 'required|boolean'
        ]);

        MataPelajaran::create([
            'kelas_id' => $request->kelas_id,
            'nama' => $request->nama,
            'is_agama' => $request->is_agama
        ]);

        return redirect()->back()->with('success', 'Mata Pelajaran berhasil dibuat!');
    }

    // A3: Upload Materi & Ekstrak Teks PDF
    public function storeMateri(Request $request) {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id', 
            'file' => 'required|mimes:pdf|max:10000', 
            'referensi_link' => 'nullable|url'
        ]);
        
        $path = $request->file('file')->store('materis', 'public');
        $fullPath = storage_path('app/public/' . $path);
        
        // Proteksi jika komputer lokal (Windows/Herd) belum terinstall Poppler/pdftotext
        $text = "Teks materi gagal diekstrak otomatis. Silakan isi RPP manual atau pastikan Poppler terinstall.";
        try {
            $text = Pdf::getText($fullPath);
        } catch (\Exception $e) {
            // Tetap simpan record meski ekstraksi PDF gagal agar aplikasi tidak crash
            logger("Spatie PdfToText Error: " . $e->getMessage());
        }

        Materi::create([
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'file_path' => $path,
            'extracted_text' => $text,
            'referensi_link' => $request->referensi_link
        ]);

        return redirect()->back()->with('success', 'Materi berhasil diunggah.');
    }
}
