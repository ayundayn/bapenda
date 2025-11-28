<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilTagihan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HasilTagihanExport;
use App\Exports\AllHasilTagihanExport;

class HasilTagihanController extends Controller
{
    public function index(Request $request)
    {
        $progress = \App\Models\Progress::latest()->first();

        $query = \App\Models\HasilTagihan::query();

        // Filter berdasarkan sheet
        if ($request->sheet) {
            $query->where('sheet_name', $request->sheet);
        }

        // Filter berdasarkan tanggal jika dipilih
        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }

        // Pagination
        $data = $query->paginate($request->perPage ?? 10);

        // Nama semua sheet
        $sheetNames = \App\Models\HasilTagihan::distinct()->pluck('sheet_name');

        return view('hasil-upload.index', [
            'data'       => $data,
            'sheetNames' => $sheetNames,
            'progress'   => $progress,
            'tanggal'    => $request->tanggal, // kirim ke view biar form tetap retain
        ]);
    }

    public function downloadExcel()
    {
        $sheet = request('sheet');

        if ($sheet) {
            return Excel::download(new HasilTagihanExport($sheet), "hasil_rekon_{$sheet}.xlsx");
        }

        return Excel::download(new AllHasilTagihanExport, 'hasil_rekon_semua_sheet.xlsx');
    }
}
