<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MataPelajaran extends Model {
    // Definisi tabel eksplisit untuk menghindari kesalahan pluralisasi Laravel
    protected $table = 'mata_pelajarans';
    protected $guarded = [];

    public function kelas(): BelongsTo { 
        return $this->belongsTo(Kelas::class); 
    }
    public function materis(): HasMany { 
        return $this->hasMany(Materi::class); 
    }
}
