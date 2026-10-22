<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AdminTransactionController extends Controller
{
    public function index(Request $request)
    {
        // 1. Filter Waktu (Bulan Ini / Tahun Ini) untuk Statistik
        $range = $request->query('range', 'Bulan Ini');
        $queryStats = Transaction::query();

        if ($range === 'Bulan Ini') {
            $queryStats->whereMonth('created_at', Carbon::now()->month)
                       ->whereYear('created_at', Carbon::now()->year);
        } elseif ($range === 'Tahun Ini') {
            $queryStats->whereYear('created_at', Carbon::now()->year);
        }

        // Hitung Statistik Header
        $stats = [
            'success' => (clone $queryStats)->where('status', 'success')->count(),
            'pending' => (clone $queryStats)->where('status', 'pending')->count(), // Di frontend labelnya "Dalam Proses"
            'failed'  => (clone $queryStats)->where('status', 'failed')->count(),
            'total_income' => (clone $queryStats)->where('status', 'success')->sum('amount'),
        ];

        // 2. Data Tabel (List Pesanan)
        // Ambil semua data dengan relasi user dan package
        $transactions = Transaction::with(['user', 'package'])
            ->latest()
            ->get()
            ->map(function ($item) {
                return [
                    'id' => 'SNV' . (100 + $item->id), // Format ID Custom sesuai desain
                    'original_id' => $item->id, // ID Asli untuk hapus data
                    'date' => Carbon::parse($item->created_at)->translatedFormat('d F Y'),
                    'name' => $item->user ? $item->user->name : 'User Terhapus',
                    'category' => $item->package ? $item->package->name : 'Paket Terhapus',
                    'total' => $item->amount,
                    'method' => $item->payment_method,
                    'status' => $this->mapStatus($item->status), // Helper untuk mapping status
                ];
            });

        return response()->json([
            'stats' => $stats,
            'orders' => $transactions
        ]);
    }

    // Helper untuk menyamakan status database dengan tampilan Frontend
    private function mapStatus($status)
    {
        switch ($status) {
            case 'success': return 'Selesai';
            case 'pending': return 'Dalam Proses';
            case 'failed': return 'Gagal';
            default: return 'Dalam Proses';
        }
    }

    public function destroy(Request $request)
    {
        // Validasi input array ID
        $ids = $request->input('ids');

        if (!is_array($ids) || empty($ids)) {
            return response()->json(['message' => 'Tidak ada data yang dipilih'], 400);
        }

        Transaction::whereIn('id', $ids)->delete();

        return response()->json(['message' => 'Pesanan berhasil dihapus']);
    }
}
