<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        Schema::create('rpps', function (Blueprint $table) {
            $table->id();
            // Menghubungkan RPP dengan user/guru
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            // Judul atau topik RPP
            $table->string('judul');

            // Konten RPP dalam format teks panjang (JSON atau HTML)
            $table->longText('konten');

            // Metadata tambahan (opsional)
            $table->string('mata_pelajaran')->nullable();
            $table->string('tingkat_kelas')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Batalkan migrasi jika terjadi kesalahan.
     */
    public function down(): void
    {
        Schema::dropIfExists('rpps');
    }
};