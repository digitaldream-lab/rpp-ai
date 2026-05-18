<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('four_cs', function (Blueprint $table) {
            $table->id();
            $table->enum('kategori', ['Creativity', 'Critical Thinking', 'Communication', 'Collaboration']);
            $table->text('batasan_deskripsi');
            $table->timestamps();
        });
    }
    public function down(): void { 
        Schema::dropIfExists('four_cs'); 
    }
};