<!DOCTYPE html>
<html>

<head>
    <title>Upload Excel</title>
</head>

<body>
    <h2>Upload Dua File Excel</h2>
    <form action="{{ route('upload.proses') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <label>File Bank:</label><br>
        <input type="file" name="bank_excel" required><br><br>

        <label>File VTax:</label><br>
        <input type="file" name="vtax_excel" required><br><br>

        <button type="submit">Proses</button>
    </form>
</body>

</html>