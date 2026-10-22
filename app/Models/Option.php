<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $guarded = [];

    // Sembunyikan kunci jawaban agar tidak mudah diintip di API (Opsional, nanti kita handle di controller)
    // protected $hidden = ['is_correct'];
}
