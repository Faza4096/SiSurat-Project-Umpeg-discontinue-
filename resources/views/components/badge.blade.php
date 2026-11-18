@props(['value' => '', 'type' => 'status'])

@php
    $peta = [
        'status' => [
            'diterima' => ['Diterima', 'bg-gov-50 text-gov-700 ring-gov-200'],
            'didisposisi' => ['Didisposisi', 'bg-gold-50 text-gold-700 ring-gold-200'],
            'diproses' => ['Diproses', 'bg-indigo-50 text-indigo-700 ring-indigo-200'],
            'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
            'diarsipkan' => ['Diarsipkan', 'bg-slate-100 text-slate-600 ring-slate-300'],
            'draf' => ['Draf', 'bg-slate-100 text-slate-600 ring-slate-200'],
            'menunggu_ttd' => ['Menunggu TTD', 'bg-amber-50 text-amber-700 ring-amber-200'],
            'revisi' => ['Perlu Revisi', 'bg-red-50 text-red-700 ring-red-200'],
            'disetujui' => ['Disetujui', 'bg-sky-50 text-sky-700 ring-sky-200'],
            'terkirim' => ['Terkirim', 'bg-emerald-50 text-emerald-700 ring-emerald-200'],
            'belum' => ['Belum', 'bg-slate-100 text-slate-600 ring-slate-200'],
        ],
        'sifat' => [
            'biasa' => ['Biasa', 'bg-slate-100 text-slate-600 ring-slate-200'],
            'segera' => ['Segera', 'bg-amber-50 text-amber-700 ring-amber-200'],
            'penting' => ['Penting', 'bg-orange-50 text-orange-700 ring-orange-200'],
            'rahasia' => ['Rahasia', 'bg-red-50 text-red-700 ring-red-200'],
        ],
        'role' => [
            'admin' => ['Administrator', 'bg-gov-50 text-gov-700 ring-gov-200'],
            'staf' => ['Staf', 'bg-slate-100 text-slate-600 ring-slate-200'],
        ],
    ];

    [$label, $kelas] = $peta[$type][$value] ?? [\Illuminate\Support\Str::headline($value ?: '-'), 'bg-slate-100 text-slate-600 ring-slate-200'];
@endphp

<span {{ $attributes->merge(['class' => "gov-badge ring-1 ring-inset {$kelas}"]) }}>{{ $label }}</span>
