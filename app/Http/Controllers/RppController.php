<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use App\Models\MataPelajaran;
use App\Models\Rpp;
use App\Services\RppAiService;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class RppController extends Controller
{
    protected $aiService;
    
    public function __construct(RppAiService $aiService) {
        $this->aiService = $aiService;
    }

    public function generate(Request $request) {
        $request->validate([
            'mata_pelajaran_id' => 'required|exists:mata_pelajarans,id',
            'jumlah_pertemuan' => 'required|integer|min:1',
            'sumber_materi' => 'required|in:materi,ai,keduanya'
        ]);

        $mapel = MataPelajaran::with('materis')->find($request->mata_pelajaran_id);
        
        $materiText = "";
        if (in_array($request->sumber_materi, ['materi', 'keduanya'])) {
            $materiText = $mapel->materis->pluck('extracted_text')->implode("\n\n");
        }

        $rppData = $this->aiService->generateRpp($mapel, $request->jumlah_pertemuan, $request->sumber_materi, $materiText);

        $parseAiText = function ($data) {
            if (is_string($data)) return $data;
            if (is_array($data) || is_object($data)) {
                $flattened = [];
                array_walk_recursive($data, function($item) use (&$flattened) { 
                    $flattened[] = $item; 
                });
                return implode('. ', $flattened);
            }
            return 'Data tidak terbaca oleh sistem.';
        };

        $rpp = Rpp::create([
            'user_id' => Auth::id(),
            'mata_pelajaran_id' => $mapel->id,
            'jumlah_pertemuan' => $request->jumlah_pertemuan,
            'sumber_materi' => $request->sumber_materi,
            'content_json' => json_encode([
                'blocks' => [
                    ['type' => 'header', 'data' => ['text' => 'RENCANA PELAKSANAAN PEMBELAJARAN (RPP)', 'level' => 1]],
                    ['type' => 'header', 'data' => ['text' => 'A. Metode Pembelajaran', 'level' => 2]],
                    ['type' => 'paragraph', 'data' => ['text' => $parseAiText($rppData['metode'] ?? '')]],
                    ['type' => 'header', 'data' => ['text' => 'B. Media Pembelajaran', 'level' => 2]],
                    ['type' => 'paragraph', 'data' => ['text' => $parseAiText($rppData['media'] ?? '')]],
                    ['type' => 'header', 'data' => ['text' => 'C. Lembar Kerja Peserta Didik (LKPD)', 'level' => 2]],
                    ['type' => 'paragraph', 'data' => ['text' => $parseAiText($rppData['lkpd'] ?? '')]],
                    ['type' => 'header', 'data' => ['text' => 'D. Evaluasi Pembelajaran & Status 4C', 'level' => 2]],
                    ['type' => 'paragraph', 'data' => ['text' => $parseAiText($rppData['evaluasi'] ?? '')]]
                ]
            ]) 
        ]);

        return Inertia::render('Guru/EditorRpp', [
            'rppId' => $rpp->id, 
            'initialData' => json_decode($rpp->content_json, true)
        ]);
    }

    // A4: Generate Gambar Ilustrasi (PERBAIKAN BASE64 AGAR 100% MUNCUL)
    public function generateImage(Request $request) {
        $url = $this->aiService->generateImage($request->prompt);
        
        try {
            // Bypass SSL Error di lokal (Herd) & Perpanjang waktu tunggu (Timeout)
            $response = Http::withoutVerifying()->timeout(30)->get($url);
            if ($response->successful()) {
                $base64 = 'data:image/jpeg;base64,' . base64_encode($response->body());
                return response()->json(['image_url' => $base64]);
            }
        } catch (\Exception $e) {
            // Jika gagal didownload, fallback ke URL asli
        }

        return response()->json(['image_url' => $url]);
    }

    public function editAiText(Request $request) {
        $result = $this->aiService->editText($request->text, $request->instruction);
        return response()->json(['result' => $result]);
    }

    public function saveAndDownload(Request $request) {
        $rpp = Rpp::findOrFail($request->rpp_id);
        $rpp->update(['content_json' => json_encode($request->content_json)]);

        $htmlContent = "";
        foreach ($request->content_json['blocks'] as $block) {
            if ($block['type'] === 'paragraph') {
                $htmlContent .= "<p>{$block['data']['text']}</p>";
            }
            if ($block['type'] === 'header') {
                $level = $block['data']['level'] ?? 2;
                $htmlContent .= "<h{$level}>{$block['data']['text']}</h{$level}>";
            }
            if ($block['type'] === 'image') {
                $htmlContent .= "<p style='text-align:center;'><img src='{$block['data']['file']['url']}' style='max-width:100%; height:auto;' /></p>";
            }
            if ($block['type'] === 'list') {
                $style = ($block['data']['style'] ?? 'unordered') === 'ordered' ? 'ol' : 'ul';
                $htmlContent .= "<{$style}>";
                foreach ($block['data']['items'] as $item) {
                    $htmlContent .= "<li>{$item}</li>";
                }
                $htmlContent .= "</{$style}>";
            }
        }

        $pdf = Pdf::loadHTML("
            <html>
            <head>
                <style>
                    body { font-family: sans-serif; line-height: 1.6; color: #2d3748; padding: 20px; }
                    h1 { color: #1a365d; text-align: center; font-size: 22px; margin-bottom: 25px; text-transform: uppercase; border-bottom: 2px solid #1a365d; padding-bottom: 10px; }
                    h2 { color: #2b6cb0; font-size: 16px; margin-top: 25px; border-left: 4px solid #2b6cb0; padding-left: 8px; }
                    p { text-align: justify; margin-bottom: 12px; }
                    ul, ol { padding-left: 20px; margin-bottom: 12px; }
                    li { margin-bottom: 4px; }
                </style>
            </head>
            <body>
                {$htmlContent}
            </body>
            </html>
        ");
        
        $fileName = 'RPP_AI_' . $rpp->id . '.pdf';
        $pdf->save(storage_path('app/public/rpp/' . $fileName));

        return response()->json(['pdf_url' => asset('storage/rpp/' . $fileName)]);
    }
}