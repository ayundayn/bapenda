<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HasilTagihan extends Model
{
    protected $table = 'hasil_tagihan';

    protected $fillable = [
        'nop_bank',
        'nominal_bank',
        'nop_vtax',
        'nominal_vtax',
        'selisih',
        'sheet_name',
        'tanggal',
        'tahun',
    ];

    /**
     * Ambil semua tahun unik sebagai string dipisah koma
     *
     * @return string
     */
    public static function getTahunUnikString()
    {
        $tahunUnik = self::selectRaw('YEAR(tanggal) as tahun')
                    ->distinct()
                    ->orderBy('tahun', 'desc')
                    ->pluck('tahun')
                    ->toArray();

        return implode(', ', $tahunUnik);
    }
}
