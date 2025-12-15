<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilBank extends Model
{
    protected $table = 'hasil_banks';

    protected $fillable = [
        'nop',
        'nominal',
        'tanggal',
        'tahun',
    ];

    public $timestamps = true;
}
