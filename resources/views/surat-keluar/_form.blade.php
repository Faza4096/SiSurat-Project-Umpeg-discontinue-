@php
    $sifatList = ['biasa' => 'Biasa', 'segera' => 'Segera', 'penting' => 'Penting', 'rahasia' => 'Rahasia'];
@endphp

@if (! empty($balasan))
    <div class="mb-4 flex flex-wrap items-center gap-3 rounded-xl border border-gov-200 bg-gov-50 px-4 py-3 text-sm text-gov-900">
        <x-icon name="mail-in" class="h-5 w-5 shrink-0 text-gov-700" />
        <p class="min-w-0 flex-1">
            Balasan untuk agenda <span class="font-mono font-semibold">{{ $balasan->no_agenda }}</span> —
            {{ $balasan->perihal }}
        </p>
        <a href="{{ route('surat-masuk.show', $balasan) }}" class="gov-btn-outline !px-3 !py-1.5 text-xs">Lihat surat</a>
    </div>
@endif

<input type="hidden" name="surat_masuk_id" value="{{ old('surat_masuk_id', $s->surat_masuk_id) }}">

<div class="grid gap-4 lg:grid-cols-3">
    <div class="gov-card lg:col-span-2">
        <div class="gov-card-head">
            <p class="gov-card-title">Data Surat Keluar</p>
            @if ($s->exists)
                <span class="font-mono text-xs text-slate-500">{{ $s->no_surat }}</span>
            @else
                <span class="rounded-full bg-gov-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-gov-700">Nomor surat otomatis</span>
            @endif
        </div>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <div>
                <label class="gov-label" for="klasifikasi_id">Klasifikasi</label>
                <select name="klasifikasi_id" id="klasifikasi_id" required class="gov-input">
                    @foreach ($klasifikasi as $k)
                        <option value="{{ $k->id }}" @selected(old('klasifikasi_id', $s->klasifikasi_id) == $k->id)>{{ $k->kode }} — {{ $k->uraian }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-label" for="unit_id">Unit Pengirim</label>
                <select name="unit_id" id="unit_id" required class="gov-input">
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}" @selected(old('unit_id', $s->unit_id) == $u->id)>{{ $u->kode }} — {{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="gov-label" for="instansi_id">Instansi Tujuan</label>
                <select name="instansi_id" id="instansi_id" class="gov-input">
                    <option value="">— Pilih instansi terdaftar —</option>
                    @foreach ($instansi as $i)
                        <option value="{{ $i->id }}" @selected(old('instansi_id', $s->instansi_id) == $i->id)>{{ $i->nama }}</option>
                    @endforeach
                </select>
                <input name="instansi_nama" value="{{ old('instansi_nama') }}" placeholder="Nama instansi baru (opsional)" class="gov-input mt-2">
            </div>

            <div>
                <label class="gov-label" for="tgl_surat">Tanggal Surat</label>
                <input type="date" name="tgl_surat" id="tgl_surat" required
                       value="{{ old('tgl_surat', $s->tgl_surat?->format('Y-m-d') ?? now()->toDateString()) }}" class="gov-input">
            </div>
            <div>
                <label class="gov-label" for="sifat">Sifat Surat</label>
                <select name="sifat" id="sifat" class="gov-input">
                    @foreach ($sifatList as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(old('sifat', $s->sifat ?? 'biasa') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="sm:col-span-2">
                <label class="gov-label" for="hal">Hal / Perihal</label>
                <input name="hal" id="hal" required value="{{ old('hal', $s->hal) }}" class="gov-input"
                       placeholder="Contoh: Undangan Rapat Koordinasi Kesehatan">
            </div>

            <div>
                <label class="gov-label" for="lampiran_ket">Lampiran</label>
                <input name="lampiran_ket" id="lampiran_ket" value="{{ old('lampiran_ket', $s->lampiran_ket) }}" class="gov-input" placeholder="-">
            </div>

            <div class="sm:col-span-2">
                <label class="gov-label" for="isi_ringkas">Isi Ringkas</label>
                <textarea name="isi_ringkas" id="isi_ringkas" rows="5" class="gov-input">{{ old('isi_ringkas', $s->isi_ringkas) }}</textarea>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="gov-card p-5">
            <p class="gov-card-title">Penomoran Otomatis</p>
            <p class="mt-2 text-xs leading-relaxed text-slate-500">
                Nomor surat dibentuk dari kode klasifikasi, nomor urut unit, dan kode unit, misalnya
                <span class="font-mono font-semibold text-gov-800">400.7/868-TU</span>.
                Nomor urut bertambah otomatis tanpa duplikat.
            </p>
        </div>
        <div class="gov-card p-5">
            <p class="gov-card-title">Alur Persetujuan</p>
            <ol class="mt-2 space-y-2 text-xs text-slate-500">
                <li class="flex gap-2"><span class="font-bold text-gov-700">1.</span> Draf dibuat dengan nomor otomatis.</li>
                <li class="flex gap-2"><span class="font-bold text-gov-700">2.</span> Draf diajukan untuk persetujuan.</li>
                <li class="flex gap-2"><span class="font-bold text-gov-700">3.</span> Disetujui atau dikembalikan untuk revisi.</li>
                <li class="flex gap-2"><span class="font-bold text-gov-700">4.</span> Berkas final diunggah dan pengiriman dicatat.</li>
                <li class="flex gap-2"><span class="font-bold text-gov-700">5.</span> Surat diarsipkan.</li>
            </ol>
        </div>
    </div>
</div>
