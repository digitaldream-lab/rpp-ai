<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model {
    // Definisi tabel eksplisit untuk menghindari kesalahan pluralisasi Laravel
    protected $table = 'kelas';
    protected $guarded = [];

    public function user(): BelongsTo { 
        return $this->belongsTo(User::class); 
    }
    public function mataPelajarans(): HasMany { 
        return $this->hasMany(MataPelajaran::class); 
    }
}
