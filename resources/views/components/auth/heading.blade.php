@props(['eyebrow', 'judul', 'keterangan' => null])

<div class="mb-6">
    <p class="gov-eyebrow text-gold-600">{{ $eyebrow }}</p>
    <h2 class="mt-1 font-display text-[25px] font-bold leading-tight tracking-[-0.025em] text-gov-900">{{ $judul }}</h2>
    @if ($keterangan)
        <p class="mt-1.5 text-sm text-slate-500">{{ $keterangan }}</p>
    @endif
</div>
