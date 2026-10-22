<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\CourseWeek; // <--- 1. PASTIKAN TAMBAH BARIS INI

class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'title', 'mentor', 'description', 'schedule', 'image',
        'jenjang', 'tingkat', 'kapasitas', 'durasi',
    ];

    // --- 2. TAMBAHKAN BAGIAN INI (INI YANG HILANG) ---
    public function weeks()
    {
        // Fungsi ini memberi tahu Laravel: "Satu Course punya banyak CourseWeek"
        return $this->hasMany(CourseWeek::class);
    }
    // ------------------------------------------------

    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user');
    }
}
