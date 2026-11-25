@props(['value' => '', 'type' => 'status'])

@php
    $peta = [
        'status' => [
            'diterima' => ['Diterima', 'tone-info'],
            'didisposisi' => ['Didisposisi', 'tone-wait'],
            'diproses' => ['Diproses', 'tone-info'],
            'selesai' => ['Selesai', 'tone-done'],
            'diarsipkan' => ['Diarsipkan', 'tone-muted'],
            'draf' => ['Draf', 'tone-muted'],
            'menunggu_ttd' => ['Menunggu TTD', 'tone-wait'],
            'revisi' => ['Perlu Revisi', 'tone-alert'],
            'disetujui' => ['Disetujui', 'tone-done'],
            'terkirim' => ['Terkirim', 'tone-done'],
            'belum' => ['Belum', 'tone-muted'],
        ],
        'sifat' => [
            'biasa' => ['Biasa', 'tone-muted'],
            'segera' => ['Segera', 'tone-wait'],
            'penting' => ['Penting', 'tone-urgent'],
            'rahasia' => ['Rahasia', 'tone-alert'],
        ],
        'role' => [
            'admin' => ['Administrator', 'tone-info'],
            'petugas_tu' => ['Petugas TU', 'tone-wait'],
            'pimpinan' => ['Pimpinan', 'tone-done'],
            'staf' => ['Staf/Umpeg', 'tone-muted'],
        ],
    ];

    [$label, $kelas] = $peta[$type][$value] ?? [\Illuminate\Support\Str::headline($value ?: '-'), 'tone-muted'];
@endphp

<span {{ $attributes->merge(['class' => "gov-badge ring-1 ring-inset {$kelas}"]) }}>{{ $label }}</span>
