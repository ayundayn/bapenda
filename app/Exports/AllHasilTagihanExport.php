<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AllHasilTagihanExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];
        $sheetNames = \App\Models\HasilTagihan::select('sheet_name')->distinct()->pluck('sheet_name');

        foreach ($sheetNames as $name) {
            $sheets[] = new HasilTagihanExport($name);
        }

        return $sheets;
    }
}
