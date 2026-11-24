<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Kartu Disposisi — {{ $instansi->nama }}</title>
    <style>
        /* Setengah folio: 16,5 cm × 21,5 cm. */
        @page { size: 165mm 215mm; margin: 10mm; }
        body { margin: 0; font-family: "Times New Roman", Times, serif; color: #000; }
    </style>
</head>
<body>
@foreach ($items as $d)
    <div class="{{ $loop->last ? 'kd-halaman-terakhir' : 'kd-halaman' }}">
        @include('disposisi._kartu', ['d' => $d, 'instansi' => $instansi, 'logo' => $logo])
    </div>
@endforeach
</body>
</html>
