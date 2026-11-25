@extends('layouts.sisurat')

@section('judul', auth()->user()->punyaRole('admin') ? 'Ringkasan Sistem' : 'Perlu Tindakan')
@section('subjudul', 'Ringkasan kerja ' . $instansi . ' · ' . now()->translatedFormat('l, d F Y'))

@section('aksi')
    @if (auth()->user()->bolehKelolaSurat())
        <a href="{{ route('surat-masuk.create') }}" class="gov-btn">
            <x-icon name="plus" class="h-4 w-4" /> Catat Surat Masuk
        </a>
        <a href="{{ route('surat-keluar.create') }}" class="gov-btn-outline">
            <x-icon name="edit" class="h-4 w-4" /> Buat Surat Keluar
        </a>
    @endif
@endsection

@section('isi')
    <dl class="gov-card gov-card-accent grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4">
        <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 px-5 py-3 sm:block sm:border-b sm:py-4 lg:border-b-0">
            <dt class="gov-eyebrow">Bulan ini:</dt>
            <dd class="text-[12px] leading-snug text-slate-500 sm:mt-1">{{ now()->translatedFormat('F Y') }}</dd>
        </div>
        <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 px-5 py-3 sm:block sm:border-b sm:border-l sm:border-slate-100 sm:py-4 lg:border-b-0">
            <dt class="gov-eyebrow">Surat masuk</dt>
            <dd class="sm:mt-2">
                <a href="{{ route('surat-masuk.index') }}" aria-label="Buka daftar surat masuk"
                   class="gov-stat text-[19px] underline-offset-4 hover:text-gov-700 hover:underline sm:text-[24px]">{{ number_format($ringkasan['masuk'], 0, ',', '.') }}</a>
            </dd>
        </div>
        <div class="flex items-baseline justify-between gap-3 border-b border-slate-100 px-5 py-3 sm:block sm:border-b sm:border-slate-100 sm:py-4 lg:border-b-0 lg:border-l">
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

    @php $adaTren = collect($tren['bulan'])->sum(fn ($b) => $b['masuk'] + $b['keluar']) > 0; @endphp

    <section class="gov-card mt-6 mb-6 overflow-hidden" x-data="{ pilih: null }">
        <div class="flex flex-wrap items-start justify-between gap-x-6 gap-y-2 border-b border-slate-100 px-5 py-3">
            <div class="min-w-0">
                <h2 class="gov-section-title">Registrasi enam bulan terakhir</h2>
                <p class="mt-0.5 text-[11.5px] tabular-nums text-slate-500" aria-live="polite">
                    <span x-show="!pilih" class="text-slate-400">Sorot batang untuk melihat angka</span>
                    <span x-show="pilih" x-cloak x-text="pilih ? pilih.label + ': ' + pilih.masuk + ' masuk, ' + pilih.keluar + ' keluar' : ''"></span>
                </p>
            </div>
            @if ($adaTren)
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 pt-1 text-[11px] text-slate-500">
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gov-700"></span> Masuk</span>
                    <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-sm bg-gov-300"></span> Keluar</span>
                </div>
            @endif
        </div>

        <div class="px-5 pb-5 pt-4">
            @if ($adaTren)
                <div class="grid grid-cols-6 gap-2 border-b border-slate-200 pb-2 sm:gap-6">
                    @foreach ($tren['bulan'] as $b)
                        <div class="flex flex-col items-center gap-2 rounded-md px-1 pt-1 transition-colors hover:bg-gov-50/60 focus-visible:bg-gov-50/60"
                             tabindex="0" role="group" aria-label="{{ $b['label'] }}: {{ $b['masuk'] }} masuk, {{ $b['keluar'] }} keluar"
                             x-data="{ label: @js($b['label']), masuk: {{ (int) $b['masuk'] }}, keluar: {{ (int) $b['keluar'] }} }"
                             @mouseenter="pilih = { label, masuk, keluar }"
                             @mouseleave="pilih = null"
                             @focus="pilih = { label, masuk, keluar }"
                             @blur="pilih = null">
                            <div class="flex h-24 w-full items-end justify-center gap-1.5">
                                <div class="anim-grow w-2.5 rounded-sm bg-gov-700 transition-all duration-200 group-hover:bg-gov-800"
                                     style="height: {{ max(2, round(($b['masuk'] / $tren['maks']) * 100)) }}%; animation-delay: {{ $loop->index * 45 }}ms"
                                     title="{{ $b['label'] }}: {{ $b['masuk'] }} surat masuk"></div>
                                <div class="anim-grow w-2.5 rounded-sm bg-gov-300 transition-all duration-200 group-hover:bg-gov-400"
                                     style="height: {{ max(2, round(($b['keluar'] / $tren['maks']) * 100)) }}%; animation-delay: {{ $loop->index * 45 + 60 }}ms"
                                     title="{{ $b['label'] }}: {{ $b['keluar'] }} surat keluar"></div>
                            </div>
                            <span class="text-[10px] font-semibold uppercase tracking-[0.08em] text-slate-400 transition-colors group-hover:text-slate-600">{{ $b['label'] }}</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="border border-dashed border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-500">Belum ada registrasi surat pada enam bulan terakhir.</p>
            @endif
        </div>
    </section>

    <div class="gov-card mb-8 px-5 py-4">
        <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-b border-slate-100 pb-3 text-[11.5px] text-slate-600">
            <span class="gov-eyebrow">Status disposisi</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-gov-500" aria-hidden="true"></span> Baru &amp; diproses</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-gold-400" aria-hidden="true"></span> Menunggu tindakan</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-leaf-500" aria-hidden="true"></span> Selesai</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-red-500" aria-hidden="true"></span> Perlu perhatian</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-400" aria-hidden="true"></span> Draf &amp; arsip</span>
        </div>

        <form method="GET" class="mt-3 flex flex-wrap items-center gap-2">
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
    </div>

    <div class="space-y-8">
        @foreach ($bagian as $b)
                <section x-data="{ buka: true }">
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
                        <div class="flex items-center gap-3">
                            @if ($b['tautan'])
                                <a href="{{ $b['tautan']['url'] }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-gov-700 hover:underline">
                                    {{ $b['tautan']['label'] }} <x-icon name="arrow-right" class="h-3 w-3" />
                                </a>
                            @endif
                            <button type="button" @click="buka = !buka"
                                    class="rounded p-2 text-slate-400 transition hover:bg-slate-100 hover:text-gov-700"
                                    aria-controls="bagian-{{ $loop->index }}" x-bind:aria-expanded="buka ? 'true' : 'false'">
                                <span class="sr-only">Buka atau tutup {{ $b['judul'] }}</span>
                                <x-icon name="chevron-down" class="h-4 w-4 transition-transform duration-200" x-bind:class="buka ? '' : '-rotate-90'" />
                            </button>
                        </div>
                    </div>

                    <div id="bagian-{{ $loop->index }}" x-show="buka"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 -translate-y-1"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 -translate-y-1">
                    @if ($b['items']->isEmpty())
                        <p class="mt-2 border border-dashed border-slate-200 bg-white px-4 py-4 text-[13px] text-slate-500">{{ $b['kosong'] }}</p>
                    @else
                        <div class="gov-card mt-1 overflow-x-auto">
                            <table class="gov-table-w560">
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
                                                @if ($row['sifat'] === 'biasa')
                                                    <span class="text-[10.5px] font-semibold uppercase tracking-[0.06em] text-slate-400">{{ $row['sifat'] }}</span>
                                                @else
                                                    <x-badge :value="$row['sifat']" type="sifat" />
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
                    </div>
                </section>
            @endforeach
    </div>

    @if ($peran === 'admin')
        <div class="mt-10 grid gap-6 border-t border-slate-200 pt-6 xl:grid-cols-12">
            <section class="min-w-0 xl:col-span-4">
                <div class="gov-card overflow-hidden">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-gov-50 text-gov-700 ring-1 ring-inset ring-gov-100">
                            <x-icon name="users" class="h-4 w-4" />
                        </span>
                        <h2 class="gov-section-title">Akun Pengguna</h2>
                    </div>
                    <dl class="divide-y divide-slate-100 text-[13.5px]">
                        <div class="flex items-center justify-between gap-3 px-5 py-2.5">
                            <dt class="flex items-center gap-2 text-slate-600">
                                <x-icon name="users" class="h-4 w-4 shrink-0 text-slate-400" /> Total akun
                            </dt>
                            <dd class="font-semibold tabular-nums text-gov-900">{{ $akun['total'] }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-2.5">
                            <dt class="flex items-center gap-2 text-slate-600">
                                <x-icon name="user" class="h-4 w-4 shrink-0 text-slate-400" /> Aktif
                            </dt>
                            <dd class="flex items-center gap-1.5">
                                <x-icon name="check" class="h-3.5 w-3.5 text-leaf-600" />
                                <span class="font-semibold tabular-nums text-gov-900">{{ $akun['aktif'] }}</span>
                            </dd>
                        </div>
                        <div class="flex items-center justify-between gap-3 px-5 py-2.5">
                            <dt class="flex items-center gap-2 text-slate-600">
                                <x-icon name="key" class="h-4 w-4 shrink-0 text-slate-400" /> Masih memakai sandi sementara
                            </dt>
                            <dd class="flex items-center gap-1.5">
                                @if ($akun['wajib_ganti'] > 0)
                                    <x-icon name="alert" class="h-3.5 w-3.5 text-gold-600" />
                                    <span class="font-semibold tabular-nums text-gold-700">{{ $akun['wajib_ganti'] }}</span>
                                @else
                                    <x-icon name="check" class="h-3.5 w-3.5 text-leaf-600" />
                                    <span class="font-semibold tabular-nums text-gov-900">0</span>
                                @endif
                            </dd>
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

                <div class="gov-card mt-4 overflow-hidden">
                    <div class="flex items-center gap-2.5 border-b border-slate-100 px-5 py-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded bg-gov-50 text-gov-700 ring-1 ring-inset ring-gov-100">
                            <x-icon name="dashboard" class="h-4 w-4" />
                        </span>
                        <h2 class="gov-section-title">Pintasan</h2>
                    </div>
                    <div class="divide-y divide-slate-100 text-[12.5px]">
                        @foreach ([
                            ['route' => 'pengguna.index', 'label' => 'Pengguna', 'icon' => 'users'],
                            ['route' => 'unit-kerja.index', 'label' => 'Unit Kerja', 'icon' => 'unit'],
                            ['route' => 'klasifikasi.index', 'label' => 'Klasifikasi', 'icon' => 'tag'],
                            ['route' => 'instansi.index', 'label' => 'Instansi', 'icon' => 'building'],
                            ['route' => 'log.index', 'label' => 'Log Aktivitas', 'icon' => 'log'],
                            ['route' => 'pengaturan.edit', 'label' => 'Pengaturan Instansi', 'icon' => 'building'],
                        ] as $pintasan)
                            <a href="{{ route($pintasan['route']) }}"
                               class="flex items-center gap-2.5 px-5 py-2.5 text-slate-600 transition hover:bg-gov-50/60 hover:text-gov-700">
                                <x-icon :name="$pintasan['icon']" class="h-4 w-4 shrink-0 text-slate-400" />
                                <span class="min-w-0 flex-1">{{ $pintasan['label'] }}</span>
                                <x-icon name="arrow-right" class="h-3 w-3 shrink-0 text-slate-300" />
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="relative min-w-0 xl:col-span-8">
                {{-- Tinggi kartu dipatok ke kolom Akun + Pintasan hanya saat dua kolom berdampingan (xl). --}}
                <div class="gov-card relative flex flex-col overflow-hidden xl:absolute xl:inset-0">
                    <div class="flex flex-wrap items-center justify-between gap-2 border-b border-slate-100 px-5 py-3">
                        <h2 class="gov-section-title">Log Terbaru</h2>
                        <a href="{{ route('log.index') }}" class="inline-flex items-center gap-1 text-[11.5px] font-semibold text-gov-700 hover:underline">
                            Semua log <x-icon name="arrow-right" class="h-3 w-3" />
                        </a>
                    </div>
                    <div class="min-h-0 flex-1 overflow-y-auto">
                    {{-- table-fixed: kolom Aktivitas dipatok lebarnya agar teks panjang terpotong dengan elipsis, bukan melebarkan tabel. --}}
                    <table class="gov-table table-fixed">
                        <thead>
                            <tr class="text-[10px] uppercase tracking-[0.1em] text-slate-500">
                                <th class="gov-th w-[112px] font-bold sm:w-[124px]">Waktu</th>
                                <th class="gov-th font-bold">Aktivitas</th>
                                <th class="gov-th hidden w-[132px] font-bold xl:table-cell">Pengguna</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse ($log as $a)
                                <tr>
                                    <td class="gov-td whitespace-nowrap text-[11.5px] tabular-nums text-slate-500">{{ $a->waktu?->translatedFormat('d M Y H:i') }}</td>
                                    <td class="gov-td text-[13px] leading-snug text-slate-700">
                                        <div class="flex items-start gap-2.5">
                                            <span class="mt-px flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-slate-50 text-slate-400 ring-1 ring-inset ring-slate-200/70">
                                                <x-icon name="log" class="h-3.5 w-3.5" />
                                            </span>
                                            <span class="min-w-0">
                                                <span class="xl:block xl:truncate" title="{{ $a->keterangan ?: $a->aksi }}">{{ $a->keterangan ?: $a->aksi }}</span>
                                                <span class="mt-0.5 block text-[11px] text-slate-400 xl:hidden">{{ $a->pengguna?->name ?? 'Sistem' }}</span>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="gov-td hidden truncate text-[11.5px] text-slate-500 xl:table-cell">{{ $a->pengguna?->name ?? 'Sistem' }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="3" class="px-4 py-8 text-center text-[13px] text-slate-500">Belum ada aktivitas tercatat.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </section>
        </div>
    @endif
@endsection
