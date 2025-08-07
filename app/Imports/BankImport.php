<?php

namespace App\Imports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class BankImport implements ToCollection, WithHeadingRow
{
    public function collection(Collection $rows)
    {
        return $rows->map(function ($row) {
            return [
                'nop' => strval($row['nop']),
                'nominal' => (float) str_replace([',', '.', '(', ')'], '', $row['nominal']),
            ];
        });
    }
}