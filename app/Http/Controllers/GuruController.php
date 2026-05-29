<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kelas;
use App\Models\MataPelajaran;
use App\Models\Materi;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class GuruController extends Controller
{
    /**
     * Menampilkan Dashboard Guru
     */
    public function dashboard(Request $request)
    {
        $userId = $request->user()->id;

        $kelas = Kelas::where('user_id', $userId)->with('mataPelajarans')->orderBy('created_at', 'desc')->get();
        $kelasIds = $kelas->pluck('id');
        $mapels = MataPelajaran::whereIn('kelas_id', $kelasIds)->orderBy('created_at', 'desc')->get();
        $materis = Materi::whereIn('mata_pelajaran_id', $mapels->pluck('id'))->with('mataPelajaran.kelas')->orderBy('created_at', 'desc')->get();

        return Inertia::render('Guru/Dashboard', [
            'kelas' => $kelas,
            'mapels' => $mapels,
            'materis' => $materis,
        ]);
    }

    // ==========================================
    // KELOLA KELAS
    // ==========================================
    public function storeKelas(Request $request) {
        $request->validate(['nama_jenjang' => 'required|string|max:50']);
        $request->user()->kelas()->create(['nama_jenjang' => $request->nama_jenjang]);
        return redirect()->back()->with('success', 'Kelas berhasil dibuat!');
    }

    public function updateKelas(Request $request, $id) {
        $request->validate(['nama_jenjang' => 'required|string|max:50']);
        Kelas::where('id', $id)->where('user_id', $request->user()->id)->update(['nama_jenjang' => $request->nama_jenjang]);
        return redirect()->back()->with('success', 'Kelas diperbarui!');
    }

    public function destroyKelas(Request $request, $id) {
        Kelas::where('id', $id)->where('user_id', $request->user()->id)->delete();
        return redirect()->back()->with('success', 'Kelas dihapus!');
    }

    // ==========================================
    // KELOLA MAPEL
    // ==========================================
    public function storeMapel(Request $request) {
        $request->validate([
            'kelas_id' => 'required|exists:kelas,id', 
            'nama' => 'required|string|max:100', 
            'is_agama' => 'required|boolean'
        ]);
        MataPelajaran::create($request->only(['kelas_id', 'nama', 'is_agama']));
        return redirect()->back()->with('success', 'Mata Pelajaran berhasil dibuat!');
    }

    public function updateMapel(Request $request, $id) {
        $request->validate([
            'nama' => 'required|string|max:100', 
            'is_agama' => 'required|boolean'
        ]);
        MataPelajaran::findOrFail($id)->update($request->only(['nama', 'is_agama']));
        return redirect()->back()->with('success', 'Mata Pelajaran diperbarui!');
    }

    public function destroyMapel($id) {
        MataPelajaran::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Mata Pelajaran dihapus!');
    }

    // ==========================================
    // FUNGSI BANTUAN: EKSTRAK TEKS DARI FILE
    // ==========================================
    private function extractTextFromFile($fullPath, $extension) {
        $extracted = "";
        try {
            if ($extension === 'pdf') {
                // Coba gunakan Smalot atau Spatie jika tersedia
                if (class_exists(\Smalot\PdfParser\Parser::class)) {
                    $parser = new \Smalot\PdfParser\Parser();
                    $pdf = $parser->parseFile($fullPath);
                    $extracted = $pdf->getText();
                } elseif (class_exists(\Spatie\PdfToText\Pdf::class)) {
                    $extracted = \Spatie\PdfToText\Pdf::getText($fullPath);
                }
            } elseif ($extension === 'docx') {
                $zip = new ZipArchive;
                if ($zip->open($fullPath) === true) {
                    $content = $zip->getFromName('word/document.xml');
                    if ($content !== false) {
                        $content = str_replace('</w:p>', " \n ", $content);
                        $extracted = strip_tags($content);
                    }
                    $zip->close();
                }
            } elseif ($extension === 'txt') {
                $extracted = file_get_contents($fullPath);
            }
        } catch (\Exception $e) {
            logger("Ekstraksi File Gagal: " . $e->getMessage());
        }
        
        return preg_replace('/\s+/', ' ', trim($extracted));
    }

    // ==========================================
    // KELOLA MATERI (UPLOAD FILE & TEKS MANUAL)
    // ==========================================
    public function storeMateri(Request $request) {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id', 
            'file' => 'nullable|mimes:pdf,doc,docx,txt|max:10000', 
            'teks_manual' => 'nullable|string',
            'referensi_link' => 'nullable|url'
        ]);
        
        $path = null;
        $extractedText = "";
        $sumberTeks = "Tidak ada materi";

        // 1. Jika ada file yang diunggah
        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->store('materis', 'public');
            $fullPath = storage_path('app/public/' . $path);
            
            $extractedText = $this->extractTextFromFile($fullPath, $extension);
            $sumberTeks = "File " . strtoupper($extension);
        }

        // 2. Jika ekstrak file gagal (teks terlalu pendek) ATAU file tidak diunggah,
        // Gunakan input teks manual sebagai penyelamat (fallback)
        if (strlen($extractedText) < 20) {
            if (!empty($request->teks_manual)) {
                $extractedText = trim($request->teks_manual);
                $sumberTeks = "Teks Manual";
            } else {
                $extractedText = "Materi tidak terbaca dan teks manual kosong. AI akan membuatkan RPP berdasarkan pengetahuan kurikulum umum.";
                $sumberTeks = "Pengetahuan Umum AI";
            }
        }

        $charCount = strlen($extractedText);

        Materi::create([
            'mata_pelajaran_id' => $request->mata_pelajaran_id,
            'file_path' => $path,
            'extracted_text' => $extractedText,
            'referensi_link' => $request->referensi_link
        ]);

        return redirect()->back()->with('success', "Materi berhasil disiapkan. (Sumber: {$sumberTeks} | Terbaca: {$charCount} karakter)");
    }

    public function updateMateri(Request $request, $id) {
        $materi = Materi::findOrFail($id);
        
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'referensi_link' => 'nullable|url',
            'file' => 'nullable|mimes:pdf,doc,docx,txt|max:10000',
            'teks_manual' => 'nullable|string'
        ]);

        $materi->mata_pelajaran_id = $request->mata_pelajaran_id;
        $materi->referensi_link = $request->referensi_link;

        $extractedText = $materi->extracted_text; 
        $sumberTeks = "Data Lama";

        // Jika user upload file baru saat proses edit
        if ($request->hasFile('file')) {
            // Hapus file lama
            if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
                Storage::disk('public')->delete($materi->file_path);
            }
            
            $file = $request->file('file');
            $extension = strtolower($file->getClientOriginalExtension());
            $path = $file->store('materis', 'public');
            $fullPath = storage_path('app/public/' . $path);
            $materi->file_path = $path;
            
            $extractedText = $this->extractTextFromFile($fullPath, $extension);
            $sumberTeks = "File Baru " . strtoupper($extension);
        }

        // Cek lagi apakah butuh fallback ke teks manual
        if (strlen($extractedText) < 20 && !empty($request->teks_manual)) {
            $extractedText = trim($request->teks_manual);
            $sumberTeks = "Teks Manual Baru";
        }

        $materi->extracted_text = $extractedText;
        $materi->save();
        
        $charCount = strlen($extractedText);
        return redirect()->back()->with('success', "Materi berhasil diperbarui. (Sumber: {$sumberTeks} | Terbaca: {$charCount} karakter)");
    }

    public function destroyMateri($id) {
        $materi = Materi::findOrFail($id);
        
        // Hapus file fisiknya dari penyimpanan server agar tidak memenuhi harddisk
        if ($materi->file_path && Storage::disk('public')->exists($materi->file_path)) {
            Storage::disk('public')->delete($materi->file_path);
        }
        
        $materi->delete();
        
        return redirect()->back()->with('success', 'Materi berhasil dihapus.');
    }
}