<?php

namespace App\Jobs;

use App\Models\HasilTagihan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ProsesTagihanJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $bankPath;
    protected $vtaxPath;

    public function __construct($bankPath, $vtaxPath)
    {
        $this->bankPath = $bankPath;
        $this->vtaxPath = $vtaxPath;
    }

    public function handle(): void
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1024M');

        HasilTagihan::truncate();

        // Ambil semua sheet Bank & VTax
        $bankSheets = Excel::toCollection(null, storage_path('app/' . $this->bankPath));
        $vtaxSheets = Excel::toCollection(null, storage_path('app/' . $this->vtaxPath));

        // === Parsing VTax dulu (hanya 1 sheet) ===
        $vtaxData = collect();
        $vtaxSheet = $vtaxSheets->first();
        if ($vtaxSheet && !$vtaxSheet->isEmpty()) {
            $vtaxHeader = $vtaxSheet->first()->map(fn($val) => strtolower(trim($val)));

            $vtaxNopIndex = $vtaxHeader->search(fn($col) => Str::contains($col, 'nop'));
            $vtaxNominalIndex = $vtaxHeader->search(fn($col) => Str::contains($col, 'nominal'));

            if ($vtaxNopIndex !== false && $vtaxNominalIndex !== false) {
                $vtaxData = $vtaxSheet->skip(1)
                    ->map(function ($row) use ($vtaxNopIndex, $vtaxNominalIndex) {
                        return [
                            'nop'     => strtoupper(trim($row[$vtaxNopIndex] ?? '')),
                            'nominal' => (float) ($row[$vtaxNominalIndex] ?? 0),
                        ];
                    })
                    ->filter(fn($item) => $item['nop'] && is_numeric($item['nominal']))
                    ->groupBy('nop')
                    ->map(function ($rows) {
                        return [
                            'nop'     => $rows->first()['nop'],
                            'nominal' => collect($rows)->sum('nominal'),
                        ];
                    });
            }
        }

        // Ambil nama sheet dari file Bank
        $reader = IOFactory::createReaderForFile(storage_path('app/' . $this->bankPath));
        $spreadsheet = $reader->load(storage_path('app/' . $this->bankPath));
        $sheetNames = $spreadsheet->getSheetNames();

        // === Proses setiap sheet Bank ===
        foreach ($bankSheets as $index => $sheetData) {
            if ($sheetData->isEmpty()) continue;

            $sheetName = $sheetNames[$index] ?? 'Sheet ' . $index;

            $header = $sheetData->first()->map(fn($val) => strtolower(trim($val)));

            $nopIndex = $header->search(fn($col) => Str::contains($col, 'nop'));
            $nominalIndex = $header->search(fn($col) => Str::contains($col, 'nominal'));
            $tanggalIndex = $header->search(fn($col) => Str::contains($col, 'tanggal'));

            if ($nopIndex === false || $nominalIndex === false) {
                continue;
            }

            $bankData = $sheetData->skip(1)
              ->map(function ($row) use ($nopIndex, $nominalIndex, $tanggalIndex) {
                  return [
                      'nop'     => strtoupper(trim($row[$nopIndex] ?? '')),
                      'nominal' => (float) ($row[$nominalIndex] ?? 0),
                      'tanggal' => isset($tanggalIndex) && $tanggalIndex !== false
                          ? \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($row[$tanggalIndex] ?? '')
                              ? \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row[$tanggalIndex])->format('Y-m-d')
                              : trim($row[$tanggalIndex] ?? null)
                          : null,
                  ];
              })
              ->filter(fn($item) => $item['nop'] && is_numeric($item['nominal']))
              ->groupBy('nop')
              ->map(function ($rows) {
                  return [
                      'nop'     => $rows->first()['nop'],
                      'nominal' => collect($rows)->sum('nominal'),
                      'tanggal' => $rows->first()['tanggal'] ?? null,
                  ];
              });

            // === Bandingkan ===
            $allKeys = $bankData->keys()->merge($vtaxData->keys())->unique();
            $insertData = [];

            foreach ($allKeys as $key) {
                $bankNominal = $bankData[$key]['nominal'] ?? 0;
                $vtaxNominal = $vtaxData[$key]['nominal'] ?? 0;
                $selisih     = $bankNominal - $vtaxNominal;

                if ($selisih != 0) {
                    $insertData[] = [
                        'nop_bank'     => $bankData[$key]['nop'] ?? null,
                        'nominal_bank' => $bankNominal,
                        'nop_vtax'     => $vtaxData[$key]['nop'] ?? null,
                        'nominal_vtax' => $vtaxNominal,
                        'selisih'      => $selisih,
                        'sheet_name'   => $sheetName,
                        'tanggal'      => $bankData[$key]['tanggal'] ?? null,
                    ];
                }
            }

            foreach (array_chunk($insertData, 1000) as $chunk) {
                HasilTagihan::insert($chunk);
            }
        }
    }
}
