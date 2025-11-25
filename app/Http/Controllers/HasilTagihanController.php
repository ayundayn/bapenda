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
      // Ambil progress terbaru
      $progress = \App\Models\Progress::latest()->first();

      // Ambil data hasil rekonsiliasi
      $query = \App\Models\HasilTagihan::query();

      // Filter berdasarkan sheet (kalau dipilih)
      if ($request->sheet) {
          $query->where('sheet_name', $request->sheet);
      }

      // Pagination
      $data = $query->paginate($request->perPage ?? 10);

      // Nama semua sheet
      $sheetNames = \App\Models\HasilTagihan::distinct()->pluck('sheet_name');

      // Kirim ke view
      return view('hasil-upload.index', [
          'data'       => $data,
          'sheetNames' => $sheetNames,
          'progress'   => $progress, // <––– VARIABLE PROGRESS
      ]);
  }

  public function downloadExcel()
  {
      $sheet = request('sheet');

      if ($sheet) {
          return Excel::download(new HasilTagihanExport($sheet), "hasil_rekon_{$sheet}.xlsx");
      }

      // Jika semua sheet ingin dijadikan satu file dengan banyak tab
      return Excel::download(new AllHasilTagihanExport, 'hasil_rekon_semua_sheet.xlsx');
  }
}
