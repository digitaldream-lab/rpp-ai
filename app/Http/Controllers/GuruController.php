<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use Smalot\PdfParser\Parser; // <--- Menggunakan parser baru yang murni PHP
use Inertia\Inertia;

class GuruController extends Controller
{
    public function dashboard(Request $request) {
        $userId = $request->user()->id;

        $kelas = Kelas::where('user_id', $userId)->with('mataPelajarans')->get();
        $kelasIds = $kelas->pluck('id');
        $mapels = MataPelajaran::whereIn('kelas_id', $kelasIds)->get();
        $materis = Materi::whereIn('mata_pelajaran_id', $mapels->pluck('id'))->with('mataPelajaran.kelas')->get();

        return Inertia::render('Guru/Dashboard', [
            'kelas' => $kelas,
            'mapels' => $mapels,
            'materis' => $materis
        ]);
    }

    public function storeKelas(Request $request) {
        $request->validate(['nama_jenjang' => 'required|string']);
        $request->user()->kelas()->create(['nama_jenjang' => $request->nama_jenjang]);
        return redirect()->back()->with('success', 'Kelas berhasil dibuat.');
    }

    public function storeMapel(Request $request) {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id', 
            'nama' => 'required|string', 
            'is_agama' => 'boolean'
        ]);
        MataPelajaran::create($request->all());
        return redirect()->back()->with('success', 'Mata Pelajaran berhasil ditambahkan.');
    }

    // A3: UPLOAD DAN EKSTRAK TEKS PDF (DIJAMIN BERHASIL DI WINDOWS)
    public function storeMateri(Request $request) {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id', 
            'file' => 'required|mimes:pdf|max:10000', 
            'referensi_link' => 'nullable|url'
        ]);
        
        $path = $request->file('file')->store('materis', 'public');
        $fullPath = storage_path('app/public/' . $path);
        
        $extractedText = "";
        
        try {
            // Menggunakan Smalot PDF Parser (Tanpa butuh Poppler)
            $pdfParser = new Parser();
            $pdf = $pdfParser->parseFile($fullPath);
            $extractedText = $pdf->getText();
            
            // Membersihkan teks dari spasi kosong berlebih agar ringan dikirim ke AI
            $extractedText = preg_replace('/\s+/', ' ', $extractedText);
            
        } catch (\Exception $e) {
            logger("Smalot PDF extraction failed: " . $e->getMessage());
            $extractedText = "Gagal membaca teks PDF.";
        }

        Materi::create([
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'file_path' => $path,
            'extracted_text' => $extractedText,
            'referensi_link' => $request->referensi_link
        ]);

        return redirect()->back()->with('success', 'Materi berhasil diunggah.');
    }
}