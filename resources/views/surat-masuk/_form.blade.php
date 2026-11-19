@php
    $sifatList = ['biasa' => 'Biasa', 'segera' => 'Segera', 'penting' => 'Penting', 'rahasia' => 'Rahasia'];
@endphp

<div class="grid gap-4 lg:grid-cols-3">
    <div class="space-y-4 lg:col-span-2">
        <div class="gov-card">
            <div class="gov-card-head">
                <p class="gov-card-title">Identitas Surat</p>
                @if ($s->no_agenda)
                    <span class="font-mono text-xs text-slate-500">Agenda: {{ $s->no_agenda }}</span>
                @else
                    <span class="rounded-full bg-gov-50 px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide text-gov-700">Nomor agenda otomatis</span>
                @endif
            </div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="gov-label" for="instansi_id">Instansi Pengirim</label>
                    <select name="instansi_id" id="instansi_id" class="gov-input">
                        <option value="">— Pilih instansi terdaftar —</option>
                        @foreach ($instansi as $i)
                            <option value="{{ $i->id }}" @selected(old('instansi_id', $s->instansi_id) == $i->id)>{{ $i->nama }}</option>
                        @endforeach
                    </select>
                    <p class="gov-help">Belum terdaftar? Tulis nama instansi baru pada kolom di bawah.</p>
                    <input name="instansi_nama" value="{{ old('instansi_nama') }}" placeholder="Nama instansi baru (opsional)"
                           class="gov-input mt-2">
                </div>

                <div>
                    <label class="gov-label" for="no_surat">Nomor Surat</label>
                    <input name="no_surat" id="no_surat" value="{{ old('no_surat', $s->no_surat) }}" required
                           class="gov-input" placeholder="400.7/868-TU">
                </div>

                <div>
                    <label class="gov-label" for="klasifikasi_id">Klasifikasi</label>
                    <select name="klasifikasi_id" id="klasifikasi_id" class="gov-input">
                        <option value="">— Tanpa klasifikasi —</option>
                        @foreach ($klasifikasi as $k)
                            <option value="{{ $k->id }}" @selected(old('klasifikasi_id', $s->klasifikasi_id) == $k->id)>{{ $k->kode }} — {{ $k->uraian }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="gov-label" for="tgl_surat">Tanggal Surat</label>
                    <input type="date" name="tgl_surat" id="tgl_surat" required
                           value="{{ old('tgl_surat', $s->tgl_surat?->format('Y-m-d')) }}" class="gov-input">
                </div>

                <div>
                    <label class="gov-label" for="tgl_diterima">Tanggal Diterima</label>
                    <input type="date" name="tgl_diterima" id="tgl_diterima" required
                           value="{{ old('tgl_diterima', $s->tgl_diterima?->format('Y-m-d') ?? now()->toDateString()) }}" class="gov-input">
                </div>

                <div class="sm:col-span-2">
                    <label class="gov-label" for="perihal">Perihal</label>
                    <input name="perihal" id="perihal" value="{{ old('perihal', $s->perihal) }}" required
                           class="gov-input" placeholder="Contoh: Permohonan Data Kesehatan Wilayah">
                </div>

                <div>
                    <label class="gov-label" for="sifat">Sifat Surat</label>
                    <select name="sifat" id="sifat" class="gov-input">
                        @foreach ($sifatList as $nilai => $label)
                            <option value="{{ $nilai }}" @selected(old('sifat', $s->sifat ?? 'biasa') === $nilai)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-4">
        <div class="gov-card">
            <div class="gov-card-head"><p class="gov-card-title">Scan Surat</p></div>
            <div class="p-5">
                <input type="file" name="scan" accept=".pdf,.jpg,.jpeg,.png" class="gov-input !p-2 text-xs">
                <p class="gov-help">Format PDF, JPG, atau PNG. Maksimal 5 MB.</p>

                @if ($s->exists && $s->lampiran->isNotEmpty())
                    <div class="mt-4 space-y-2 border-t border-slate-100 pt-4">
                        <p class="text-[11px] font-bold uppercase tracking-wide text-slate-500">Berkas tersimpan</p>
                        @foreach ($s->lampiran as $l)
                            <a href="{{ route('lampiran.unduh', $l) }}" class="flex items-center gap-2 rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600 hover:border-gov-300 hover:text-gov-700">
                                <x-icon name="document" class="h-4 w-4 shrink-0" />
                                <span class="truncate">{{ $l->nama_file }}</span>
                                <x-icon name="download" class="ml-auto h-4 w-4 shrink-0" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="gov-card p-5">
            <p class="text-xs leading-relaxed text-slate-500">
                Nomor agenda otomatis mengikuti pola <span class="font-mono font-semibold text-gov-800">AGD/0001/XI/2026</span>
                menurut unit pencatat dan tahun berjalan.
            </p>
        </div>
    </div>
</div>
