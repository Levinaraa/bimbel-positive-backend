<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\TryoutAttempt; // Pastikan Model ini sesuai dengan yang Anda pakai
use App\Models\Tryout;

class RekapController extends Controller
{
    public function index()
    {
        $userId = Auth::id(); // AMBIL ID SISWA YANG SEDANG LOGIN

        // 1. DATA PIE CHART (Rata-rata Nilai Siswa Ini per Mapel)
        $pieData = Tryout::join('tryout_attempts', 'tryouts.id', '=', 'tryout_attempts.tryout_id')
            ->where('tryout_attempts.user_id', $userId) // <--- FILTER USER
            ->select('tryouts.subject as name', DB::raw('ROUND(AVG(tryout_attempts.score)) as value'))
            ->groupBy('tryouts.subject')
            ->get();

        // Beri warna
        $pieData = $pieData->map(function($item) {
            $colors = ['#74151e', '#9e4c55', '#d9b6b9', '#E5989B', '#B5838D'];
            $item->color = $colors[rand(0, 4)];
            return $item;
        });

        // 2. DATA LINE CHART (Aktivitas Siswa Ini)
        $lineData = TryoutAttempt::where('user_id', $userId) // <--- FILTER USER
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month_key"),
                DB::raw("DATE_FORMAT(created_at, '%M') as name"),
                DB::raw("COUNT(*) as total_ujian") // Berapa kali dia ujian bulan ini
            )
            ->groupBy('month_key', 'name')
            ->orderBy('month_key', 'asc')
            ->limit(6)
            ->get();

        // 3. TABEL DETAIL (Riwayat Siswa Ini)
        $details = TryoutAttempt::with('tryout')
            ->where('user_id', $userId) // <--- FILTER USER
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($attempt) {
                return [
                    'id' => $attempt->id,
                    'topic' => $attempt->tryout->title, // Judul Tryout
                    'week' => $attempt->created_at->format('d M Y H:i'), // Tanggal
                    'score' => $attempt->score
                ];
            });
        
        // Format agar sesuai dengan struktur Accordion di Frontend (rekap.jsx)
        // Kita kelompokkan berdasarkan Mapel
        $groupedDetails = $details->groupBy(function($item) use ($details) {
             // Kita perlu ambil nama mapel, tapi karena di map di atas sudah jadi array flat,
             // kita ambil ulang dari query raw atau grouping manual.
             // Agar simpel, kita kirim raw details dulu.
             return 'Riwayat'; 
        });
        
        // Agar sesuai persis dengan rekap.jsx (frontend), kita sesuaikan strukturnya:
        // Frontend mengharapkan array of object dengan key: { id, subject_name, average_score, history: [] }
        
        $finalDetails = [];
        $rawAttempts = TryoutAttempt::with('tryout')
            ->where('user_id', $userId)
            ->get()
            ->groupBy('tryout.subject');

        foreach($rawAttempts as $subject => $attempts) {
            $finalDetails[] = [
                'id' => md5($subject),
                'subject_name' => $subject,
                'average_score' => round($attempts->avg('score')),
                'history' => $attempts->map(function($a) {
                    return [
                        'topic' => $a->tryout->title,
                        'week' => $a->created_at->format('d M Y'),
                        'score' => $a->score
                    ];
                })
            ];
        }

        return response()->json([
            'charts' => [
                'pie' => $pieData,
                'line' => $lineData
            ],
            'details' => $finalDetails
        ]);
    }
}