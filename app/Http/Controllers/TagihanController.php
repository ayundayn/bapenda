<?php

namespace App\Http\Controllers;
use App\Models\HasilTagihan;


use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Concerns\FromCollection;

class TagihanController extends Controller
{
    public function index()
    {
        return view('upload');
    }

    public function proses(Request $request)
    {
        $request->validate([
            'bank_excel' => 'required|file|mimes:xlsx,xls',
            'vtax_excel' => 'required|file|mimes:xlsx,xls',
        ]);

        // Kosongkan tabel
        HasilTagihan::truncate();

        // Ambil file Excel sebagai collection
        $bank = Excel::toCollection(null, $request->file('bank_excel'))[0];
        $vtax = Excel::toCollection(null, $request->file('vtax_excel'))[0];

        // Bersihkan baris kosong & header (jika perlu)
        $bank = $bank->filter(function ($row) {
            return isset($row[0]) && is_numeric(preg_replace('/[^0-9]/', '', $row[0]));
        });

        $vtax = $vtax->filter(function ($row) {
            return isset($row[0]) && is_numeric(preg_replace('/[^0-9]/', '', $row[0]));
        });

        // Ubah ke format [NOP => nominal]
        $bankData = collect($bank)->mapWithKeys(function ($row) {
            $nop = trim($row[0]);
            $nominal = str_replace([',', '.'], '', $row[1] ?? '0'); // hilangkan pemisah ribuan
            return [$nop => (float) $nominal];
        });

        $vtaxData = collect($vtax)->mapWithKeys(function ($row) {
            $nop = trim($row[0]);
            $nominal = str_replace([',', '.'], '', $row[1] ?? '0');
            return [$nop => (float) $nominal];
        });

        // Gabungkan semua NOP
        $allNop = $bankData->keys()->merge($vtaxData->keys())->unique();

        foreach ($allNop as $nop) {
            $bankNominal = $bankData[$nop] ?? 0;
            $vtaxNominal = $vtaxData[$nop] ?? 0;
            $selisih = $bankNominal - $vtaxNominal;

            // Hanya simpan kalau selisih ≠ 0
            if ($selisih != 0) {
                HasilTagihan::create([
                    'nop_bank' => $bankData->has($nop) ? $nop : null,
                    'nominal_bank' => $bankNominal,
                    'nop_vtax' => $vtaxData->has($nop) ? $nop : null,
                    'nominal_vtax' => $vtaxNominal,
                    'selisih' => $selisih,
                ]);
            }
        }

        $data = HasilTagihan::all();

        return view('hasil', ['data' => $data]);
    }


    public function download()
    {
        $data = HasilTagihan::all();

        return Excel::download(new class ($data) implements FromCollection {
            protected $data;
            public function __construct($data)
            {
                $this->data = $data;
            }

            public function collection()
            {
                return $this->data->map(function ($item) {
                    return [
                        'nop_bank' => $item->nop_bank,
                        'nominal_bank' => $item->nominal_bank,
                        'nop_vtax' => $item->nop_vtax,
                        'nominal_vtax' => $item->nominal_vtax,
                        'selisih' => $item->selisih,
                    ];
                });
            }
        }, 'hasil-selisih.xlsx');

    }
}
