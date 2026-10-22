<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CourseSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('courses')->insert([
            [
                'title' => 'Intensif UTBK: Matematika Saintek',
                'mentor' => 'Kak Bimo',
                'description' => 'Bedah tuntas materi Matematika Saintek.',
                'schedule' => 'Senin & Kamis, 14:00',
                'image' => '/assets/course-math.jpg', // Path gambar di folder public React
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Mastering Tenses English',
                'mentor' => 'Ms. Sarah',
                'description' => 'Kuasai 16 tenses dengan mudah.',
                'schedule' => 'Selasa, 16:00',
                'image' => '/assets/course-english.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Fisika Dasar: Hukum Newton',
                'mentor' => 'Kak Rio',
                'description' => 'Memahami konsep gaya dan gerak.',
                'schedule' => 'Rabu, 19:00',
                'image' => '/assets/course-physics.jpg',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);
    }
}
