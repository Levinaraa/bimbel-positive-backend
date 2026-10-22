<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    // 1. Ambil Data Tabel
    public function index()
    {
        return response()->json(Package::latest()->get());
    }

    // 2. Ambil Data Grafik & Ringkasan
    public function chartData()
    {
        // Data Grafik (Total terjual per kategori)
        $chart = [
            ['label' => 'SD', 'value' => Package::where('kategori', 'SD')->sum('terjual')],
            ['label' => 'SMP', 'value' => Package::where('kategori', 'SMP')->sum('terjual')],
            ['label' => 'SMA', 'value' => Package::where('kategori', 'SMA')->sum('terjual')],
            ['label' => 'UTBK', 'value' => Package::where('kategori', 'UTBK')->sum('terjual')],
        ];

        // Data Ringkasan (Terlaris & Kurang Diminati)
        $bestSeller = Package::orderBy('terjual', 'desc')->first();
        $worstSeller = Package::orderBy('terjual', 'asc')->first();

        $summary = [
            'bestSeller' => [
                'name' => $bestSeller ? $bestSeller->nama_paket : '-',
                'count' => $bestSeller ? $bestSeller->terjual : 0
            ],
            'leastInterested' => [
                'name' => $worstSeller ? $worstSeller->nama_paket : '-',
                'count' => $worstSeller ? $worstSeller->terjual : 0
            ]
        ];

        return response()->json([
            'chart' => $chart,
            'summary' => $summary
        ]);
    }

    // 3. Simpan Paket Baru
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'price' => 'required|numeric',
            'duration' => 'required|numeric',
        ]);

        Package::create([
            'nama_paket' => $request->name,
            'deskripsi' => $request->desc,
            'durasi' => $request->duration,
            'harga' => $request->price,
            'diskon' => $request->discount ?? 0,
            'kategori' => $request->category ?? 'Umum',
            'status' => $request->status ?? 'Aktif',
            'terjual' => 0
        ]);

        return response()->json(['message' => 'Sukses']);
    }

    // 4. Update Paket
    public function update(Request $request, $id)
    {
        $pkg = Package::findOrFail($id);
        $pkg->update([
            'nama_paket' => $request->name,
            'deskripsi' => $request->desc,
            'durasi' => $request->duration,
            'harga' => $request->price,
            'diskon' => $request->discount,
            'kategori' => $request->category,
            'status' => $request->status,
        ]);

        return response()->json(['message' => 'Update Sukses']);
    }

    // 5. Hapus Paket
    public function destroy($id)
    {
        Package::findOrFail($id)->delete();
        return response()->json(['message' => 'Hapus Sukses']);
    }
}
