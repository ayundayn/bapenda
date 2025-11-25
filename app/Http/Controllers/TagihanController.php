<?php

namespace App\Http\Controllers;
use App\Models\HasilTagihan;
use App\Models\Progress;

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

        // Buat progress baru
         $progress = Progress::create([
        'total' => 0,
        'processed' => 0,
        'status' => 'processing',
    ]);

    // 3. dispatch job
    ProsesTagihanJob::dispatch($bankPath, $vtaxPath, $progress->id);

        return redirect()->route('hasil.view')->with('status', 'File sedang diproses. Silakan cek hasil beberapa saat lagi.');
    }

    public function hasil(Request $request)
    {
        $perPage = $request->get('perPage', 10);
        $selectedSheet = $request->get('sheet'); // ambil sheet yang dipilih user

        // Ambil semua nama sheet unik dari DB
        $sheetNames = HasilTagihan::select('sheet_name')->distinct()->pluck('sheet_name');

        // Query hasil sesuai sheet
        $query = HasilTagihan::query();

        if ($selectedSheet) {
            $query->where('sheet_name', $selectedSheet);
        }

        $data = $query->paginate($perPage);

        return view('hasil', compact('data', 'sheetNames', 'selectedSheet'));
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
