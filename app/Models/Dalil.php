<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dalil extends Model {
    // Definisi tabel eksplisit untuk menghindari kesalahan pluralisasi Laravel
    protected $table = 'dalils';
    protected $guarded = [];
}