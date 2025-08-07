<?php

namespace App\Jobs;

use App\Models\HasilTagihan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BankImport;
use App\Imports\VTaxImport;


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

        $bankCollection = Excel::toCollection(new BankImport, storage_path("app/{$this->bankPath}"))[0];
        $vtaxCollection = Excel::toCollection(new VTaxImport, storage_path("app/{$this->vtaxPath}"))[0];    

        $bankData = $bankCollection->mapWithKeys(fn ($row) => [$row['nop'] => $row['nominal']]);
        $vtaxData = $vtaxCollection->mapWithKeys(fn ($row) => [$row['nop'] => $row['nominal']]);

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
                ];
            }
        }

        foreach (array_chunk($insertData, 1000) as $chunk) {
            HasilTagihan::insert($chunk);
        }
    }
}