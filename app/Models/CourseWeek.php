<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseWeek extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi (mass assignment)
    protected $guarded = [];

    // Relasi Kebalikan: Minggu ini milik satu Course (Kelas)
    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    // Relasi: Satu Minggu punya banyak Materi (Materials)
    public function materials()
    {
        return $this->hasMany(CourseMaterial::class);
    }
}
