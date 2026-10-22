<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\Option;

class TryoutSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Tryout Header
        $tryout = Tryout::create([
            'title' => 'Tryout Ilmu Pengetahuan Alam - Peredaran Darah',
            'subject' => 'Biologi',
            'duration' => 30, // 30 Menit
            'total_questions' => 2, // Kita buat 2 soal contoh
            'status' => 'active'
        ]);

        // 2. Buat Soal Nomor 1
        $q1 = Question::create([
            'tryout_id' => $tryout->id,
            'question_text' => 'Urutan jalannya peredaran darah kecil yang benar adalah...',
            'explanation' => 'Peredaran darah kecil dimulai dari Jantung (Bilik Kanan) -> Arteri Pulmonalis -> Paru-paru -> Vena Pulmonalis -> Jantung (Serambi Kiri).'
        ]);

        // Pilihan Jawaban Soal 1
        Option::create(['question_id' => $q1->id, 'option_text' => 'Jantung → seluruh tubuh → jantung', 'is_correct' => false]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Jantung → paru-paru → jantung', 'is_correct' => true]); // Jawaban Benar
        Option::create(['question_id' => $q1->id, 'option_text' => 'Paru-paru → jantung → seluruh tubuh', 'is_correct' => false]);
        Option::create(['question_id' => $q1->id, 'option_text' => 'Seluruh tubuh → jantung → paru-paru', 'is_correct' => false]);

        // 3. Buat Soal Nomor 2
        $q2 = Question::create([
            'tryout_id' => $tryout->id,
            'question_text' => 'Bagian jantung yang berfungsi memompa darah ke seluruh tubuh adalah...',
            'explanation' => 'Bilik kiri memiliki dinding paling tebal karena bertugas memompa darah ke seluruh tubuh.'
        ]);

        // Pilihan Jawaban Soal 2
        Option::create(['question_id' => $q2->id, 'option_text' => 'Serambi Kanan', 'is_correct' => false]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'Bilik Kanan', 'is_correct' => false]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'Serambi Kiri', 'is_correct' => false]);
        Option::create(['question_id' => $q2->id, 'option_text' => 'Bilik Kiri', 'is_correct' => true]); // Jawaban Benar
    }
}
