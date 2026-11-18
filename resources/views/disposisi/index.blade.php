@extends('layouts.sisurat')

@section('judul', 'Disposisi')
@section('subjudul', 'Daftar disposisi beserta status tindak lanjut unit kerja.')

@section('aksi')
    <a href="{{ route('laporan.tindak-lanjut') }}" class="gov-btn-outline">
        <x-icon name="chart" class="h-4 w-4" /> Laporan Tindak Lanjut
    </a>
@endsection

@section('isi')
    @if ($menunggu['masuk']->isNotEmpty() || $menunggu['keluar']->isNotEmpty())
        <section class="gov-card mb-4">
            <div class="gov-card-head">
                <div class="min-w-0">
                    <p class="gov-eyebrow">Menunggu disposisi</p>
                    <p class="mt-0.5 text-xs text-slate-500">Surat yang baru dicatat langsung masuk antrean ini.</p>
                </div>
                <span class="shrink-0 rounded-full bg-gold-50 px-2.5 py-1 text-[11px] font-semibold text-gold-700 ring-1 ring-inset ring-gold-200">
                    {{ $menunggu['totalMasuk'] + $menunggu['totalKeluar'] }} surat
                </span>
            </div>
            <div class="divide-y divide-slate-100">
                @foreach ($menunggu['masuk'] as $m)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800">{{ $m->perihal }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Surat masuk · {{ $m->no_agenda }} · {{ $m->instansi?->nama }}
                                · diterima {{ $m->tgl_diterima?->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <a href="{{ route('surat-masuk.show', $m) }}" class="gov-btn-outline !px-3 !py-1.5 text-xs">Buka</a>
                            @can('acc', $m)
                                <a href="{{ route('disposisi.acc.form', $m) }}" class="gov-btn !px-3 !py-1.5 text-xs">Disposisi</a>
                            @endcan
                        </div>
                    </div>
                @endforeach
                @foreach ($menunggu['keluar'] as $k)
                    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3">
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-slate-800">{{ $k->hal }}</p>
                            <p class="mt-0.5 text-xs text-slate-500">
                                Surat keluar · {{ $k->no_surat }} · {{ $k->unit?->nama }}
                                · {{ $k->tgl_surat?->translatedFormat('d M Y') }}
                            </p>
                        </div>
                        <div class="flex shrink-0 items-center gap-2">
                            <x-badge :value="$k->status" />
                            @if (in_array($k->status, ['draf', 'revisi'], true))
                                @can('ajukan', $k)
                                    <form method="POST" action="{{ route('surat-keluar.ajukan', $k) }}">
                                        @csrf
                                        <button class="gov-btn !px-3 !py-1.5 text-xs">Ajukan</button>
                                    </form>
                                @endcan
                            @endif
                            <a href="{{ route('surat-keluar.show', $k) }}" class="gov-btn-outline !px-3 !py-1.5 text-xs">Tinjau</a>
                        </div>
                    </div>
                @endforeach
            </div>

            @if ($menunggu['totalMasuk'] > $menunggu['masuk']->count() || $menunggu['totalKeluar'] > $menunggu['keluar']->count())
                <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t border-slate-100 px-5 py-3">
                    @if ($menunggu['totalMasuk'] > $menunggu['masuk']->count())
                        <a href="{{ route('surat-masuk.index', ['status' => 'diterima']) }}" class="text-xs font-semibold text-gov-700 hover:underline">
                            Lihat semua {{ $menunggu['totalMasuk'] }} surat masuk menunggu disposisi
                        </a>
                    @endif
                    @if ($menunggu['totalKeluar'] > $menunggu['keluar']->count())
                        <a href="{{ route('surat-keluar.index', ['status' => implode(',', App\Models\SuratKeluar::BELUM_DISETUJUI)]) }}" class="text-xs font-semibold text-gov-700 hover:underline">
                            Lihat semua {{ $menunggu['totalKeluar'] }} surat keluar menunggu persetujuan
                        </a>
                    @endif
                </div>
            @endif
        </section>
    @endif

    <form method="GET" class="gov-card mb-4 p-4">
        <div class="grid grid-cols-2 gap-3 md:grid-cols-[1fr_14rem_12rem_auto]">
            <div class="relative col-span-2 md:col-span-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input name="cari" value="{{ request('cari') }}" placeholder="Cari instruksi, agenda, atau perihal…" class="gov-input pl-9">
            </div>
            <select name="unit" class="gov-input">
                <option value="">Semua unit tujuan</option>
                @foreach ($units as $u)
                    <option value="{{ $u->id }}" @selected(request('unit') == $u->id)>{{ $u->nama }}</option>
                @endforeach
            </select>
            <select name="status" class="gov-input">
                <option value="">Semua tindak lanjut</option>
                @foreach (App\Models\DisposisiTujuan::STATUS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            <div class="col-span-2 flex gap-2 md:col-span-1">
                <button class="gov-btn w-full md:w-auto"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
                @if (request()->hasAny(['cari', 'unit', 'status']))
                    <a href="{{ route('disposisi.index') }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <form id="cetak-massal" method="GET" action="{{ route('disposisi.cetak') }}"
          class="mb-3 flex flex-wrap items-center justify-between gap-2 border border-slate-200 bg-white px-4 py-2.5">
        <label class="flex items-center gap-2 text-xs text-slate-600">
            <input type="checkbox" class="rounded border-slate-300 text-gov-700 focus:ring-gov-500"
                   onclick="document.querySelectorAll('.pilih-disposisi').forEach(c => c.checked = this.checked)">
            Pilih semua di halaman ini
        </label>
        <button class="gov-btn-outline !px-3 !py-1.5 text-xs">
            <x-icon name="download" class="h-4 w-4" /> Unduh PDF terpilih
        </button>
    </form>

    <div class="space-y-4">
        @forelse ($items as $d)
            <div class="gov-card">
                <div class="gov-card-head">
                    <div class="flex min-w-0 items-start gap-3">
                        <input type="checkbox" form="cetak-massal" name="ids[]" value="{{ $d->id }}"
                               class="pilih-disposisi mt-1 rounded border-slate-300 text-gov-700 focus:ring-gov-500"
                               aria-label="Pilih disposisi {{ $d->nomorSurat() }}">
                        <div class="min-w-0">
                            <a href="{{ $d->tautanSurat() }}" class="font-semibold text-slate-800 hover:text-gov-700">
                                {{ $d->perihal() }}
                            </a>
                            <p class="mt-0.5 text-xs text-slate-500">
                                {{ $d->suratMasuk ? 'Masuk · ' . $d->suratMasuk->no_agenda : 'Keluar · ' . $d->nomorSuratAsli() }}
                                · {{ $d->asal() }}
                            </p>
                        </div>
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        @if ($d->tgl_penyelesaian)
                            <span class="rounded-full px-2.5 py-1 text-[11px] font-semibold ring-1 ring-inset {{ $d->sudahLewatBatas() ? 'bg-red-50 text-red-700 ring-red-200' : 'bg-gold-50 text-gold-700 ring-gold-200' }}">
                                Batas {{ $d->tgl_penyelesaian->translatedFormat('d M Y') }}
                            </span>
                        @endif
                        <a href="{{ route('disposisi.kartu', $d) }}" target="_blank" class="gov-btn-outline !px-3 !py-1.5 text-xs">
                            <x-icon name="print" class="h-4 w-4" /> Kartu
                        </a>
                        @can('unduhKartu', $d)
                            <a href="{{ route('disposisi.pdf', $d) }}" class="gov-btn-outline !px-3 !py-1.5 text-xs">
                                <x-icon name="download" class="h-4 w-4" /> PDF
                            </a>
                        @endcan
                    </div>
                </div>

                <div class="grid gap-4 px-5 py-4 md:grid-cols-2">
                    <div>
                        <p class="gov-label">Instruksi</p>
                        <p class="text-sm leading-relaxed text-slate-700">{{ $d->instruksi }}</p>
                        <p class="mt-2 text-[11px] text-slate-400">
                            Didisposisikan {{ $d->tgl_disposisi?->translatedFormat('d M Y H:i') }} oleh {{ $d->pembuat?->name ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="gov-label">Tindak Lanjut Unit</p>
                        <div class="space-y-2">
                            @foreach ($d->tujuan as $t)
                                <div class="rounded-lg border border-slate-200 px-3 py-2">
                                    <div class="flex items-center justify-between gap-2">
                                        <span class="text-xs font-semibold text-slate-700">{{ $t->unit?->nama }}</span>
                                        <x-badge :value="$t->status_tindak_lanjut" />
                                    </div>
                                    @can('ubah', $t)
                                        <form method="POST" action="{{ route('tujuan.status', $t) }}" class="mt-2 flex items-center gap-1.5">
                                            @csrf
                                            @method('PATCH')
                                            <label class="sr-only" for="status-{{ $t->id }}">Status tindak lanjut {{ $t->unit?->nama }}</label>
                                            <select id="status-{{ $t->id }}" name="status" class="gov-input !py-1 !text-[11px]">
                                                @foreach (App\Models\DisposisiTujuan::STATUS as $nilai => $label)
                                                    <option value="{{ $nilai }}" @selected($t->status_tindak_lanjut === $nilai)>{{ $label }}</option>
                                                @endforeach
                                            </select>
                                            <button class="gov-btn-outline !px-2.5 !py-1 text-[11px]">Simpan</button>
                                        </form>
                                    @endcan
                                    @if ($t->tgl_selesai)
                                        <p class="mt-1 text-[11px] text-slate-400">
                                            Selesai {{ $t->tgl_selesai->translatedFormat('d M Y') }}{{ $t->penyelesai ? ' · ' . $t->penyelesai->name : '' }}
                                        </p>
                                    @endif
                                    @if ($t->catatan)
                                        <p class="mt-1 text-[11px] text-slate-500">{{ $t->catatan }}</p>
                                    @endif
                                    @if ($t->bukti_path)
                                        <a href="{{ route('tujuan.bukti.unduh', $t) }}" target="_blank"
                                           class="mt-1 inline-flex items-center gap-1 text-[11px] font-semibold text-gov-700 hover:underline">
                                            <x-icon name="document" class="h-3.5 w-3.5" /> {{ $t->bukti_nama }}
                                        </a>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="gov-card px-6 py-14 text-center">
                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                    <x-icon name="forward" class="h-6 w-6" />
                </span>
                <p class="mt-3 text-sm font-semibold text-slate-600">Belum ada disposisi</p>
                <p class="mt-1 text-xs text-slate-500">Disposisi dibuat dari antrean di atas atau halaman detail surat masuk.</p>
            </div>
        @endforelse
    </div>

    @if ($items->hasPages())
        <div class="mt-4">{{ $items->links() }}</div>
    @endif
@endsection
