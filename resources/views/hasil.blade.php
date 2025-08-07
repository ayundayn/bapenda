@extends('layouts/contentNavbarLayout')

@section('title', 'Tabel Hasil Upload')

@section('page-script')
  @vite('resources/assets/js/form-basic-inputs.js')
@endsection

@section('content')

@if ($data->count() === 0)
    <div class="alert alert-info">
      Data sedang diproses, harap tunggu...
    </div>

    {{-- Auto Refresh tiap 5 detik --}}
    <script>
      setTimeout(() => {
        location.reload();
      }, 5000); // 5 detik
    </script>
  @endif

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Hasil Upload Excel</h5>
    <a href="{{ route('download.excel') }}" class="btn btn-success">
      <i class="bx bx-download"></i> Download Excel
    </a>
  </div>
  <div class="table-responsive text-nowrap">
    <table class="table">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>NOP Bank</th>
          <th>Nominal Bank</th>
          <th>NOP VTax</th>
          <th>Nominal VTax</th>
          <th>Selisih</th>
        </tr>
      </thead>
      <tbody class="table-border-bottom-0">
        @forelse ($data as $index => $row)
          <tr>
            <td>{{ $index + 1 }}</td>
            <td>{{ $row->nop_bank ?? '-' }}</td>
            <td>Rp {{ number_format($row->nominal_bank, 0, ',', '.') }}</td>
            <td>{{ $row->nop_vtax ?? '-' }}</td>
            <td>Rp {{ number_format($row->nominal_vtax, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->selisih, 0, ',', '.') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="6" class="text-center text-muted">Tidak ada data ditampilkan.</td>
          </tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection