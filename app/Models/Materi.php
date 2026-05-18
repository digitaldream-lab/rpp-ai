<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Materi extends Model {
    // Definisi tabel eksplisit untuk menghindari kesalahan pluralisasi Laravel
    protected $table = 'materis';
    protected $guarded = [];

    public function mataPelajaran(): BelongsTo {
        return $this->belongsTo(MataPelajaran::class);
    }
}