<?php

namespace App\Exports;

use App\Models\HasilTagihan;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilTagihanExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithColumnWidths,
    WithStyles,
    ShouldAutoSize,
    WithColumnFormatting,
    WithChunkReading
{
    protected $sheet;

    public function __construct($sheet = null)
    {
        $this->sheet = $sheet;
    }

    /**
     * Query data tanpa load semua ke memori
     */
    public function query()
    {
        return HasilTagihan::query()
            ->select([
                'nop_bank',
                'nominal_bank',
                'nop_vtax',
                'nominal_vtax',
                'selisih',
                'sheet_name',
                'tanggal'
            ])
            ->when($this->sheet, function ($q) {
                $q->where('sheet_name', $this->sheet);
            });
    }

    /**
     * Mapping baris data ke kolom Excel
     */
    public function map($row): array
    {
        return [
            "'" . $row->nop_bank,
            $row->nominal_bank,
            "'" . $row->nop_vtax,
            $row->nominal_vtax,
            $row->selisih,
            $row->sheet_name,
        ];
    }

    public function headings(): array
    {
        return [
            'NOP BANK',
            'NOMINAL BANK',
            'NOP VTAX',
            'NOMINAL VTAX',
            'SELISIH',
            'SHEET',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 18,
            'C' => 20,
            'D' => 18,
            'E' => 18,
            'F' => 15,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'B' => NumberFormat::FORMAT_NUMBER_00,
            'D' => NumberFormat::FORMAT_NUMBER_00,
            'E' => NumberFormat::FORMAT_NUMBER_00,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('center');
        $sheet->getStyle('A1:F' . $sheet->getHighestRow())
            ->getBorders()->getAllBorders()
            ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        return [];
    }

    /**
     * Baca data per 500 baris (hemat memori)
     */
    public function chunkSize(): int
    {
        return 500;
    }
}
