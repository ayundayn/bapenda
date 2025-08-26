<?php

namespace App\Jobs;

use App\Models\HasilTagihan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        // Kosongkan tabel dulu
        HasilTagihan::truncate();

        // Load seluruh sheet dari Bank
        $bankSheets = Excel::toCollection(null, storage_path('app/' . $this->bankPath));
        $vtaxSheet = Excel::toCollection(null, storage_path('app/' . $this->vtaxPath))->first();

        $reader = IOFactory::createReaderForFile(storage_path('app/' . $this->bankPath));
        $spreadsheet = $reader->load(storage_path('app/' . $this->bankPath));
        $sheetNames = $spreadsheet->getSheetNames();

        foreach ($bankSheets as $index => $sheetData) {
            $sheetName = $sheetNames[$index] ?? 'Sheet ' . $index;

            $bankData = collect();
            foreach ($sheetData as $row) {
                $nop = $row['nop'] ?? $row[0] ?? null;
                $nominal = $row['nominal'] ?? $row[1] ?? null;

                if ($nop && is_numeric($nominal)) {
                    $bankData[$nop] = $nominal;
                }
            }

            $vtaxData = collect();
            foreach ($vtaxSheet as $row) {
                $nop = $row['nop'] ?? $row[0] ?? null;
                $nominal = $row['nominal'] ?? $row[1] ?? null;

                if ($nop && is_numeric($nominal)) {
                    $vtaxData[$nop] = $nominal;
                }
            }

            $allNop = $bankData->keys()->merge($vtaxData->keys())->unique();
            $insertData = [];

            foreach ($allNop as $nop) {
                $bankNominal = $bankData[$nop] ?? 0;
                $vtaxNominal = $vtaxData[$nop] ?? 0;
                $selisih = $bankNominal - $vtaxNominal;

                if ($selisih != 0) {
                    $insertData[] = [
                        'nop_bank' => $bankData->has($nop) ? $nop : null,
                        'nominal_bank' => $bankNominal,
                        'nop_vtax' => $vtaxData->has($nop) ? $nop : null,
                        'nominal_vtax' => $vtaxNominal,
                        'selisih' => $selisih,
                        'sheet_name' => $sheetName, // Ini yang dipakai buat filter
                    ];
                }
            }

            foreach (array_chunk($insertData, 1000) as $chunk) {
                HasilTagihan::insert($chunk);
            }
        }
    }
}
