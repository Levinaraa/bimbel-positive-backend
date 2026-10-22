<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Course;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage; // <--- PENTING: Import Storage

class AdminCourseController extends Controller
{
    public function index()
    {
        $courses = Course::latest()->get();
        return response()->json($courses);
    }

    public function store(Request $request)
    {
        // 1. Validasi
        $request->validate([
            'title'       => 'required|string',
            'mentor'      => 'required|string',
            'schedule'    => 'required|string',
            'description' => 'nullable|string',
            'jenjang'     => 'required|string',
            'tingkat'     => 'required|integer',
            'kapasitas'   => 'required|integer',
            'durasi'      => 'required|integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Validasi Gambar
        ]);

        // 2. Handle Upload Gambar
        $imagePath = '/assets/course-placeholder.jpg'; // Default jika tidak ada upload

        if ($request->hasFile('image')) {
            // Simpan ke folder: storage/app/public/courses
            $path = $request->file('image')->store('courses', 'public');
            // Ubah path agar bisa diakses via URL: /storage/courses/namafile.jpg
            $imagePath = '/storage/' . $path;
        }

        // 3. Simpan ke Database
        $course = Course::create([
            'title'       => $request->title,
            'mentor'      => $request->mentor,
            'schedule'    => $request->schedule,
            'description' => $request->description ?? '-',
            'image'       => $imagePath, // Simpan Path Gambar
            'jenjang'     => $request->jenjang,
            'tingkat'     => $request->tingkat,
            'kapasitas'   => $request->kapasitas,
            'durasi'      => $request->durasi,
        ]);

        return response()->json(['message' => 'Kelas berhasil dibuat', 'data' => $course], 201);
    }

    public function update(Request $request, $id)
    {
        $course = Course::findOrFail($id);

        $request->validate([
            'title'       => 'string',
            'mentor'      => 'string',
            'schedule'    => 'string',
            'description' => 'nullable|string',
            'jenjang'     => 'string',
            'tingkat'     => 'integer',
            'kapasitas'   => 'integer',
            'durasi'      => 'integer',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $dataToUpdate = $request->only([
            'title', 'mentor', 'schedule', 'description',
            'jenjang', 'tingkat', 'kapasitas', 'durasi'
        ]);

        // Handle Ganti Gambar
        if ($request->hasFile('image')) {
            // Hapus gambar lama jika bukan placeholder
            if ($course->image && $course->image !== '/assets/course-placeholder.jpg') {
                // Konversi URL '/storage/...' kembali ke path storage asli
                $oldPath = str_replace('/storage/', '', $course->image);
                Storage::disk('public')->delete($oldPath);
            }

            $path = $request->file('image')->store('courses', 'public');
            $dataToUpdate['image'] = '/storage/' . $path;
        }

        $course->update($dataToUpdate);

        return response()->json(['message' => 'Kelas berhasil diperbarui', 'data' => $course]);
    }

    public function destroy($id)
    {
        $course = Course::findOrFail($id);

        // Hapus gambar fisik saat data dihapus
        if ($course->image && $course->image !== '/assets/course-placeholder.jpg') {
            $oldPath = str_replace('/storage/', '', $course->image);
            Storage::disk('public')->delete($oldPath);
        }

        $course->delete();
        return response()->json(['message' => 'Kelas berhasil dihapus']);
    }
}
