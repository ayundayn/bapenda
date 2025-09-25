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

  <script>
    setTimeout(() => {
      location.reload();
    }, 5000);
  </script>
@endif

<div class="card">
  <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h5 class="mb-3 mb-md-0">Hasil Upload Excel</h5>

    <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">

      {{-- Dropdown Filter Sheet --}}
      <form method="GET" class="d-flex align-items-center">
        <label for="sheet" class="me-2 mb-0">Sheet</label>
        <select name="sheet" id="sheet" class="form-select form-select-sm w-auto me-2" onchange="this.form.submit()">
          <option value="">Semua Sheet</option>
          @foreach ($sheetNames as $sheet)
            <option value="{{ $sheet }}" {{ request('sheet') == $sheet ? 'selected' : '' }}>
              {{ $sheet }}
            </option>
          @endforeach
        </select>

        {{-- Hidden input biar dropdown perPage ikut dikirim --}}
        <input type="hidden" name="perPage" value="{{ request('perPage', 10) }}">
      </form>

      {{-- Dropdown Tampilkan Per Page --}}
      <form method="GET" class="d-flex align-items-center">
        <label for="perPage" class="me-2 mb-0">Tampilkan</label>
        <select name="perPage" id="perPage" class="form-select form-select-sm w-auto me-2" onchange="this.form.submit()">
          @foreach ([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" {{ request('perPage') == $size ? 'selected' : '' }}>{{ $size }}</option>
          @endforeach
        </select>

        {{-- Hidden input biar dropdown sheet ikut dikirim --}}
        <input type="hidden" name="sheet" value="{{ request('sheet') }}">
      </form>

      {{-- Tombol Download --}}
      <a href="{{ route('download.excel', request()->only(['sheet'])) }}" class="btn btn-success btn-sm">
        <i class="bx bx-download"></i> Download Excel
      </a>
    </div>
  </div>

  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
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
      <tbody>
        @forelse ($data as $index => $row)
          <tr>
            <td>{{ ($data->currentPage() - 1) * $data->perPage() + $index + 1 }}</td>
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

    {{-- Pagination --}}
    <div class="d-flex justify-content-between align-items-center mt-3 px-3">
      <div class="text-muted small">
        Menampilkan <strong>{{ $data->firstItem() }}</strong> sampai <strong>{{ $data->lastItem() }}</strong> dari <strong>{{ $data->total() }}</strong> hasil
      </div>
      <div>
        {{ $data->appends(request()->except('page'))->links('vendor.pagination.bootstrap-5') }}
      </div>
    </div>
  </div>
</div>
@endsection
