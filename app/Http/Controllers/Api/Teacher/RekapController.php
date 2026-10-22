<?php

namespace App\Http\Controllers\Api\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TryoutAttempt;
use App\Models\Tryout;
use Illuminate\Support\Facades\DB;

class RekapController extends Controller
{
    public function index()
    {
        // 1. DATA DONUT CHART (Rata-rata Nilai per Mata Pelajaran)
        // Mengambil rata-rata skor dari semua percobaan siswa, dikelompokkan per Mapel
        $pieData = Tryout::join('tryout_attempts', 'tryouts.id', '=', 'tryout_attempts.tryout_id')
            ->select('tryouts.subject as name', DB::raw('ROUND(AVG(tryout_attempts.score)) as value'))
            ->groupBy('tryouts.subject')
            ->get();

        // Berikan warna untuk chart
        $pieData = $pieData->map(function($item) {
            // Palet warna merah/emas sesuai tema
            $colors = [
                'Matematika' => '#74151e',
                'Biologi' => '#9e4c55',
                'Kimia' => '#d9b6b9',
                'Fisika' => '#E5989B',
                'Bahasa Inggris' => '#B5838D'
            ];
            $item->color = $colors[$item->name] ?? '#6D6875';
            return $item;
        });

        // 2. DATA LINE CHART (Aktivitas Pengerjaan per Bulan)
        // Menghitung berapa kali tryout dikerjakan per bulan
        $lineData = TryoutAttempt::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw("DATE_FORMAT(created_at, '%M') as name"), // Nama Bulan
                DB::raw("COUNT(*) as total_ujian")
            )
            ->groupBy('month_key', 'name')
            ->orderBy('month_key', 'asc')
            ->limit(6) // Ambil 6 bulan terakhir
            ->get();

        // 3. TABEL DETAIL (10 Riwayat Pengerjaan Terakhir Siswa)
        $recentActivities = TryoutAttempt::with(['user', 'tryout'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get()
            ->map(function($attempt) {
                return [
                    'id' => $attempt->id,
                    'siswa' => $attempt->user->name,
                    'tryout' => $attempt->tryout->title,
                    'mapel' => $attempt->tryout->subject,
                    'score' => $attempt->score,
                    'date' => $attempt->created_at->format('d M Y'),
                    'status' => $attempt->score >= 70 ? 'Lulus' : 'Remedial' // Logika sederhana
                ];
            });

        return response()->json([
            'pie' => $pieData,
            'line' => $lineData,
            'recent' => $recentActivities
        ]);
    }
}
