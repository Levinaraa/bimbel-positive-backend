<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use Carbon\Carbon; // PENTING: Untuk fitur jadwal otomatis

class CourseController extends Controller
{
    /**
     * 1. GET ALL COURSES (List Kelas)
     * Bisa digunakan untuk halaman "Kelas Saya" atau pencarian
     * Endpoint: GET /api/courses?q=matematika
     */
    public function index(Request $request)
    {
        $query = Course::query();

        // Fitur Pencarian (Search)
        if ($request->has('q') && $request->q != '') {
            $search = $request->q;
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('mentor', 'like', "%{$search}%")
                  ->orWhere('subject', 'like', "%{$search}%");
        }

        // Urutkan dari yang terbaru
        $courses = $query->orderBy('created_at', 'desc')->get();

        // Format data agar seragam untuk frontend
        $data = $courses->map(function ($course) {
            return [
                'id' => $course->id,
                'title' => $course->title,
                'subject' => $course->subject,
                'mentor' => $course->mentor,
                'schedule' => $course->schedule, // Contoh: "Senin & Kamis, 14:00"
                'image' => $course->image_url ?? 'https://via.placeholder.com/400x200?text=Kelas+Bimbel',
                'description' => $course->description,
                'is_active' => true // Bisa ambil dari kolom status di DB jika ada
            ];
        });

        return response()->json($data);
    }

    /**
     * 2. SHOW COURSE (Detail Kelas)
     * Endpoint: GET /api/courses/{id}
     */
    public function show($id)
    {
        $course = Course::find($id);

        if (!$course) {
            return response()->json(['message' => 'Kelas tidak ditemukan'], 404);
        }

        return response()->json([
            'id' => $course->id,
            'title' => $course->title,
            'subject' => $course->subject,
            'mentor' => $course->mentor,
            'schedule' => $course->schedule,
            'description' => $course->description,
            'image' => $course->image_url,
            // Jika ada relasi materi (videos/files), bisa ditambahkan di sini:
            // 'materials' => $course->materials 
        ]);
    }

    /**
     * 3. GET TODAY SCHEDULE (Jadwal Hari Ini)
     * Endpoint: GET /api/schedules/today
     * Digunakan di Dashboard Siswa untuk widget "Jadwal Hari Ini"
     */
    public function getTodaySchedule()
    {
        // Set locale ke Indonesia agar nama hari jadi "Senin", "Selasa", dst.
        Carbon::setLocale('id');
        $todayName = Carbon::now()->translatedFormat('l'); // Hasil: "Senin", "Selasa"

        // Cari kelas yang jadwalnya mengandung nama hari ini
        // Contoh: Jika hari ini "Senin", cari yang schedulenya "Senin & Kamis"
        $courses = Course::where('schedule', 'LIKE', "%{$todayName}%")
            ->get()
            ->map(function ($course) {
                return [
                    'id' => $course->id,
                    'subject' => $course->title,
                    'mentor' => $course->mentor,
                    'time' => $course->schedule,
                    'type' => 'Online Class', // Bisa disesuaikan dengan kolom type di DB
                    'isActive' => true,
                    'presensiStatus' => 'pending' // Default status presensi
                ];
            });

        return response()->json($courses);
    }

    /**
     * 4. STORE (Tambah Kelas Baru - Admin/Guru)
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string',
            'subject' => 'required|string',
            'mentor' => 'required|string',
            'schedule' => 'required|string', // Format teks bebas dulu
            'description' => 'nullable|string',
        ]);

        $course = Course::create($request->all());

        return response()->json(['message' => 'Kelas berhasil dibuat', 'data' => $course], 201);
    }

    /**
     * 5. UPDATE (Edit Kelas - Admin/Guru)
     */
    public function update(Request $request, $id)
    {
        $course = Course::find($id);
        if (!$course) return response()->json(['message' => 'Kelas tidak ditemukan'], 404);

        $course->update($request->all());

        return response()->json(['message' => 'Kelas berhasil diperbarui', 'data' => $course]);
    }

    /**
     * 6. DESTROY (Hapus Kelas - Admin)
     */
    public function destroy($id)
    {
        $course = Course::find($id);
        if (!$course) return response()->json(['message' => 'Kelas tidak ditemukan'], 404);

        $course->delete();

        return response()->json(['message' => 'Kelas berhasil dihapus']);
    }
}