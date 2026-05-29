<?php

namespace App\Services;

use App\Models\MataPelajaran;
use App\Models\FourC;
use App\Models\Dalil;
use Illuminate\Support\Facades\Http;

class RppAiService
{
    public function generateRpp(MataPelajaran $mapel, int $pertemuan, string $sumber, string $materiText = '')
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            throw new \Exception("GROQ_API_KEY belum dikonfigurasi di file .env Anda.");
        }

        // 1. PEMBERSIHAN TEKS (Sangat Penting!)
        // Menghapus karakter-karakter aneh/corrupt hasil ektraksi PDF yang gagal
        $materiText = preg_replace('/[^\p{L}\p{N}\s[:punct:]]/u', '', $materiText);
        $materiText = trim($materiText);

        // 2. CEK VALIDITAS TEKS
        if (in_array($sumber, ['materi', 'keduanya']) && strlen($materiText) < 150) {
            throw new \Exception("GAGAL: Teks materi dari PDF Anda kosong, berisi gambar scan, atau format font PDF tidak didukung. AI tidak dapat memprosesnya.");
        }

        $fourC = FourC::all()->pluck('batasan_deskripsi')->implode(' | ');

        $dalilContext = "";
        if ($mapel->is_agama) {
            $dalils = Dalil::inRandomOrder()->limit(3)->get();
            foreach ($dalils as $d) {
                $dalilContext .= "- [{$d->referensi}]: {$d->arti}\n";
            }
        }

        // 3. PROMPT ANTI MENGARANG BEBAS
        $systemPrompt = "Anda adalah Asisten Pembuat Kurikulum RPP Profesional. Anda disetel pada mode SANGAT KETAT (STRICT RAG).\n";
        $systemPrompt .= "Tugas: Buat RPP untuk {$pertemuan} pertemuan berdasarkan REFERENSI MUTLAK yang diberikan.\n\n";

        if ($mapel->is_agama) {
            $systemPrompt .= "ATURAN AGAMA: Anda HANYA diizinkan menyisipkan dalil berikut ini ke dalam RPP. DILARANG KERAS mencari/menggunakan dalil lain:\n<DAFTAR_DALIL>\n{$dalilContext}</DAFTAR_DALIL>\n\n";
        }

        if ($sumber === 'materi' || $sumber === 'keduanya') {
            // Potong teks agar tidak overload
            $materiText = substr($materiText, 0, 15000);
            
            $systemPrompt .= "ATURAN MATERI MUTLAK: Anda WAJIB menyusun konten RPP (Metode, Media, LKPD, Evaluasi) HANYA berdasarkan informasi yang ada di dalam <TEKS_REFERENSI> di bawah ini.\n";
            $systemPrompt .= "SANKSI: Jika <TEKS_REFERENSI> tidak mengandung materi pelajaran yang jelas, atau Anda tidak menemukan bahan yang cocok, Anda DILARANG MENGARANG! Isi semua jawaban dengan kalimat 'MATERI PDF TIDAK RELEVAN ATAU RUSAK'.\n\n";
            $systemPrompt .= "<TEKS_REFERENSI>\n{$materiText}\n</TEKS_REFERENSI>\n\n";
        }

        $systemPrompt .= "ATURAN 4C: Pastikan metode dan LKPD mengandung unsur 4C berikut: {$fourC}.\n";

        // 4. Format Output
        $userPrompt = "Berikan output WAJIB berupa JSON MURNI dengan tepat 4 kunci berikut:\n" .
                      "1. 'metode' (String paragraf narasi)\n" .
                      "2. 'media' (String paragraf narasi)\n" .
                      "3. 'lkpd' (String paragraf narasi)\n" .
                      "4. 'evaluasi' (String paragraf narasi)\n\n" .
                      "PENTING: Nilai harus berupa String paragraf biasa. JANGAN gunakan array, JSON bersarang, atau formatting markdown.";

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'response_format' => ['type' => 'json_object'],
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $userPrompt],
            ],
            // 5. TEMPERATURE 0.1: Mengubah AI menjadi robot kaku yang hanya bisa membaca/merangkum (tidak bisa mengarang cerita)
            'temperature' => 0.1, 
        ]);

        if ($response->failed()) {
            throw new \Exception("API Error: " . $response->body());
        }

        $result = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? '{}';

        return json_decode($content, true);
    }

    public function generateImage(string $prompt)
    {
        $cleanedPrompt = urlencode("educational illustration, flat 2d vector, child friendly, pastel colors, clean design: " . $prompt);
        $randomSeed = rand(1, 99999);
        return "https://image.pollinations.ai/prompt/{$cleanedPrompt}?width=1024&height=1024&nologo=true&seed={$randomSeed}";
    }

    public function editText(string $text, string $instruction)
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            throw new \Exception("GROQ_API_KEY belum dikonfigurasi di file .env.");
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah asisten editor RPP. ' . $instruction . '. Balas dengan teks perbaikannya saja.'],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.5,
        ]);

        if ($response->failed()) {
            throw new \Exception("API Error: " . $response->body());
        }

        $result = $response->json();
        return $result['choices'][0]['message']['content'] ?? '';
    }
}