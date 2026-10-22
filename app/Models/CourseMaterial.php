<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CourseMaterial extends Model
{
    use HasFactory;

    // Mengizinkan semua kolom diisi (mass assignment)
    protected $guarded = [];

    // Relasi Kebalikan: Materi ini milik satu CourseWeek (Minggu)
    public function week()
    {
        return $this->belongsTo(CourseWeek::class, 'course_week_id');
    }

    // Relasi: Satu Materi punya banyak Komentar (Diskusi)
    // Kita urutkan 'desc' agar komentar terbaru muncul paling atas
    public function comments()
    {
        return $this->hasMany(Comment::class)->orderBy('created_at', 'desc');
    }
}
