<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
{
    Schema::table('dalils', function (Blueprint $table) {
        $table->string('gambar_path')->nullable(); // Menambahkan kolom untuk menyimpan lokasi file gambar
    });
}

public function down(): void
{
    Schema::table('dalils', function (Blueprint $table) {
        $table->dropColumn('gambar_path');
    });
}
};
