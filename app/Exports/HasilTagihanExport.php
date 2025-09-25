<?php

namespace App\Exports;

use App\Models\HasilTagihan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HasilTagihanExport implements
    FromCollection,
    WithHeadings,
    WithColumnWidths,
    WithStyles,
    ShouldAutoSize,
    WithColumnFormatting
{
    protected $sheet;

    public function __construct($sheet = null)
    {
        $this->sheet = $sheet;
    }

    public function collection()
    {
        $query = HasilTagihan::query();

        if ($this->sheet) {
            $query->where('sheet_name', $this->sheet);
        }

        return $query->select([
            'nop_bank',
            'nominal_bank',
            'nop_vtax',
            'nominal_vtax',
            'selisih',
            'sheet_name',
        ])->get();
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

    // Atur lebar kolom manual (kalau mau fix)
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

    // Format angka
    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_NUMBER, // NOP Bank
            'B' => NumberFormat::FORMAT_ACCOUNTING_USD, // Nominal Bank
            'C' => NumberFormat::FORMAT_NUMBER, // NOP VTax
            'D' => NumberFormat::FORMAT_ACCOUNTING_USD, // Nominal VTax
            'E' => NumberFormat::FORMAT_ACCOUNTING_USD, // Selisih
        ];
    }

    // Styling (border, bold header, alignment)
    public function styles(Worksheet $sheet)
    {
        // Bold header
        $sheet->getStyle('A1:F1')->getFont()->setBold(true);

        // Border semua sel
        $sheet->getStyle('A1:F' . $sheet->getHighestRow())
            ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        // Alignment center untuk header
        $sheet->getStyle('A1:F1')->getAlignment()->setHorizontal('center');

        return [];
    }
}
