<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('materis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mata_pelajaran_id')->constrained()->cascadeOnDelete();
            $table->string('file_path'); // Path berkas PDF yang disimpan
            $table->longText('extracted_text')->nullable(); // Teks hasil pembacaan Spatie PDF
            $table->string('referensi_link')->nullable(); // Referensi link opsional
            $table->timestamps();
        });
    }
    public function down(): void { 
        Schema::dropIfExists('materis'); 
    }
};