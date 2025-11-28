<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilVTax extends Model
{
    // Nama tabel sesuai migration
    protected $table = 'hasil_v_taxes';

    // Kolom yang bisa mass-assignment
    protected $fillable = [
        'nop',
        'nominal',
        'tanggal',
    ];

    // Timestamps aktif (default Laravel)
    public $timestamps = true;
}
