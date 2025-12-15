@extends('layouts/contentNavbarLayout')

@section('title', 'Tabel Hasil Upload')

@section('page-script')
  @vite('resources/assets/js/form-basic-inputs.js')
@endsection

@section('content')

{{-- Progress Bar --}}
@if ($data->count() === 0)
  <div id="progressBox" style="display:none;">
    <div class="alert alert-info">
        Sedang memproses data...
        <br>
        <strong id="progressText">0 / 0</strong>
        <div class="progress mt-2" style="height: 20px;">
            <div id="progressBar" class="progress-bar" role="progressbar"></div>
        </div>
    </div>
  </div>

  <script>
    function checkProgress() {
        fetch('/progress')
            .then(res => res.json())
            .then(data => {
                if (!data) return;

                document.getElementById('progressBox').style.display = 'block';
                document.getElementById('progressText').innerText =
                    `${data.processed} / ${data.total}`;

                const percent = data.total > 0
                    ? (data.processed / data.total * 100)
                    : 0;

                document.getElementById('progressBar').style.width = percent + '%';

                if (data.status !== 'done') {
                    setTimeout(checkProgress, 1500);
                } else {
                    location.reload();
                }
            });
    }

    checkProgress();
  </script>
@endif

<div class="card">
  <div class="card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center">
    <h5 class="mb-3 mb-md-0">Hasil Upload Excel</h5>

    <div class="d-flex flex-column flex-sm-row gap-2 align-items-start align-items-sm-center">

      {{-- Filter Sheet --}}
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

        <input type="hidden" name="perPage" value="{{ request('perPage', 10) }}">
      </form>

      {{-- Filter Per Page --}}
      <form method="GET" class="d-flex align-items-center">
        <label for="perPage" class="me-2 mb-0">Tampilkan</label>
        <select name="perPage" id="perPage" class="form-select form-select-sm w-auto me-2" onchange="this.form.submit()">
          @foreach ([10, 25, 50, 100] as $size)
            <option value="{{ $size }}" {{ request('perPage') == $size ? 'selected' : '' }}>{{ $size }}</option>
          @endforeach
        </select>

        <input type="hidden" name="sheet" value="{{ request('sheet') }}">
      </form>

      {{-- Tombol Download --}}
      <a href="{{ route('download.excel', request()->only(['sheet'])) }}" class="btn btn-success btn-sm">
        <i class="bx bx-download"></i> Download Excel
      </a>
    </div>
  </div>

  {{-- Tampilkan Tahun Unik --}}
  @if(isset($tahunString) && $tahunString)
    <div class="p-3">
      <strong>Tahun tersedia:</strong> {{ $tahunString }}
    </div>
  @endif

  <div class="table-responsive text-nowrap">
    <table class="table table-hover">
      <thead class="table-light">
        <tr>
          <th>No</th>
          <th>Tanggal Bayar</th>
          <th>Tahun</th>
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
            <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('d-m-Y') : '-' }}</td>
            <td>{{ $row->tanggal ? \Carbon\Carbon::parse($row->tanggal)->format('Y') : '-' }}</td>
            <td>{{ $row->nop_bank ?? '-' }}</td>
            <td>Rp {{ number_format($row->nominal_bank, 0, ',', '.') }}</td>
            <td>{{ $row->nop_vtax ?? '-' }}</td>
            <td>Rp {{ number_format($row->nominal_vtax, 0, ',', '.') }}</td>
            <td>Rp {{ number_format($row->selisih, 0, ',', '.') }}</td>
          </tr>
        @empty
          <tr>
            <td colspan="8" class="text-center text-muted">Tidak ada data ditampilkan.</td>
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
