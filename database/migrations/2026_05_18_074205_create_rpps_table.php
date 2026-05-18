<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rpps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mata_pelajaran_id')->constrained()->cascadeOnDelete();
            $table->integer('jumlah_pertemuan');
            $table->enum('sumber_materi', ['materi', 'ai', 'keduanya']);
            $table->json('content_json')->nullable(); // Struktur teks Editor.js
            $table->string('pdf_path')->nullable(); // Path hasil ekspor PDF
            $table->timestamps();
        });
    }
    public function down(): void { 
        Schema::dropIfExists('rpps'); 
    }
};