<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TryoutAttempt extends Model
{
    use HasFactory;
    protected $guarded = [];

    // 1. Relasi ke Tryout (untuk ambil judul/mapel)
    public function tryout()
    {
        return $this->belongsTo(Tryout::class);
    }

    // 2. TAMBAHKAN INI: Relasi ke User (untuk ambil nama siswa)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}