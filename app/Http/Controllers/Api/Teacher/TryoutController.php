<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tryout;
use App\Models\Question;
use App\Models\Option;
use Illuminate\Support\Facades\DB;

class TryoutController extends Controller
{
    // 1. LIST TRYOUT (Khusus Guru)
    public function index()
    {
        return response()->json(Tryout::orderBy('created_at', 'desc')->get());
    }

    // 2. DETAIL TRYOUT (BESERTA SOAL) - [FIXED]
    // Fungsi ini wajib ada untuk halaman Input Soal / Edit Tryout
    public function show($id)
    {
        // Ambil tryout beserta relasi questions dan options
        $tryout = Tryout::with(['questions.options'])->find($id);

        if (!$tryout) {
            return response()->json(['message' => 'Tryout tidak ditemukan'], 404);
        }

        return response()->json($tryout);
    }

    // 3. BUAT TRYOUT BARU (Header)
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'subject' => 'required|string',
            'duration' => 'required|integer',
            'status' => 'required|in:active,upcoming,finished'
        ]);

        $tryout = Tryout::create($request->all());

        return response()->json([
            'message' => 'Tryout berhasil dibuat',
            'data' => $tryout
        ], 201);
    }

    // 4. TAMBAH SOAL BARU - [FIXED VALIDATION]
    public function storeQuestion(Request $request, $tryoutId)
    {
        // Sesuaikan validasi dengan payload dari Frontend (isitryoutguru.jsx)
        $request->validate([
            'question_text' => 'required|string',       // Frontend kirim 'question_text'
            'explanation'   => 'nullable|string',
            'options'       => 'required|array|min:2',
            'options.*.option_text' => 'required|string', // Frontend kirim 'option_text'
            'options.*.is_correct'  => 'required'         // Frontend kirim 'is_correct'
        ]);

        DB::beginTransaction();
        try {
            // A. Simpan Soal
            $question = Question::create([
                'tryout_id' => $tryoutId,
                'question_text' => $request->question_text, // Sesuaikan key request
                'explanation' => $request->explanation
            ]);

            // B. Simpan Opsi Jawaban
            foreach ($request->options as $opt) {
                Option::create([
                    'question_id' => $question->id,
                    'option_text' => $opt['option_text'], // Sesuaikan key array
                    'is_correct'  => $opt['is_correct']   // Sesuaikan key array
                ]);
            }

            // C. Update Counter Soal
            $tryout = Tryout::find($tryoutId);
            if ($tryout) {
                $tryout->increment('total_questions');
            }

            DB::commit();
            return response()->json(['message' => 'Soal berhasil disimpan'], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Gagal simpan soal: ' . $e->getMessage()], 500);
        }
    }

    // 5. UPDATE HEADER TRYOUT
    public function update(Request $request, $id)
    {
        $tryout = Tryout::find($id);
        if (!$tryout) return response()->json(['message' => 'Not found'], 404);

        $request->validate([
            'title' => 'required',
            'duration' => 'required|integer',
            'start_time' => 'nullable', 
            'end_time' => 'nullable'
        ]);

        $tryout->update($request->only(['title', 'subject', 'duration', 'start_time', 'end_time']));

        return response()->json(['message' => 'Info Tryout berhasil diupdate']);
    }

    // 6. HAPUS TRYOUT
    public function destroy($id)
    {
        $tryout = Tryout::find($id);
        if (!$tryout) return response()->json(['message' => 'Not found'], 404);

        $tryout->delete(); 
        return response()->json(['message' => 'Tryout dihapus']);
    }
}