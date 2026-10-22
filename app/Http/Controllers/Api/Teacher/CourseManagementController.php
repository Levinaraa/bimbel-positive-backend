<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\CourseWeek;
use App\Models\CourseMaterial;

class CourseManagementController extends Controller
{
    // 1. TAMBAH TOPIK MINGGUAN (Create Week)
    // Dipanggil dari 'kelasguru.jsx' (TopicModal)
    public function storeWeek(Request $request, $courseId)
    {
        $request->validate([
            'week_number' => 'required|string', // "Minggu 1"
            'title' => 'required|string',       // "Aljabar Dasar"
        ]);

        $week = CourseWeek::create([
            'course_id' => $courseId,
            'week_number' => $request->week_number,
            'title' => $request->title
        ]);

        return response()->json($week, 201);
    }

    // 2. TAMBAH MATERI (PDF/Video)
    // Dipanggil dari 'kelasguru.jsx' (MaterialModal)
    public function storeMaterial(Request $request, $weekId)
    {
        $request->validate([
            'title' => 'required|string',
            'type' => 'required|in:video,pdf,quiz',
            'file' => 'nullable|file|mimes:pdf|max:10000', // Max 10MB jika PDF
            'url' => 'nullable|url' // Jika video youtube
        ]);

        $contentUrl = $request->url;

        // Jika upload file PDF
        if ($request->hasFile('file')) {
            $contentUrl = $request->file('file')->store('materials', 'public');
        }

        $material = CourseMaterial::create([
            'course_week_id' => $weekId,
            'title' => $request->title,
            'type' => $request->type,
            'content_url' => $contentUrl
        ]);

        return response()->json($material, 201);
    }
}
