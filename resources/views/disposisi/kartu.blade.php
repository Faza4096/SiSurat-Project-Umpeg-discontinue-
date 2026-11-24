<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Kartu Disposisi {{ $d->nomorSurat() }} — SiSurat</title>
    <link rel="icon" type="image/png" href="{{ asset('img/lambang-kabupaten-bogor.png') }}">
    @vite(['resources/css/app.css'])
    <style>
        /* Setengah folio: 16,5 cm × 21,5 cm. */
        @page { size: 165mm 215mm; margin: 10mm; }

        .kartu-kertas {
            box-sizing: border-box;
            width: 165mm;
            max-width: 100%;
            margin: 0 auto;
            padding: 10mm;
            background: #fff;
            border: 1px solid #d7dee7;
        }

        @media screen and (max-width: 760px) {
            .kartu-kertas { padding: 6mm 5mm; }
            .kd-kop-logo { width: 16mm; }
            .kd-kop-logo img { width: 14mm; }
            .kd-kop-nama { font-size: 11pt; }
            .kd-data > tbody > tr > td.kd-lbl-col { width: 19mm; font-size: 8.5pt; }
            .kd-dua > tbody > tr > td { height: auto; }
        }

        /* Di layar HP kedua kolom ditumpuk agar tetap terbaca tanpa menggeser ke samping. */
        @media screen and (max-width: 520px) {
            .kd-atas > tbody > tr > td { display: block; width: 100%; }
            .kd-atas > tbody > tr > td + td { border-left: 0; }
            .kd-dua > tbody > tr > td.kd-kiri,
            .kd-dua > tbody > tr > td.kd-kanan {
                display: block;
                width: 100%;
                height: auto;
                border-right: 0;
                border-bottom: 0;
            }
            .kd-dua > tbody > tr > td.kd-kiri {
                border-bottom: 1px solid #000;
                min-height: 30mm;
            }
            .kd-dua > tbody > tr > td.kd-kanan { min-height: 20mm; }
            .kd-instruksi { font-size: 8.5pt; }
        }

        @media print {
            body { background: #fff !important; padding: 0 !important; }
            .toolbar-kartu { display: none !important; }
            .kartu-kertas { width: auto; max-width: none; margin: 0; padding: 0; border: 0; }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 py-6">
@php
    $instansi = \App\Models\PengaturanInstansi::current();
    $logo = $instansi->logoDataUri();
@endphp

<div class="toolbar-kartu mx-auto mb-4 flex w-full max-w-[165mm] flex-wrap items-center justify-between gap-3 border border-slate-200 bg-white px-4 py-3">
    <div class="min-w-0">
        <p class="text-[10px] font-bold uppercase tracking-[0.16em] text-slate-500">Kartu Disposisi · ½ Folio</p>
        <p class="truncate font-mono text-sm font-semibold text-gov-900">{{ $d->nomorSurat() }}</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $d->tautanSurat() }}" class="gov-btn-outline">
            <x-icon name="arrow-left" class="h-4 w-4" /> Kembali
        </a>
        <a href="{{ route('disposisi.pdf', $d) }}" class="gov-btn-outline">
            <x-icon name="download" class="h-4 w-4" /> Unduh PDF
        </a>
        <button type="button" onclick="window.print()" class="gov-btn">
            <x-icon name="print" class="h-4 w-4" /> Cetak
        </button>
    </div>
</div>

<div class="kartu-kertas">
    @include('disposisi._kartu', ['d' => $d, 'instansi' => $instansi, 'logo' => $logo])
</div>
</body>
</html>
