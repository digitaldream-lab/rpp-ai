<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Rpp extends Model {
    // Definisi tabel eksplisit untuk menghindari kesalahan pluralisasi Laravel
    protected $table = 'rpps';
    protected $guarded = [];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    public function mataPelajaran(): BelongsTo {
        return $this->belongsTo(MataPelajaran::class);
    }
}
