@extends('layouts.sisurat')

@section('judul', 'ACC & Disposisi')
@section('subjudul', 'Agenda ' . $s->no_agenda . ' · ' . $s->no_surat)

@section('aksi')
    <a href="{{ route('surat-masuk.show', $s) }}" class="gov-btn-outline">Kembali</a>
@endsection

@section('isi')
    <form method="POST" action="{{ route('disposisi.acc', $s) }}" class="grid gap-4 lg:grid-cols-5">
        @csrf

        <div class="lg:col-span-2">
            <div class="gov-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-600">Identitas Surat</p>
                </div>
                <dl class="divide-y divide-slate-100 text-sm">
                    <div class="px-5 py-3">
                        <dt class="gov-label">Dari</dt>
                        <dd class="mt-0.5 text-slate-800">{{ $s->instansi?->nama ?? '-' }}</dd>
                    </div>
                    <div class="px-5 py-3">
                        <dt class="gov-label">Perihal</dt>
                        <dd class="mt-0.5 text-slate-800">{{ $s->perihal }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2 px-5 py-3">
                        <div>
                            <dt class="gov-label">Tgl. Surat</dt>
                            <dd class="mt-0.5 text-slate-800">{{ $s->tgl_surat?->translatedFormat('d F Y') }}</dd>
                        </div>
                        <div>
                            <dt class="gov-label">Diterima</dt>
                            <dd class="mt-0.5 text-slate-800">{{ $s->tgl_diterima?->translatedFormat('d F Y') }}</dd>
                        </div>
                    </div>
                    <div class="px-5 py-3">
                        <dt class="gov-label">No. Surat</dt>
                        <dd class="mt-0.5 font-mono text-xs text-slate-800">{{ $s->no_surat }}</dd>
                    </div>
                    <div class="grid grid-cols-2 gap-2 px-5 py-3">
                        <div>
                            <dt class="gov-label">Klasifikasi</dt>
                            <dd class="mt-0.5 text-slate-800">{{ $s->klasifikasi?->kode ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="gov-label">Sifat</dt>
                            <dd class="mt-0.5"><x-badge :value="$s->sifat" type="sifat" /></dd>
                        </div>
                    </div>
                </dl>
                <p class="border-t border-slate-100 px-5 py-3 text-[11px] leading-relaxed text-slate-500">
                    Data diambil otomatis dari surat. Setelah disimpan, status surat diperbarui
                    dan PDF kartu diterbitkan.
                </p>
            </div>
        </div>

        <div class="lg:col-span-3">
            <div class="gov-card overflow-hidden">
                <div class="border-b border-slate-100 px-5 py-3">
                    <p class="text-xs font-bold uppercase tracking-wide text-slate-600">Isi Disposisi</p>
                </div>
                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <div>
                        <label class="gov-label" for="indeks">Index</label>
                        <input id="indeks" name="indeks" required value="{{ old('indeks', $s->klasifikasi?->kode) }}"
                               class="gov-input" placeholder="400.7">
                    </div>
                    <div>
                        <label class="gov-label" for="tgl_penyelesaian">Tanggal Penyelesaian</label>
                        <input type="date" id="tgl_penyelesaian" name="tgl_penyelesaian" required
                               value="{{ old('tgl_penyelesaian', now()->addDays(3)->toDateString()) }}" class="gov-input">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="gov-label" for="instruksi">Instruksi / Informasi</label>
                        <textarea id="instruksi" name="instruksi" rows="4" required class="gov-input"
                                  placeholder="Contoh: Tindak lanjuti, koordinasikan dengan bidang terkait, laporkan hasilnya.">{{ old('instruksi') }}</textarea>
                        <p class="gov-help">Tercetak pada kolom Instruksi/Informasi di kartu.</p>
                    </div>
                    <div class="sm:col-span-2">
                        <span class="gov-label">Diteruskan Kepada</span>
                        <div class="mt-1 grid gap-2 sm:grid-cols-2">
                            @foreach ($units as $u)
                                <label class="flex items-center gap-2 border border-slate-200 px-3 py-2 text-sm text-slate-700 hover:border-gov-400">
                                    <input type="checkbox" name="tujuan[]" value="{{ $u->id }}"
                                           @checked(in_array($u->id, old('tujuan', [])))
                                           class="rounded border-slate-300 text-gov-700 focus:ring-gov-500">
                                    {{ $u->nama }}
                                </label>
                            @endforeach
                        </div>
                        @error('tujuan')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap items-center justify-between gap-2 border-t border-slate-100 px-5 py-3">
                    <p class="text-[11px] text-slate-500">PDF kartu dibuat otomatis dan dilampirkan ke notifikasi unit tujuan.</p>
                    <div class="flex gap-2">
                        <a href="{{ route('surat-masuk.show', $s) }}" class="gov-btn-outline">Batal</a>
                        <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> ACC &amp; Teruskan</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
