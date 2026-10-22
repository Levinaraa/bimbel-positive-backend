<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Course;
use App\Models\CourseWeek;
use App\Models\CourseMaterial;

class MaterialSeeder extends Seeder
{
    public function run(): void
    {
        // Ambil salah satu kelas (Misal Matematika yang kita buat sebelumnya)
        $course = Course::where('title', 'LIKE', '%Matematika%')->first();

        if ($course) {
            // Buat Minggu 1
            $week1 = CourseWeek::create([
                'course_id' => $course->id,
                'week_number' => 'Minggu 1',
                'title' => 'Eksponen dan Logaritma',
                'description' => 'Mempelajari dasar perpangkatan.'
            ]);

            // Materi Video
            CourseMaterial::create([
                'course_week_id' => $week1->id,
                'type' => 'video',
                'title' => 'Video Pembelajaran: Sifat Eksponen',
                'content_url' => 'https://www.youtube.com/watch?v=dummyvideo'
            ]);

            // Materi PDF
            CourseMaterial::create([
                'course_week_id' => $week1->id,
                'type' => 'pdf',
                'title' => 'Modul Ringkasan Rumus',
                'content_url' => 'modul-eksponen.pdf'
            ]);

            // Buat Minggu 2
            CourseWeek::create([
                'course_id' => $course->id,
                'week_number' => 'Minggu 2',
                'title' => 'Persamaan Kuadrat',
                'description' => 'Cara memfaktorkan persamaan.'
            ]);
        }
    }
}
