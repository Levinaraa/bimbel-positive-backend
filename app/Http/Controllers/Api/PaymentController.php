<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction; 
use App\Models\Package; // Import Package jika ingin validasi harga dari DB
use Midtrans\Config;
use Midtrans\Snap;

class PaymentController extends Controller
{
    public function __construct()
    {
        // 1. Set Konfigurasi Midtrans
        // Pastikan Anda sudah menaruh MIDTRANS_SERVER_KEY di file .env
        Config::$serverKey = config('midtrans.server_key');
        Config::$isProduction = config('midtrans.is_production');
        Config::$isSanitized = config('midtrans.is_sanitized');
        Config::$is3ds = config('midtrans.is_3ds');
    }

    /**
     * 1. MEMBUAT TRANSAKSI BARU & MENDAPATKAN SNAP TOKEN
     * Endpoint: POST /api/payment/charge
     */
    public function createTransaction(Request $request)
    {
        // Validasi Input
        $request->validate([
            'product_name' => 'required|string',
            'amount' => 'required|numeric',
            'package_id' => 'required|exists:packages,id', // Pastikan paket ada
        ]);

        $user = Auth::user();

        // GENERATE ORDER ID UNIK
        // Format: ORDER-{TIMESTAMP}-{USER_ID}-{RANDOM}
        // Ini penting agar 'order_id' selalu unik dan tidak ditolak Midtrans
        $orderId = 'ORDER-' . time() . '-' . $user->id . '-' . rand(100, 999);

        // Siapkan Parameter untuk Midtrans Snap
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $request->amount,
            ],
            'customer_details' => [
                'first_name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '08123456789', // Default jika kosong
            ],
            'item_details' => [
                [
                    'id' => $request->package_id,
                    'price' => $request->amount,
                    'quantity' => 1,
                    'name' => substr($request->product_name, 0, 50), // Midtrans membatasi panjang nama item
                ]
            ]
        ];

        try {
            // A. Dapatkan Snap Token dari Midtrans
            $snapToken = Snap::getSnapToken($params);

            // B. Simpan Transaksi ke Database (Status: Pending)
            // Pastikan 'order_id' sudah ditambahkan ke $fillable di Model Transaction!
            $transaction = Transaction::create([
                'user_id'      => $user->id,
                'order_id'     => $orderId,          // <--- INI DATA PENTING YANG SEBELUMNYA ERROR
                'package_id'   => $request->package_id,
                'amount'       => $request->amount,
                'status'       => 'pending',
                'snap_token'   => $snapToken,        // Simpan token agar bisa dipanggil ulang (retry payment)
                'payment_method' => 'Midtrans Snap'
            ]);

            return response()->json([
                'status' => 'success',
                'snap_token' => $snapToken,
                'transaction' => $transaction
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * 2. HANDLE NOTIFIKASI DARI MIDTRANS (WEBHOOK)
     * Endpoint: POST /api/payment/notification
     * Midtrans akan menembak URL ini secara otomatis saat status bayar berubah.
     */
    public function notificationHandler(Request $request)
    {
        $serverKey = config('midtrans.server_key');

        // Buat Signature Key untuk Verifikasi Keamanan
        // Rumus: SHA512(order_id + status_code + gross_amount + ServerKey)
        $hashed = hash("sha512", $request->order_id . $request->status_code . $request->gross_amount . $serverKey);

        // Validasi Signature
        if ($hashed == $request->signature_key) {

            $transactionStatus = $request->transaction_status;
            $orderId = $request->order_id;

            // Cari Transaksi di Database berdasarkan Order ID
            $transaction = Transaction::where('order_id', $orderId)->first();

            if ($transaction) {
                // Update Status Berdasarkan Respon Midtrans
                if ($transactionStatus == 'capture' || $transactionStatus == 'settlement') {
                    $transaction->update(['status' => 'success']);
                }
                else if ($transactionStatus == 'pending') {
                    $transaction->update(['status' => 'pending']);
                }
                else if ($transactionStatus == 'deny' || $transactionStatus == 'expire' || $transactionStatus == 'cancel') {
                    $transaction->update(['status' => 'failed']);
                }
            }
        }

        return response()->json(['status' => 'OK']);
    }

    /**
     * 3. RIWAYAT TRANSAKSI USER (HISTORY)
     * Endpoint: GET /api/payment/history
     */
    public function history(Request $request)
    {
        $user = Auth::user();

        // Ambil data transaksi milik user yang login
        // Join dengan tabel packages agar nama produk tampil (opsional, bisa juga simpan nama produk di trx)
        $transactions = Transaction::where('user_id', $user->id)
                        ->with('package') // Pastikan ada relasi public function package() di Model Transaction
                        ->orderBy('created_at', 'desc')
                        ->get()
                        ->map(function ($item) {
                            return [
                                'id' => $item->id,
                                'order_id' => $item->order_id,
                                'amount' => $item->amount,
                                'status' => $item->status,
                                'snap_token' => $item->snap_token,
                                'created_at' => $item->created_at,
                                // Tampilkan nama paket dari relasi, atau fallback jika paket dihapus
                                'product_name' => $item->package ? $item->package->nama_paket : 'Paket Belajar (Arsip)',
                            ];
                        });

        return response()->json($transactions);
    }
}