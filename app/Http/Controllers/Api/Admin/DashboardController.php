<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Package;
use App\Models\Transaction;
use App\Models\Attendance;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function stats()
    {
        // 1. Hitung Statistik Utama (Card Atas)
        $totalGuru = User::where('role', 'guru')->count();
        $totalSiswa = User::where('role', 'student')->count();
        $totalPaket = Package::count();
        
        // Hitung Absensi Hari Ini (Guru + Siswa)
        $absenHariIni = Attendance::whereDate('waktu_absen', Carbon::today())->count();

        // 2. Ambil Data Terbaru (Untuk Tabel di Bawah)
        // Ambil 5 Guru Terbaru
        $newestTeachers = User::where('role', 'guru')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['name', 'email', 'created_at']);

        // Ambil 5 Siswa Terbaru
        $newestStudents = User::where('role', 'student')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get(['name', 'email', 'created_at']);

        return response()->json([
            'stats' => [
                'total_teachers' => $totalGuru,
                'total_students' => $totalSiswa,
                'total_packages' => $totalPaket,
                'attendance_today' => $absenHariIni
            ],
            'newest_teachers' => $newestTeachers,
            'newest_students' => $newestStudents
        ]);
    }
}