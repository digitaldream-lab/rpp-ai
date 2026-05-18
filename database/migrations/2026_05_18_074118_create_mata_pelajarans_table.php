<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('mata_pelajarans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kelas_id')->constrained()->cascadeOnDelete();
            $table->string('nama'); // Contoh: Matematika, IPA, Agama Islam
            $table->boolean('is_agama')->default(false); // Validasi apakah mapel agama
            $table->timestamps();
        });
    }
    public function down(): void { 
        Schema::dropIfExists('mata_pelajarans'); 
    }
};