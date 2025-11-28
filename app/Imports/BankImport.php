<?php

namespace App\Imports;

use App\Models\HasilBank;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;

class BankImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Cek minimal ada nop dan nominal
            if (empty($row['nop']) || empty($row['nominal'])) continue;

           HasilBank::create([
    'nop' => strval($row['nop']),
    'nominal' => (float) str_replace([',', '.', '(', ')'], '', $row['nominal']),
    'tanggal' => now()->format('Y-m-d'), // <–– wajib
]);

        }
    }
}
