<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\HasilTagihan;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\HasilTagihanExport;

class HasilTagihanController extends Controller
{
  public function index()
  {
    $query = HasilTagihan::query();

    // Ambil semua nama sheet unik dari DB (sudah diisi saat job jalan)
    $sheetNames = HasilTagihan::select('sheet_name')
      ->distinct()
      ->pluck('sheet_name');

    // Filter berdasarkan sheet jika ada
    if (request('sheet')) {
      $query->where('sheet_name', request('sheet'));
    }

    // Pagination dengan pilihan perPage
    $perPage = request('perPage', 10);
    $data = $query->paginate($perPage);

    return view('hasil-upload.index', compact('data', 'sheetNames'));
  }

  public function downloadExcel()
  {
    $sheet = request('sheet');

    return Excel::download(new HasilTagihanExport($sheet), 'hasil_rekon.xlsx');
  }
}
