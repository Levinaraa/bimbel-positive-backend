<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tryout;
use App\Models\TryoutAttempt; // Import Model untuk simpan nilai
use Illuminate\Http\Request;

class TryoutController extends Controller
{
    // 1. Halaman List Tryout
    public function index()
    {
        $tryouts = Tryout::orderBy('created_at', 'desc')->get();
        return response()->json($tryouts);
    }

    // 2. Halaman Mengerjakan Soal (Isi Tryout)
    // PENTING: Kita sembunyikan 'is_correct' agar tidak bisa diintip di Network tab browser
    public function show($id)
    {
        $tryout = Tryout::with(['questions.options' => function($query) {
            // Hanya ambil kolom text, jangan ambil kolom is_correct
            $query->select('id', 'question_id', 'option_text');
        }])->find($id);

        if (!$tryout) {
            return response()->json(['message' => 'Tryout tidak ditemukan'], 404);
        }

        return response()->json($tryout);
    }

    // 3. Submit Jawaban & Hitung Nilai (Disimpan ke Database)
    public function submit(Request $request, $id)
    {
        // Ambil data jawaban dari Frontend
        // Format: { "answers": { "0": 105, "1": 108 } }
        $userAnswers = $request->input('answers');

        // Ambil Tryout beserta kunci jawabannya (is_correct ada)
        $tryout = Tryout::with(['questions.options'])->find($id);

        if (!$tryout) return response()->json(['message' => 'Error: Tryout tidak ditemukan'], 404);

        // --- Logika Hitung Nilai ---
        $totalCorrect = 0;
        $totalWrong = 0;

        foreach ($tryout->questions as $index => $question) {
            // Cari ID opsi yang dipilih user untuk index soal ini
            $selectedOptionId = $userAnswers[$index] ?? null;

            if ($selectedOptionId) {
                // Cek apakah opsi tersebut memiliki is_correct = 1
                // Kita filter dari collection options milik question tersebut
                $isCorrect = $question->options->where('id', $selectedOptionId)->where('is_correct', 1)->isNotEmpty();

                if ($isCorrect) {
                    $totalCorrect++;
                } else {
                    $totalWrong++;
                }
            }
        }

        // Hitung Skor Akhir (Skala 100)
        $totalQuestions = $tryout->questions->count();
        $score = 0;
        if ($totalQuestions > 0) {
            $score = ($totalCorrect / $totalQuestions) * 100;
        }

        // --- Simpan ke Database (Tabel tryout_attempts) ---
        TryoutAttempt::create([
            'user_id' => $request->user()->id, // Ambil ID user yang sedang login
            'tryout_id' => $id,
            'total_correct' => $totalCorrect,
            'total_wrong' => $totalWrong,
            'score' => round($score) // Bulatkan skor
        ]);

        // Kembalikan data lengkap (termasuk kunci jawaban) agar Frontend bisa menampilkan hasil detail
        return response()->json($tryout);
    }

    // 4. Halaman Pembahasan (Soal + Kunci + Penjelasan)
    public function discussion($id)
    {
        // Load semua data termasuk is_correct dan explanation
        $tryout = Tryout::with(['questions.options'])->find($id);

        if (!$tryout) {
            return response()->json(['message' => 'Tryout tidak ditemukan'], 404);
        }

        return response()->json($tryout);
    }
}
