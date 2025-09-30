@extends('layouts/contentNavbarLayout')

@section('title', 'Rekonsiliasi')

@section('page-script')
  @vite('resources/assets/js/form-basic-inputs.js')
@endsection

@section('content')
  <h2>Upload Dua File Excel</h2>
  @if (session('status'))
  <div class="alert alert-info alert-dismissible fade show" role="alert">
    {{ session('status') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
  </div>
@endif

  <form action="{{ route('upload.proses') }}" method="POST" enctype="multipart/form-data">
      @csrf

      <div class="mb-3">
          <label for="bank_excel" class="form-label">File Bank:</label>
          <input type="file" class="form-control" id="bank_excel" name="bank_excel" required>
      </div>

      <div class="mb-3">
          <label for="vtax_excel" class="form-label">File VTax:</label>
          <input type="file" class="form-control" id="vtax_excel" name="vtax_excel" required>
      </div>

      <button type="submit" class="btn btn-primary">Proses</button>
  </form>
@endsection
