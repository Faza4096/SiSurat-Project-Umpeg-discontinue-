@extends('layouts.sisurat')

@section('judul', 'Detail Surat Masuk')
@section('subjudul', 'Agenda ' . $s->no_agenda . ' · ' . $s->no_surat)

@section('aksi')
    <a href="{{ route('surat-masuk.index') }}" class="gov-btn-outline">Kembali</a>
    @can('create', App\Models\SuratKeluar::class)
        <a href="{{ route('surat-masuk.balas', $s) }}" class="gov-btn-outline"><x-icon name="mail-out" class="h-4 w-4" /> Buat Balasan</a>
    @endcan
    @can('update', $s)
        <a href="{{ route('surat-masuk.edit', $s) }}" class="gov-btn-outline"><x-icon name="edit" class="h-4 w-4" /> Ubah</a>
    @endcan
    @if (auth()->user()->can('delete', $s))
        <form method="POST" action="{{ route('surat-masuk.destroy', $s) }}"
              onsubmit="return confirm('Pindahkan surat {{ $s->no_agenda }} ke arsip terhapus?')">
            @csrf @method('DELETE')
            <button class="gov-btn-danger"><x-icon name="trash" class="h-4 w-4" /> Hapus</button>
        </form>
    @endif
@endsection

@section('isi')
    @php
        $langkah = ['diterima', 'didisposisi', 'diproses', 'selesai', 'diarsipkan'];
        $posisi = array_search($s->status, $langkah, true);
        $posisi = $posisi === false ? 0 : $posisi;
    @endphp

    <div class="gov-card mb-4 overflow-hidden">
        <div class="border-b border-slate-100 bg-gradient-to-r from-gov-900 to-gov-700 px-6 py-6 text-white">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div class="min-w-0">
                    <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gold-300">Surat Masuk · {{ $s->no_agenda }}</p>
                    <h2 class="mt-1 font-serif text-xl font-bold leading-snug sm:text-2xl">{{ $s->perihal }}</h2>
                    <p class="mt-2 text-sm text-gov-100/85">{{ $s->instansi?->nama ?? '-' }} · diterima {{ $s->tgl_diterima?->translatedFormat('d F Y') }}</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    <x-badge :value="$s->status" class="!bg-white/15 !text-white !ring-white/20" />
                    <x-badge :value="$s->sifat" type="sifat" class="!bg-gold-400 !text-gov-900 !ring-gold-300" />
                </div>
            </div>
        </div>

        <div class="grid gap-2 border-b border-slate-100 px-6 py-4 sm:grid-cols-5">
            @foreach ($langkah as $i => $tahap)
                @php $aktif = $i <= $posisi; @endphp
                <div class="flex items-center gap-2">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $aktif ? 'bg-gov-700 text-white' : 'bg-slate-100 text-slate-400' }}">
                        @if ($i < $posisi)
                            <x-icon name="check" class="h-3.5 w-3.5" />
                        @else
                            {{ $i + 1 }}
                        @endif
                    </span>
                    <span class="text-xs font-semibold {{ $aktif ? 'text-gov-800' : 'text-slate-400' }}">{{ \Illuminate\Support\Str::headline($tahap) }}</span>
                </div>
            @endforeach
        </div>

        @if ($s->isRahasia())
            <div class="flex items-start gap-3 border-b border-red-100 bg-red-50 px-6 py-3 text-xs text-red-800">
                <x-icon name="eye" class="mt-0.5 h-4 w-4 shrink-0" />
                <p>Surat bersifat <strong>rahasia</strong>: hanya staf dan administrator yang dapat membuka berkas ini.</p>
            </div>
        @endif

        <dl class="grid gap-x-8 gap-y-4 p-6 sm:grid-cols-2">
            <div>
                <dt class="gov-label">Nomor Agenda</dt>
                <dd class="font-mono text-sm font-semibold text-gov-800">{{ $s->no_agenda }}</dd>
            </div>
            <div>
                <dt class="gov-label">Nomor Surat</dt>
                <dd class="font-mono text-sm text-slate-700">{{ $s->no_surat }}</dd>
            </div>
            <div>
                <dt class="gov-label">Tanggal Surat</dt>
                <dd class="text-sm text-slate-700">{{ $s->tgl_surat?->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="gov-label">Tanggal Diterima</dt>
                <dd class="text-sm text-slate-700">{{ $s->tgl_diterima?->translatedFormat('d F Y') }}</dd>
            </div>
            <div>
                <dt class="gov-label">Klasifikasi</dt>
                <dd class="text-sm text-slate-700">
                    {{ $s->klasifikasi ? $s->klasifikasi->kode . ' — ' . $s->klasifikasi->uraian : 'Tanpa klasifikasi' }}
                </dd>
            </div>
            <div>
                <dt class="gov-label">Dicatat Oleh</dt>
                <dd class="text-sm text-slate-700">{{ $s->pencatat?->name ?? '-' }}</dd>
            </div>
            @if ($s->status === 'diarsipkan')
                <div>
                    <dt class="gov-label">Lokasi Arsip</dt>
                    <dd class="text-sm text-slate-700">{{ $s->lokasi_arsip ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="gov-label">Diarsipkan</dt>
                    <dd class="text-sm text-slate-700">
                        {{ $s->diarsipkan_pada?->translatedFormat('d F Y H:i') ?? '-' }}
                        @if ($s->pengarsip) · {{ $s->pengarsip->name }} @endif
                    </dd>
                </div>
            @endif
        </dl>
    </div>

    <div class="gov-card mb-4">
        <div class="gov-card-head">
            <p class="gov-card-title">Lampiran &amp; Scan</p>
            <span class="text-xs text-slate-500">{{ $s->lampiran->count() }} berkas</span>
        </div>
        <div class="space-y-3 p-5">
            @forelse ($s->lampiran as $l)
                <div class="flex flex-wrap items-center gap-2 rounded-lg border border-slate-200 px-3 py-2">
                    <x-icon name="document" class="h-4 w-4 shrink-0 text-slate-400" />
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xs font-semibold text-slate-700">{{ $l->nama_file }}</p>
                        <p class="text-[11px] text-slate-400">
                            {{ App\Models\Lampiran::JENIS[$l->jenis] ?? 'Lampiran' }}
                            @if ($l->pengunggah) · {{ $l->pengunggah->name }} @endif
                            · {{ $l->created_at?->translatedFormat('d M Y') }}
                        </p>
                    </div>
                    <a href="{{ route('lampiran.unduh', $l) }}" class="gov-btn-outline !px-2.5 !py-1.5 text-xs">
                        <x-icon name="download" class="h-4 w-4" /> Unduh
                    </a>
                    @if (auth()->user()->can('hapus', $l))
                        <form method="POST" action="{{ route('lampiran.hapus', $l) }}" onsubmit="return confirm('Hapus lampiran ini?')">
                            @csrf @method('DELETE')
                            <button class="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Hapus lampiran">
                                <x-icon name="trash" class="h-4 w-4" />
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <p class="text-sm text-slate-400">Belum ada lampiran diunggah.</p>
            @endforelse

            @can('kelolaLampiran', $s)
                <form method="POST" action="{{ route('surat-masuk.lampiran.store', $s) }}" enctype="multipart/form-data"
                      class="grid gap-2 border-t border-slate-100 pt-4 sm:grid-cols-[1fr_10rem_auto]">
                    @csrf
                    <input type="file" name="berkas" accept=".pdf,.jpg,.jpeg,.png" required class="gov-input !p-2 text-xs">
                    <select name="jenis" class="gov-input text-xs">
                        @foreach (App\Models\Lampiran::JENIS as $nilai => $label)
                            <option value="{{ $nilai }}">{{ $label }}</option>
                        @endforeach
                    </select>
                    <button class="gov-btn text-xs"><x-icon name="plus" class="h-4 w-4" /> Unggah</button>
                    <p class="gov-help sm:col-span-3">PDF/JPG/PNG maksimal 10 MB.</p>
                </form>
            @endcan
        </div>
    </div>

    <div class="mb-4 grid gap-4 lg:grid-cols-2">
        <div class="gov-card p-5">
            <p class="gov-card-title">Arsip Fisik</p>
            @if ($s->status === 'diarsipkan')
                <p class="mt-2 text-sm text-slate-600">Disimpan di: <span class="font-semibold">{{ $s->lokasi_arsip }}</span></p>
                <p class="mt-1 text-xs text-slate-400">{{ $s->diarsipkan_pada?->translatedFormat('d F Y H:i') }}</p>
            @elseif (auth()->user()->can('arsipkan', $s))
                <p class="mt-1 text-xs text-slate-500">Tindak lanjut selesai. Catat lokasi penyimpanan berkas fisik.</p>
                <form method="POST" action="{{ route('surat-masuk.arsipkan', $s) }}" class="mt-3 flex flex-wrap gap-2">
                    @csrf @method('PATCH')
                    <input name="lokasi_arsip" required placeholder="Contoh: Lemari B-2, Rak 3" class="gov-input flex-1 text-sm">
                    <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Arsipkan</button>
                </form>
            @else
                <p class="mt-2 text-xs leading-relaxed text-slate-500">
                    Surat dapat diarsipkan setelah semua tindak lanjut selesai.
                </p>
            @endif
        </div>

        <div class="gov-card p-5">
            <p class="gov-card-title">Surat Balasan</p>
            @forelse ($s->balasan as $b)
                <a href="{{ route('surat-keluar.show', $b) }}" class="mt-2 flex items-center justify-between gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs hover:border-gov-300">
                    <span class="min-w-0">
                        <span class="block truncate font-semibold text-slate-700">{{ $b->hal }}</span>
                        <span class="font-mono text-[11px] text-slate-400">{{ $b->no_surat }}</span>
                    </span>
                    <x-badge :value="$b->status" />
                </a>
            @empty
                <p class="mt-2 text-xs text-slate-500">Belum ada surat balasan.</p>
                @can('create', App\Models\SuratKeluar::class)
                    <a href="{{ route('surat-masuk.balas', $s) }}" class="gov-btn-outline mt-3 !px-3 !py-1.5 text-xs">
                        <x-icon name="plus" class="h-4 w-4" /> Buat Balasan
                    </a>
                @endcan
            @endforelse
        </div>
    </div>

    @if (auth()->user()->punyaRole('staf', 'admin'))
        <div class="gov-card mb-4 flex flex-wrap items-center justify-between gap-3 p-5">
            <div class="min-w-0">
                <p class="gov-card-title">ACC &amp; Disposisi</p>
                <p class="mt-1 max-w-xl text-xs leading-relaxed text-slate-500">
                    Isian terisi otomatis dari data surat. Setelah disimpan, status menjadi <em>didisposisi</em>
                    dan kartu PDF dikirim ke unit tujuan.
                </p>
            </div>
            @can('acc', $s)
                <a href="{{ route('disposisi.acc.form', $s) }}" class="gov-btn shrink-0">
                    <x-icon name="check" class="h-4 w-4" /> ACC &amp; Disposisi
                </a>
            @endcan
        </div>
    @endif

    <div class="space-y-4">
        @forelse ($s->disposisi as $d)
            <div class="gov-card">
                <div class="gov-card-head">
                    <div>
                        <p class="gov-card-title">Disposisi {{ $d->tgl_disposisi?->translatedFormat('d F Y') }}</p>
                        <p class="text-xs text-slate-500">Oleh {{ $d->pembuat?->name ?? '-' }}{{ $d->indeks ? ' · Indeks ' . $d->indeks : '' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
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
                                @if ($d->kartu_dibuat_pada)
                                    <span class="sr-only">dibuat {{ $d->kartu_dibuat_pada->diffForHumans() }}</span>
                                @endif
                            </a>
                        @endcan
                        @can('delete', $d)
                            <form method="POST" action="{{ route('disposisi.destroy', $d) }}" onsubmit="return confirm('Batalkan disposisi ini?')">
                                @csrf @method('DELETE')
                                <button class="rounded-md p-1.5 text-slate-400 hover:bg-red-50 hover:text-red-600" title="Batalkan disposisi">
                                    <x-icon name="trash" class="h-4 w-4" />
                                </button>
                            </form>
                        @endcan
                    </div>
                </div>

                <div class="border-b border-slate-100 px-5 py-4">
                    <p class="gov-label">Instruksi</p>
                    <p class="whitespace-pre-line text-sm text-slate-700">{{ $d->instruksi }}</p>
                </div>

                <div class="divide-y divide-slate-100">
                    @foreach ($d->tujuan as $t)
                        @php $bolehUbah = auth()->user()->can('ubah', $t); @endphp
                        <div class="px-5 py-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-slate-700">{{ $t->unit?->nama ?? '-' }}</p>
                                    <p class="mt-0.5 text-[11px] text-slate-400">
                                        @if ($t->tgl_selesai)
                                            Selesai {{ $t->tgl_selesai->translatedFormat('d M Y H:i') }}{{ $t->penyelesai ? ' oleh ' . $t->penyelesai->name : '' }}
                                        @else
                                            Belum dinyatakan selesai
                                        @endif
                                    </p>
                                </div>
                                <x-badge :value="$t->status_tindak_lanjut" />
                            </div>

                            @if ($t->catatan)
                                <p class="mt-2 rounded-lg bg-slate-50 px-3 py-2 text-xs text-slate-600">{{ $t->catatan }}</p>
                            @endif

                            @if ($t->bukti_path)
                                <div class="mt-2 flex flex-wrap items-center gap-2 text-xs">
                                    <a href="{{ route('tujuan.bukti.unduh', $t) }}" target="_blank"
                                       class="inline-flex items-center gap-1.5 font-semibold text-gov-700 hover:underline">
                                        <x-icon name="document" class="h-4 w-4" /> {{ $t->bukti_nama }}
                                    </a>
                                    <span class="text-slate-400">· diunggah {{ $t->diunggah_pada?->translatedFormat('d M Y') }}</span>
                                    @if ($bolehUbah)
                                        <form method="POST" action="{{ route('tujuan.bukti.hapus', $t) }}" onsubmit="return confirm('Hapus bukti tindak lanjut?')">
                                            @csrf @method('DELETE')
                                            <button class="text-red-600 hover:underline">hapus</button>
                                        </form>
                                    @endif
                                </div>
                            @endif

                            @if ($bolehUbah)
                                <form method="POST" action="{{ route('tujuan.status', $t) }}" class="mt-3 grid gap-2 sm:grid-cols-[10rem_1fr_auto]">
                                    @csrf @method('PATCH')
                                    <select name="status" class="gov-input !py-1.5 text-xs">
                                        @foreach (App\Models\DisposisiTujuan::STATUS as $nilai => $label)
                                            <option value="{{ $nilai }}" @selected($t->status_tindak_lanjut === $nilai)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input name="catatan" value="{{ $t->catatan }}" placeholder="Catatan tindak lanjut (opsional)" class="gov-input !py-1.5 text-xs">
                                    <button class="gov-btn-outline !px-2.5 !py-1.5 text-xs">Simpan</button>
                                </form>

                                <form method="POST" action="{{ route('tujuan.bukti', $t) }}" enctype="multipart/form-data"
                                      class="mt-2 flex flex-wrap items-center gap-2">
                                    @csrf
                                    <input type="file" name="bukti" accept=".pdf,.jpg,.jpeg,.png" required class="gov-input !w-auto !py-1.5 text-xs">
                                    <button class="gov-btn-outline !px-2.5 !py-1.5 text-xs">
                                        <x-icon name="download" class="h-4 w-4 rotate-180" /> Unggah bukti
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="gov-card px-6 py-10 text-center">
                <p class="text-sm font-semibold text-slate-600">Belum ada disposisi</p>
                <p class="mt-1 text-xs text-slate-500">
                    @if (auth()->user()->punyaRole('staf', 'admin'))
                        Isi formulir di atas untuk meneruskan surat ke unit kerja.
                    @else
                        Surat ini belum didisposisikan.
                    @endif
                </p>
            </div>
        @endforelse
    </div>

    @can('hapusPermanen', $s)
        <div class="mt-6 flex justify-end">
            <a href="{{ route('surat-masuk.hapus-permanen.form', $s) }}" class="gov-btn-danger">
                <x-icon name="trash" class="h-4 w-4" /> Hapus Permanen Surat Masuk
            </a>
        </div>
    @endcan
@endsection
