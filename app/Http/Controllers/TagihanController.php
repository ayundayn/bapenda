<?php

namespace App\Http\Controllers;
use App\Models\HasilTagihan;


use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Excel as ExcelWriter;
use Maatwebsite\Excel\Concerns\FromCollection;
use App\Jobs\ProsesTagihanJob;
use Maatwebsite\Excel\HeadingRowImport;
use PhpOffice\PhpSpreadsheet\IOFactory;

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

    $bankPath = $request->file('bank_excel')->store('uploads');
    $vtaxPath = $request->file('vtax_excel')->store('uploads');

    // Kirim ke antrian
    ProsesTagihanJob::dispatch($bankPath, $vtaxPath);

    return redirect()->route('hasil.view')->with('status', 'File sedang diproses. Silakan cek hasil beberapa saat lagi.');
}

public function hasil()
{
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