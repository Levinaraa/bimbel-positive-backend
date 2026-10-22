<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    /**
     * Properti $fillable menentukan kolom mana saja yang boleh diisi
     * secara massal (melalui method create/update).
     * * PENTING: 'order_id' harus ada di sini agar tidak error saat insert.
     */
    protected $fillable = [
        'order_id',       // <--- INI PERBAIKAN UTAMANYA
        'user_id', 
        'package_id', 
        'amount', 
        'payment_method', 
        'status', 
        'snap_token'
    ];

    /**
     * Relasi ke Model User
     * Setiap transaksi dimiliki oleh satu user.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Relasi ke Model Package
     * Setiap transaksi berhubungan dengan satu paket bimbel.
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
}