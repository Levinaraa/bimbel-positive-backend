<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    /**
     * Ambil Data Pengajar (Role = 'guru')
     */
    public function getTeachers()
    {
        $teachers = User::where('role', 'guru')->latest()->get();
        return response()->json($teachers);
    }

    /**
     * Ambil Data Siswa (Role = 'student')
     * Dilengkapi dengan data kelas yang diambil (relasi 'courses')
     */
    public function getStudents()
    {
        $students = User::where('role', 'student')
                        ->with('courses') // Eager load relasi kelas agar frontend tahu siswa ini masuk kelas mana
                        ->latest()
                        ->get();

        return response()->json($students);
    }

    /**
     * Tambah User Baru (Bisa Guru atau Siswa)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'name'       => 'required|string|max:255',
            'email'      => 'required|email|unique:users,email',
            'password'   => 'required|min:6',
            'role'       => 'required|in:guru,student',

            // Validasi Array ID Kelas (Hanya jika role student)
            'course_ids'   => 'nullable|array',
            'course_ids.*' => 'exists:courses,id', // Pastikan ID kelas benar-benar ada di tabel courses
        ]);

        // 2. Buat User di Database
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password), // Hash Password
            'role'     => $request->role,
        ]);

        // 3. Jika Siswa & Memilih Kelas, Simpan ke Tabel Pivot (course_user)
        if ($request->role === 'student' && $request->filled('course_ids')) {
            // Method sync() otomatis mengisi tabel perantara
            $user->courses()->sync($request->course_ids);
        }

        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'data'    => $user
        ], 201);
    }

    /**
     * Update Data User
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // 1. Validasi Input (Nullable agar tidak wajib isi semua saat edit)
        $request->validate([
            'name'       => 'string|max:255',
            'email'      => 'email|unique:users,email,'.$id, // Ignore email sendiri
            'role'       => 'in:guru,student',
            'course_ids'   => 'nullable|array',
            'course_ids.*' => 'exists:courses,id',
        ]);

        // 2. Update Data User
        $user->name  = $request->name ?? $user->name;
        $user->email = $request->email ?? $user->email;
        $user->role  = $request->role ?? $user->role;

        // Cek jika password diganti
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        // 3. Update Relasi Kelas (Khusus Siswa)
        // Jika request mengirim 'course_ids', update relasinya.
        // Jika course_ids dikirim kosong [], maka semua kelas akan terhapus (siswa keluar dari semua kelas)
        if ($user->role === 'student' && $request->has('course_ids')) {
            $user->courses()->sync($request->course_ids);
        }

        return response()->json([
            'message' => 'User berhasil diperbarui',
            'data'    => $user
        ]);
    }

    /**
     * Hapus User
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // Hapus user (Data di pivot course_user otomatis terhapus jika migration pakai onDelete cascade)
        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }
}
