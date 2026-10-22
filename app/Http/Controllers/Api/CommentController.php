<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Comment;
use App\Models\CourseMaterial;

class CommentController extends Controller
{
    // Ambil semua komentar di suatu materi
    public function index($materialId)
    {
        $comments = Comment::with('user') // Sertakan data user (nama & avatar)
            ->where('course_material_id', $materialId)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($comments);
    }

    // Kirim komentar baru
    public function store(Request $request, $materialId)
    {
        $request->validate([
            'content' => 'required|string'
        ]);

        $comment = Comment::create([
            'user_id' => $request->user()->id,
            'course_material_id' => $materialId,
            'content' => $request->content
        ]);

        // Return komentar baru beserta data user-nya agar bisa langsung tampil di frontend
        return response()->json($comment->load('user'), 201);
    }
}
