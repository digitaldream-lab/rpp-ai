<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('dalils', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['Al-Quran', 'Hadis']);
            $table->string('gambar_path')->nullable();
            $table->string('referensi'); // Contoh: QS. Al-Baqarah: 151
            $table->text('arti');
            $table->text('deskripsi')->nullable();
            $table->string('keyword'); // Kata kunci pencarian
            $table->timestamps();
        });
    }
    public function down(): void { 
        Schema::dropIfExists('dalils'); 
    }
};