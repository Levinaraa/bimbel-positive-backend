<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Paket; // Pastikan Anda sudah membuat Model Paket
use Illuminate\Support\Facades\DB;

class PaketController extends Controller
{
    public function index()
    {
        // Ambil semua paket
        $pakets = Paket::latest()->get();

        // Hitung Summary (Logic Sederhana)
        $bestSeller = Paket::orderBy('terjual', 'desc')->first();
        $leastInterested = Paket::orderBy('terjual', 'asc')->first();

        return response()->json([
            'pakets' => $pakets,
            'summary' => [
                'bestSeller' => [
                    'name' => $bestSeller ? $bestSeller->nama_paket : '-',
                    'count' => $bestSeller ? $bestSeller->terjual : 0
                ],
                'leastInterested' => [
                    'name' => $leastInterested ? $leastInterested->nama_paket : '-',
                    'count' => $leastInterested ? $leastInterested->terjual : 0
                ]
            ]
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'desc' => 'nullable|string',
            'duration' => 'required|numeric',
            'price' => 'required|numeric',
            'discount' => 'numeric',
            'status' => 'required|in:Aktif,Tidak Aktif',
            'category' => 'nullable|string'
        ]);

        $paket = Paket::create([
            'nama_paket' => $validated['name'],
            'deskripsi' => $validated['desc'],
            'durasi' => $validated['duration'],
            'harga' => $validated['price'],
            'diskon' => $validated['discount'],
            'status' => $validated['status'],
            'kategori' => $validated['category'] ?? 'Umum',
        ]);

        return response()->json(['message' => 'Paket berhasil dibuat', 'data' => $paket]);
    }

    public function update(Request $request, $id)
    {
        $paket = Paket::findOrFail($id);

        $paket->update([
            'nama_paket' => $request->name,
            'deskripsi' => $request->desc,
            'durasi' => $request->duration,
            'harga' => $request->price,
            'diskon' => $request->discount,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Paket diupdate', 'data' => $paket]);
    }

    public function destroy($id)
    {
        Paket::destroy($id);
        return response()->json(['message' => 'Paket dihapus']);
    }

    // API Khusus Chart (Simulasi Data)
    public function chartData(Request $request)
    {
        $range = $request->query('range', 'Bulan Ini');

        // Logika dummy agar chart berubah sesuai filter
        // Di real app, ini query ke tabel transaksi berdasarkan created_at
        $multiplier = 1;
        if ($range == '3 Bulan') $multiplier = 3;
        if ($range == '6 Bulan') $multiplier = 6;
        if ($range == '1 Tahun') $multiplier = 12;

        $data = [
            ['label' => 'SD', 'value' => rand(5, 10) * $multiplier],
            ['label' => 'SMP', 'value' => rand(10, 20) * $multiplier],
            ['label' => 'SMA', 'value' => rand(8, 15) * $multiplier],
            ['label' => 'UTBK', 'value' => rand(10, 25) * $multiplier],
        ];

        return response()->json($data);
    }
}
