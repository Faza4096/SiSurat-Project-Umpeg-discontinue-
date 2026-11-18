@extends('layouts.sisurat')

@section('judul', 'Perlu Tindakan')
@section('subjudul', 'Ringkasan kerja ' . $instansi . ' · ' . now()->translatedFormat('l, d F Y'))

@section('aksi')
    @if (in_array($peran, ['staf', 'admin'], true))
        <a href="{{ route('surat-masuk.create') }}" class="gov-btn">
            <x-icon name="plus" class="h-4 w-4" /> Catat Surat Masuk
        </a>
        <a href="{{ route('surat-keluar.create') }}" class="gov-btn-outline">
            <x-icon name="edit" class="h-4 w-4" /> Buat Surat Keluar
        </a>
    @endif
@endsection

@section('isi')
    <dl class="gov-card mb-8 grid grid-cols-1 sm:grid-cols-4">
        <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 px-5 py-3 sm:block sm:border-b-0 sm:py-4">
            <dt class="gov-eyebrow">Bulan ini:</dt>
            <dd class="text-[12px] leading-snug text-slate-500 sm:mt-1">{{ now()->translatedFormat('F Y') }}</dd>
        </div>
        <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 px-5 py-3 sm:block sm:border-b-0 sm:border-l sm:border-slate-100 sm:py-4">
            <dt class="gov-eyebrow">Surat masuk</dt>
            <dd class="sm:mt-2">
                <a href="{{ route('surat-masuk.index') }}" aria-label="Buka daftar surat masuk"
                   class="gov-stat text-[19px] underline-offset-4 hover:text-gov-700 hover:underline sm:text-[24px]">{{ number_format($ringkasan['masuk'], 0, ',', '.') }}</a>
            </dd>
        </div>
        <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 px-5 py-3 sm:block sm:border-b-0 sm:border-l sm:border-slate-100 sm:py-4">
            <dt class="gov-eyebrow">Surat keluar</dt>
            <dd class="sm:mt-2">
                <a href="{{ route('surat-keluar.index') }}" aria-label="Buka daftar surat keluar"
                   class="gov-stat text-[19px] underline-offset-4 hover:text-gov-700 hover:underline sm:text-[24px]">{{ number_format($ringkasan['keluar'], 0, ',', '.') }}</a>
            </dd>
        </div>
        <div class="flex items-baseline justify-between gap-3 px-5 py-3 sm:block sm:border-l sm:border-slate-100 sm:py-4">
            <dt class="gov-eyebrow">Tindak lanjut terbuka</dt>
            <dd class="sm:mt-2">
                <a href="{{ route('laporan.tindak-lanjut') }}" aria-label="Buka laporan tindak lanjut"
                   class="gov-stat text-[19px] underline-offset-4 hover:text-gov-700 hover:underline sm:text-[24px] {{ $ringkasan['terbuka'] > 0 ? 'text-gold-700' : '' }}">{{ number_format($ringkasan['terbuka'], 0, ',', '.') }}</a>
            </dd>
        </div>
    </dl>

    <form method="GET" class="mb-6 flex flex-wrap items-center gap-2">
        <label for="cari" class="sr-only">Cari surat</label>
        <div class="relative w-full min-w-0 sm:w-auto sm:flex-1 sm:max-w-md">
            <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                <x-icon name="search" class="h-4 w-4" />
            </span>
            <input id="cari" name="cari" class="gov-input pl-9" placeholder="Cari nomor agenda, nomor surat, atau perihal…">
        </div>
        <button type="submit" formaction="{{ route('surat-masuk.index') }}" class="gov-btn-outline">
            <x-icon name="mail-in" class="h-4 w-4" /> Surat masuk
        </button>
        <button type="submit" formaction="{{ route('surat-keluar.index') }}" class="gov-btn-outline">
            <x-icon name="mail-out" class="h-4 w-4" /> Surat keluar
        </button>
    </form>

    @if ($peran === 'admin')
        <div class="mb-8 grid gap-6 lg:grid-cols-12">
            <section class="min-w-0 lg:col-span-5 xl:col-span-4">
                <div class="border border-slate-200 bg-white">
                    <div class="border-b border-slate-100 px-5 py-3">
                        <h2 class="gov-section-title">Akun Pengguna</h2>
                    </div>
                    <dl class="divide-y divide-slate-100 text-[13.5px]">
                        <div class="flex items-baseline justify-between gap-3 px-5 py-2.5">
                            <dt class="text-slate-600">Total akun</dt>
                            <dd class="font-semibold tabular-nums text-gov-900">{{ $akun['total'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 px-5 py-2.5">
                            <dt class="text-slate-600">Aktif</dt>
                            <dd class="font-semibold tabular-nums text-gov-900">{{ $akun['aktif'] }}</dd>
                        </div>
                        <div class="flex items-baseline justify-between gap-3 px-5 py-2.5">
                            <dt class="text-slate-600">Masih memakai sandi sementara</dt>
                            <dd class="font-semibold tabular-nums {{ $akun['wajib_ganti'] > 0 ? 'text-gold-700' : 'text-gov-900' }}">{{ $akun['wajib_ganti'] }}</dd>
                        </div>
                    </dl>

                    <div class="border-t border-slate-100 px-5 py-3">
                        <p class="gov-eyebrow">Sebaran peran</p>
                        <dl class="mt-2 space-y-1.5 text-[13px]">
                            @foreach ($akun['peran'] as $nama => $jumlah)
                                <div class="flex items-baseline justify-between gap-3">
                                    <dt class="text-slate-600">{{ \Illuminate\Support\Str::headline($nama) }}</dt>
                                    <dd class="tabular-nums text-slate-800">{{ $jumlah }}</dd>
                                </div>
                            @endforeach
                        </dl>
                    </div>

                    <div class="border-t border-slate-100 px-5 py-3">
                        <a href="{{ route('pengguna.index') }}" class="inline-flex items-center gap-1.5 text-[12.5px] font-semibold text-gov-700 hover:underline">
                            Kelola pengguna <x-icon name="arrow-right" class="h-3.5 w-3.5" />
                        </a>
                    </div>
                </div>
            </section>

            <section class="min-w-0 lg:col-span-7 xl:col-span-8">
                <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-slate-200 pb-2">
                    <h2 class="gov-section-title">Log Terbaru</h2>
                    <a href="{{ route('log.index') }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-gov-700 hover:underline">
                        Semua log <x-icon name="arrow-right" class="h-3 w-3" />
                    </a>
                </div>
                <div class="mt-1 overflow-x-auto border border-slate-200 bg-white">
                    <table class="gov-table min-w-[520px]">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-[0.1em] text-slate-500">
                                <th class="w-[132px] py-2 pr-3 font-bold">Waktu</th>
                                <th class="py-2 pr-3 font-bold">Aktivitas</th>
                                <th class="w-[150px] py-2 font-bold">Pengguna</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($log as $a)
                                <tr>
                                    <td class="whitespace-nowrap py-2 pr-3 text-[11.5px] tabular-nums text-slate-500">{{ $a->waktu?->translatedFormat('d M Y H:i') }}</td>
                                    <td class="py-2 pr-3 text-[13px] text-slate-700">{{ $a->keterangan ?: $a->aksi }}</td>
                                    <td class="truncate py-2 text-[11.5px] text-slate-500">{{ $a->pengguna?->name ?? 'Sistem' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="py-6 text-[13px] text-slate-500">Belum ada aktivitas tercatat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </section>
        </div>
    @endif

    <div class="space-y-8">
        @foreach ($bagian as $b)
                <section>
                    <div class="flex flex-wrap items-start justify-between gap-x-4 gap-y-1 border-b border-slate-200 pb-2">
                        <div class="min-w-0">
                            <div class="flex flex-wrap items-baseline gap-2">
                                <h2 class="gov-section-title">{{ $b['judul'] }}</h2>
                                @if ($b['items']->isNotEmpty())
                                    <span class="rounded-full bg-gov-50 px-2 py-0.5 text-[11px] font-semibold tabular-nums text-gov-700 ring-1 ring-inset ring-gov-100">{{ $b['items']->count() }}</span>
                                @endif
                            </div>
                            <p class="mt-0.5 text-[12px] text-slate-500">{{ $b['catatan'] }}</p>
                        </div>
                        @if ($b['tautan'])
                            <a href="{{ $b['tautan']['url'] }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-gov-700 hover:underline">
                                {{ $b['tautan']['label'] }} <x-icon name="arrow-right" class="h-3 w-3" />
                            </a>
                        @endif
                    </div>

                    @if ($b['items']->isEmpty())
                        <p class="mt-2 border border-dashed border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-500">{{ $b['kosong'] }}</p>
                    @else
                        <div class="mt-1 overflow-x-auto border border-slate-200 bg-white">
                            <table class="gov-table min-w-full sm:min-w-[560px]">
                                <thead>
                                    <tr>
                                        <th class="gov-th whitespace-normal sm:whitespace-nowrap">Nomor / Perihal</th>
                                        <th class="gov-th w-[92px] whitespace-normal sm:whitespace-nowrap">Sifat</th>
                                        <th class="gov-th w-[150px] whitespace-normal sm:whitespace-nowrap">Waktu</th>
                                        <th class="gov-th hidden w-[132px] text-right sm:table-cell"><span class="sr-only">Aksi</span></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    @foreach ($b['items'] as $row)
                                        <tr class="align-top">
                                            <td class="gov-td px-4 py-3">
                                                <div class="flex gap-2.5">
                                                    <span class="mt-[7px] h-1.5 w-1.5 shrink-0 rounded-full {{ $row['keadaan'] === 'lewat' || in_array($row['sifat'], ['segera', 'penting', 'rahasia'], true) ? 'bg-gold-500' : 'bg-slate-300' }}"></span>
                                                    <div class="min-w-0">
                                                        <a href="{{ $row['url'] }}" class="font-medium text-gov-900 underline-offset-2 hover:text-gov-700 hover:underline">{{ $row['judul'] }}</a>
                                                        <p class="mt-0.5 font-mono text-[11px] text-slate-500">{{ $row['nomor'] }}</p>
                                                        <p class="text-[11.5px] leading-snug text-slate-500">{{ $row['meta'] }}</p>
                                                        <div class="mt-2 flex flex-wrap gap-1.5 sm:hidden">
                                                            @isset($row['aksi'])
                                                                <a href="{{ $row['aksi']['url'] }}" class="gov-btn !px-2.5 !py-1 !text-[11px]">{{ $row['aksi']['label'] }}</a>
                                                            @endisset
                                                            <a href="{{ $row['url'] }}" class="gov-btn-outline !px-2.5 !py-1 !text-[11px]">Buka</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-3 py-3">
                                                @if (in_array($row['sifat'], ['segera', 'penting', 'rahasia'], true))
                                                    <span class="gov-badge bg-gold-50 text-gold-700 ring-1 ring-inset ring-gold-200">{{ $row['sifat'] }}</span>
                                                @else
                                                    <span class="text-[10.5px] font-semibold uppercase tracking-[0.06em] text-slate-400">{{ $row['sifat'] }}</span>
                                                @endif
                                            </td>
                                            <td class="whitespace-normal px-3 py-3 text-[12px] tabular-nums sm:whitespace-nowrap {{ $row['keadaan'] === 'lewat' ? 'font-semibold text-red-600' : ($row['keadaan'] === 'dekat' ? 'font-semibold text-gold-700' : 'text-slate-500') }}">
                                                {{ $row['waktu'] }}
                                            </td>
                                            <td class="hidden px-3 py-3 text-right sm:table-cell">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    @isset($row['aksi'])
                                                        <a href="{{ $row['aksi']['url'] }}" class="gov-btn !px-2.5 !py-1 !text-[11px]">{{ $row['aksi']['label'] }}</a>
                                                    @endisset
                                                    <a href="{{ $row['url'] }}" class="gov-btn-outline !px-2.5 !py-1 !text-[11px]">Buka</a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </section>
            @endforeach
    </div>

    <section class="mt-10 border-t border-slate-200 pt-4">
        @php $adaTren = collect($tren['bulan'])->sum(fn ($b) => $b['masuk'] + $b['keluar']) > 0; @endphp

        <div class="flex flex-wrap items-baseline justify-between gap-2">
            <h2 class="gov-eyebrow">Registrasi enam bulan terakhir</h2>
            @if ($adaTren)
                <div class="flex items-center gap-4 text-[11px] text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gov-700"></span> Masuk</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gov-300"></span> Keluar</span>
                </div>
            @endif
        </div>

        @if ($adaTren)
            <div class="mt-4 grid grid-cols-6 gap-2 border-b border-slate-200 pb-2 sm:gap-6">
                @foreach ($tren['bulan'] as $b)
                    <div class="flex flex-col items-center gap-2">
                        <div class="flex h-24 w-full items-end justify-center gap-1.5">
                            <div class="w-2.5 rounded-sm bg-gov-700 transition-all"
                                 style="height: {{ max(2, round(($b['masuk'] / $tren['maks']) * 100)) }}%"
                                 title="{{ $b['label'] }}: {{ $b['masuk'] }} surat masuk"></div>
                            <div class="w-2.5 rounded-sm bg-gov-300 transition-all"
                                 style="height: {{ max(2, round(($b['keluar'] / $tren['maks']) * 100)) }}%"
                                 title="{{ $b['label'] }}: {{ $b['keluar'] }} surat keluar"></div>
                        </div>
                        <span class="text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-400">{{ $b['label'] }}</span>
                    </div>
                @endforeach
            </div>
        @else
            <p class="mt-2 border border-dashed border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-500">Belum ada registrasi surat pada enam bulan terakhir.</p>
        @endif
    </section>
@endsection
