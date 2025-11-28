<?php

namespace App\Imports;

use App\Models\HasilVTax;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class VTaxImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (empty($row['nop']) || empty($row['nominal'])) continue;

            HasilVTax::create([
    'nop' => strval($row['nop']),
    'nominal' => (float) str_replace([',', '.', '(', ')'], '', $row['nominal']),
    'tanggal' => now()->format('Y-m-d'),
]);

        }
    }
}
