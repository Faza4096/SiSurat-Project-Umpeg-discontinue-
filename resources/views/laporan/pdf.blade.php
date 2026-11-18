<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Surat {{ ucfirst($jenis) }} — SiSurat</title>
    <style>
        @page { size: A4 landscape; margin: 12mm 12mm; }
        * { box-sizing: border-box; }
        body { font-family: "Times New Roman", Times, serif; font-size: 9.5pt; color: #000; margin: 0; }
        .kop { text-align: center; border-bottom: 2.5px solid #000; padding-bottom: 4mm; margin-bottom: 5mm; }
        .kop h1 { margin: 0; font-size: 14pt; letter-spacing: .3px; }
        .kop h2 { margin: 1mm 0 0; font-size: 11pt; font-weight: 700; text-transform: uppercase; }
        .kop p { margin: 1mm 0 0; font-size: 8pt; }
        table { width: 100%; border-collapse: collapse; margin-top: 3mm; }
        th, td { border: 1px solid #444; padding: 1.6mm 2mm; vertical-align: top; text-align: left; font-size: 8.5pt; }
        th { background: #e8edf3; font-weight: 700; text-transform: uppercase; letter-spacing: .3px; }
        .ringkas { margin: 2mm 0 3mm; font-size: 9pt; }
        .ringkas span { display: inline-block; margin-right: 6mm; }
        .ttd { margin-top: 8mm; width: 60mm; float: right; text-align: center; font-size: 9pt; }
        .kecil { font-size: 8pt; color: #333; }
        .toolbar { margin-bottom: 4mm; }
        .toolbar button { font-family: inherit; padding: 2mm 4mm; border: 1px solid #123; background: #0b2036; color: #fff; border-radius: 2mm; cursor: pointer; }
        @media print { .toolbar { display: none; } }
    </style>
</head>
<body>
@if (!isset($pdf) || !$pdf)
    <div class="toolbar no-print">
        <button onclick="window.print()">Cetak / Simpan sebagai PDF</button>
    </div>
@endif

<div class="kop">
    <h1>{{ mb_strtoupper($identitas->nama) }}</h1>
    <h2>Laporan Arsip Surat {{ ucfirst($jenis) }}</h2>
    <p>{{ $identitas->namaUnit() }} · Sistem Otomatisasi Registrasi Surat (SiSurat) · Dicetak {{ now()->translatedFormat('d F Y H:i') }}</p>
</div>

<div class="ringkas">
    <span><strong>Jumlah data:</strong> {{ $data->count() }} surat</span>
    @if (! empty($filter['dari']) || ! empty($filter['sampai']))
        <span><strong>Periode:</strong> {{ $filter['dari'] ?: '…' }} s.d. {{ $filter['sampai'] ?: '…' }}</span>
    @endif
    @foreach ($ringkasan as $status => $jumlah)
        <span>{{ \Illuminate\Support\Str::headline($status) }}: {{ $jumlah }}</span>
    @endforeach
</div>

<table>
    <thead>
        <tr>
            <th style="width: 4%">No</th>
            <th style="width: 14%">Nomor</th>
            <th style="width: 16%">{{ $jenis === 'masuk' ? 'Pengirim' : 'Tujuan' }}</th>
            <th>Perihal / Hal</th>
            <th style="width: 10%">Klasifikasi</th>
            <th style="width: 11%">Tanggal</th>
            <th style="width: 11%">Status</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($data as $i => $s)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $jenis === 'masuk' ? $s->no_agenda : $s->no_surat }}</td>
                <td>{{ $s->instansi?->nama ?? '-' }}</td>
                <td>{{ $jenis === 'masuk' ? $s->perihal : $s->hal }}</td>
                <td>{{ $s->klasifikasi?->kode ?? '-' }}</td>
                <td>{{ ($jenis === 'masuk' ? $s->tgl_diterima : $s->tgl_surat)?->format('d/m/Y') }}</td>
                <td>{{ \Illuminate\Support\Str::headline($s->status) }}</td>
            </tr>
        @empty
            <tr><td colspan="7" style="text-align:center; padding:8mm;">Tidak ada data pada filter tersebut.</td></tr>
        @endforelse
    </tbody>
</table>

<div class="ttd">
    <p>Bogor, {{ now()->translatedFormat('d F Y') }}</p>
    <p>Pengelola Tata Naskah</p>
    <div style="height: 16mm;"></div>
    <p style="border-top:1px solid #000; padding-top:1mm;">{{ auth()->user()->name ?? 'Staf' }}</p>
    <p class="kecil">NIP {{ auth()->user()->nip ?: '-' }}</p>
</div>
</body>
</html>
