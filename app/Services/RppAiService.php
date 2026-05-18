<?php

namespace App\Services;

use App\Models\MataPelajaran;
use App\Models\FourC;
use App\Models\Dalil;
use Illuminate\Support\Facades\Http;

class RppAiService
{
    // A4: Generate Draf RPP Menggunakan Groq API (LLaMA-3.3)
    public function generateRpp(MataPelajaran $mapel, int $pertemuan, string $sumber, string $materiText = '')
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            throw new \Exception("GROQ_API_KEY belum dikonfigurasi di file .env Anda.");
        }

        $fourC = FourC::all()->pluck('batasan_deskripsi')->implode(' | ');

        $dalilContext = "";
        if ($mapel->is_agama) {
            $dalils = Dalil::inRandomOrder()->limit(3)->get();
            foreach ($dalils as $d) {
                $dalilContext .= "Dalil {$d->referensi}: '{$d->arti}'. ";
            }
        }

        // PROMPT AWAL
        $systemPrompt = "Anda adalah Pakar Kurikulum Pendidikan Indonesia. Buatkan Rencana Pelaksanaan Pembelajaran (RPP) untuk {$pertemuan} pertemuan. " .
                        "Terapkan batasan 4C berikut: {$fourC}. ";
        
        if ($mapel->is_agama) {
            $systemPrompt .= "PENTING: Ini mata pelajaran Agama. Anda HANYA boleh menggunakan dalil resmi berikut: {$dalilContext}. Jangan mengarang dalil lain.";
        }
        
        // PROMPT KETAT UNTUK MATERI PDF
        if ($sumber === 'materi' || $sumber === 'keduanya') {
            $materiText = substr($materiText, 0, 15000); // Batasi panjang karakter agar API tidak overload
            $systemPrompt .= "\n\nSANGAT PENTING: Anda WAJIB menyusun RPP ini secara spesifik HANYA berdasarkan teks materi referensi berikut. DILARANG KERAS mengarang materi umum di luar teks ini.\n";
            $systemPrompt .= "=== TEKS REFERENSI MATERI ===\n{$materiText}\n=============================\n";
        }

        $userPrompt = "Hasilkan RPP terstruktur dalam format JSON. WAJIB gunakan 4 kunci (keys) utama ini saja: 'metode', 'media', 'lkpd', 'evaluasi'.\n" .
                      "ATURAN: Nilai (value) dari setiap kunci WAJIB berupa TEKS STRING (paragraf panjang naratif), JANGAN gunakan array, JANGAN gunakan nested object.";

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
            'temperature' => 0.5, // Diturunkan agar AI lebih patuh pada materi dan tidak halusinasi
        ]);

        if ($response->failed()) {
            throw new \Exception("Gagal menghubungi API Groq: " . $response->body());
        }

        $result = $response->json();
        $content = $result['choices'][0]['message']['content'] ?? '{}';

        return json_decode($content, true);
    }

    // A4: Generate Gambar Ilustrasi Media Pembelajaran
    public function generateImage(string $prompt)
    {
        $cleanedPrompt = urlencode("educational illustration, flat 2d vector, child friendly, pastel colors, clean design: " . $prompt);
        $randomSeed = rand(1, 99999);
        // URL dibersihkan dari format markdown
        return "[https://image.pollinations.ai/prompt/](https://image.pollinations.ai/prompt/){$cleanedPrompt}?width=1024&height=1024&nologo=true&seed={$randomSeed}";
    }

    // A4: Edit AI Melalui Sidebar
    public function editText(string $text, string $instruction)
    {
        $apiKey = env('GROQ_API_KEY');

        if (!$apiKey) {
            throw new \Exception("GROQ_API_KEY belum dikonfigurasi di file .env Anda.");
        }

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->post('https://api.groq.com/openai/v1/chat/completions', [
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                ['role' => 'system', 'content' => 'Anda adalah asisten editor RPP profesional. Tugas Anda adalah: ' . $instruction . '. Balas langsung dengan hasil teks perbaikannya saja tanpa ada komentar pembuka atau penutup.'],
                ['role' => 'user', 'content' => $text],
            ],
            'temperature' => 0.6,
        ]);

        if ($response->failed()) {
            throw new \Exception("Gagal menghubungi API Groq: " . $response->body());
        }

        $result = $response->json();
        return $result['choices'][0]['message']['content'] ?? '';
    }
}