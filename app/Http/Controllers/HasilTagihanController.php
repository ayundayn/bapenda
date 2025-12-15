<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilTagihan;
use App\Models\Progress;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HasilTagihanExport;
use App\Exports\AllHasilTagihanExport;
use Carbon\Carbon;

class HasilTagihanController extends Controller
{
    /**
     * Tampilkan halaman daftar hasil tagihan
     */
    public function index(Request $request)
    {
        // 1️⃣ Ambil progress terakhir
        $progress = Progress::latest()->first();

        // 2️⃣ Isi kolom tahun untuk data lama yang kosong
        HasilTagihan::whereNull('tahun')->get()->each(function($row) {
            if ($row->tanggal) {
                $row->tahun = Carbon::parse($row->tanggal)->format('Y');
                $row->save();
            }
        });

        // 3️⃣ Mulai query dari model HasilTagihan
        $query = HasilTagihan::query();

        // Filter berdasarkan sheet jika ada request
        if ($request->sheet) {
            $query->where('sheet_name', $request->sheet);
        }

        // Filter berdasarkan tanggal jika dipilih
        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // 4️⃣ Ambil data dengan pagination (default 10 per halaman)
        $data = $query->orderBy('tanggal', 'desc')->paginate($request->perPage ?? 10);

        // 5️⃣ Ambil semua nama sheet unik
        $sheetNames = HasilTagihan::distinct()->pluck('sheet_name');

        // 6️⃣ Ambil semua tahun unik sebagai string dipisah koma
        $tahunString = HasilTagihan::getTahunUnikString(); // <--- method statik di model

        // 7️⃣ Kirim data ke view
        return view('hasil_tagihan.index', [ // pastikan nama view sesuai Blade
            'data'        => $data,
            'sheetNames'  => $sheetNames,
            'progress'    => $progress,
            'tanggal'     => $request->tanggal, // agar form retain value
            'tahunString' => $tahunString,      // semua tahun unik
        ]);
    }

    /**
     * Download hasil tagihan dalam format Excel
     */
    public function downloadExcel()
    {
        $sheet = request('sheet');

        if ($sheet) {
            // Download hanya sheet yang dipilih
            return Excel::download(new HasilTagihanExport($sheet), "hasil_rekon_{$sheet}.xlsx");
        }

        // Download semua sheet
        return Excel::download(new AllHasilTagihanExport, 'hasil_rekon_semua_sheet.xlsx');
    }
}
