<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FourC extends Model {
    // Definisi tabel eksplisit secara paksa untuk mencocokkan 'four_cs'
    protected $table = 'four_cs';
    protected $guarded = [];
}
