<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Relasi: Soal milik Tryout
    public function tryout()
    {
        return $this->belongsTo(Tryout::class);
    }

    // Relasi: Satu Soal punya banyak Pilihan Jawaban
    public function options()
    {
        return $this->hasMany(Option::class);
    }
}
