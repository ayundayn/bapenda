<table border="1" cellpadding="5" cellspacing="0">
    <thead>
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
        @foreach($data as $index => $row)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $row->nop_bank ?? '-' }}</td>
                <td>{{ number_format($row->nominal_bank, 0) }}</td>
                <td>{{ $row->nop_vtax ?? '-' }}</td>
                <td>{{ number_format($row->nominal_vtax, 0) }}</td>
                <td>{{ number_format($row->selisih, 0) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<br>

<a href="{{ route('download.excel') }}">Download Excel</a>