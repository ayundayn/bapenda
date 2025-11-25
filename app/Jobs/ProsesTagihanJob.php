<?php

namespace App\Jobs;

use App\Models\HasilTagihan;
use App\Models\Progress;
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
    protected $progressId;

    public function __construct($bankPath, $vtaxPath, $progressId)
    {
        $this->bankPath = $bankPath;
        $this->vtaxPath = $vtaxPath;
        $this->progressId = $progressId;
    }

    public function handle(): void
    {
        ini_set('max_execution_time', 0);
        ini_set('memory_limit', '1024M');

        HasilTagihan::truncate();

        $progress = Progress::find($this->progressId);
        $progress->update(['status' => 'processing']);

        HasilTagihan::truncate();
        // ================= TOTAL ROWS ====================
        $total = 0;

        // hitung total row VTax
        $vtaxSheets = Excel::toCollection(null, storage_path('app/' . $this->vtaxPath));
        foreach ($vtaxSheets as $sheet) {
            $total += max(0, $sheet->count() - 1);
        }

        // hitung total row Bank
        $bankSheets = Excel::toCollection(null, storage_path('app/' . $this->bankPath));
        foreach ($bankSheets as $sheet) {
            $total += max(0, $sheet->count() - 1);
        }

        // simpan total
        $progress->update(['total' => $total]);

        $processed = 0;

        // ================= PROCESS VTAX ====================
        $vtaxData = collect();
        $vtaxSheet = $vtaxSheets->first();

        if ($vtaxSheet && !$vtaxSheet->isEmpty()) {
            $header = $vtaxSheet->first()->map(fn($v) => strtolower(trim($v)));

            $nopIndex = $header->search(fn($c) => Str::contains($c, 'nop'));
            $nominalIndex = $header->search(fn($c) => Str::contains($c, 'nominal'));

            $vtaxData = $vtaxSheet->skip(1)
                ->map(function ($row) use ($nopIndex, $nominalIndex) {
                    return [
                        'nop' => strtoupper(trim($row[$nopIndex] ?? '')),
                        'nominal' => (float) ($row[$nominalIndex] ?? 0),
                    ];
                })
                ->filter(fn($v) => $v['nop'])
                ->groupBy('nop')
                ->map(function ($rows) {
                    return [
                        'nop' => $rows->first()['nop'],
                        'nominal' => collect($rows)->sum('nominal'),
                    ];
                });

            // update progress
            $processed += max(0, $vtaxSheet->count() - 1);
            $progress->update(['processed' => $processed]);
        }

        // Ambil nama sheet Bank
        $reader = IOFactory::createReaderForFile(storage_path('app/' . $this->bankPath));
        $spreadsheet = $reader->load(storage_path('app/' . $this->bankPath));
        $sheetNames = $spreadsheet->getSheetNames();

        // ================= PROCESS BANK ====================
        foreach ($bankSheets as $index => $sheetData) {
            if ($sheetData->isEmpty()) continue;

            $sheetName = $sheetNames[$index] ?? "Sheet $index";

            $header = $sheetData->first()->map(fn($v) => strtolower(trim($v)));

            $nopIndex = $header->search(fn($c) => Str::contains($c, 'nop'));
            $nominalIndex = $header->search(fn($c) => Str::contains($c, 'nominal'));
            $tanggalIndex = $header->search(fn($c) => Str::contains($c, 'tanggal'));

            $bankData = $sheetData->skip(1)
                ->map(function ($row) use ($nopIndex, $nominalIndex, $tanggalIndex) {
                    return [
                        'nop'     => strtoupper(trim($row[$nopIndex] ?? '')),
                        'nominal' => (float) ($row[$nominalIndex] ?? 0),
                        'tanggal' => $row[$tanggalIndex] ?? null,
                    ];
                })
                ->filter(fn($v) => $v['nop'])
                ->groupBy('nop')
                ->map(function ($rows) {
                    return [
                        'nop' => $rows->first()['nop'],
                        'nominal' => collect($rows)->sum('nominal'),
                        'tanggal' => $rows->first()['tanggal'],
                    ];
                });

            // Compare
            $allKeys = $bankData->keys()->merge($vtaxData->keys())->unique();
            $insertData = [];

            foreach ($allKeys as $key) {
                $bankRow = $bankData->get($key);
                $vtaxRow = $vtaxData->get($key);

                $bankNom = $bankRow['nominal'] ?? 0;
                $vtaxNom = $vtaxRow['nominal'] ?? 0;

                $selisih = $bankNom - $vtaxNom;

                if ($selisih != 0) {
                    $insertData[] = [
                        'nop_bank'     => $bankRow['nop'] ?? null,
                        'nominal_bank' => $bankNom,
                        'nop_vtax'     => $vtaxRow['nop'] ?? null,
                        'nominal_vtax' => $vtaxNom,
                        'selisih'      => $selisih,
                        'sheet_name'   => $sheetName,
                        'tanggal'      => null,
                    ];
                }
            }

            foreach (array_chunk($insertData, 1000) as $chunk) {
                HasilTagihan::insert($chunk);
            }

            // update progress setiap sheet
             $processed++;
            $progress->update(['processed' => $processed]);
        }

        // selesai
        $progress->update(['status' => 'done']);
    }
}
