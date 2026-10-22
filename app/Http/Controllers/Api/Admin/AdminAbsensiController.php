<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Course;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;

class AdminAbsensiController extends Controller
{
    // A. Ambil Kelas yang Sedang Aktif (Untuk Card Merah)
    public function getActiveClasses()
    {
        // Ambil 4 kelas terbaru
        $courses = Course::latest()->take(4)->get()->map(function($c) {
            return [
                'name' => $c->nama_kelas ?? $c->title, // Sesuaikan dengan kolom DB Course Anda
                'mentor' => $c->mentor ?? 'Guru Pengampu',
                'time' => $c->jadwal ?? $c->schedule ?? '08:00 - 10:00',
            ];
        });
        return response()->json($courses);
    }

    // B. Ambil Absensi Guru (Hari Ini)
    public function getTeacherAttendance()
    {
        $teachers = User::where('role', 'guru')->get()->map(function($u) {
            // Cek apakah hari ini sudah absen?
            $log = Attendance::where('user_id', $u->id)
                             ->whereDate('waktu_absen', Carbon::today())
                             ->first();

            return [
                'name' => $u->name,
                'role' => 'Pengajar Utama',
                'status' => $log ? $log->status : 'Belum Absen',
                'time' => $log ? Carbon::parse($log->waktu_absen)->format('H:i') : '-',
                'date' => Carbon::now()->format('d M Y')
            ];
        });
        return response()->json($teachers);
    }

    // C. Ambil Absensi Siswa (Hari Ini)
    public function getStudentAttendance()
    {
        $students = User::where('role', 'student')->take(20)->get()->map(function($u) {
            $log = Attendance::where('user_id', $u->id)
                             ->whereDate('waktu_absen', Carbon::today())
                             ->first();

            return [
                'name' => $u->name,
                'className' => 'Reguler Class', // Bisa ambil dari relasi course
                'status' => $log ? $log->status : 'Belum Absen',
                'time' => $log ? Carbon::parse($log->waktu_absen)->format('H:i') : '-',
                'date' => Carbon::now()->format('d M Y')
            ];
        });
        return response()->json($students);
    }
}
