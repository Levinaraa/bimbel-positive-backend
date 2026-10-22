<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tryout extends Model
{
    use HasFactory;

    /**
     * Menentukan kolom mana saja yang boleh diisi secara massal (Mass Assignment).
     * Pastikan 'start_time' dan 'end_time' ada di sini.
     */
    protected $fillable = [
        'title',
        'subject',
        'duration',
        'total_questions',
        'status',
        'start_time', // <--- Kolom Baru
        'end_time'    // <--- Kolom Baru
    ];

    // Relasi: Satu Tryout memiliki banyak Soal (Questions)
    public function questions()
    {
        return $this->hasMany(Question::class);
    }

    // Relasi: Satu Tryout memiliki banyak Percobaan/Pengerjaan oleh Siswa
    public function attempts()
    {
        return $this->hasMany(TryoutAttempt::class);
    }
}
