<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilTagihan extends Model
{
    // Sesuaikan nama tabel kalau tidak pakai plural default Laravel
    protected $table = 'hasil_tagihan';

    // Kolom-kolom yang boleh diisi pakai mass assignment (seperti create())
    protected $fillable = [
        'nop_bank',
        'nominal_bank',
        'nop_vtax',
        'nominal_vtax',
        'selisih',
        'sheet_name',
    ];
}
