@extends('layouts.sisurat')

@section('judul', 'Diarsipkan')
@section('subjudul', 'Surat masuk dan keluar yang sudah diarsipkan beserta tanggal dan petugas arsipnya.')

@section('aksi')
    <a href="{{ route('arsip.ekspor', request()->query()) }}" class="gov-btn-outline">
        <x-icon name="download" class="h-4 w-4" /> Ekspor CSV
    </a>
    <a href="{{ route('arsip.pdf', request()->query()) }}" target="_blank" class="gov-btn">
        <x-icon name="print" class="h-4 w-4" /> Ekspor PDF
    </a>
@endsection

@section('isi')
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('arsip.index', ['jenis' => 'masuk']) }}"
           class="inline-flex items-center gap-2 rounded-md border px-3.5 py-2 text-[12.5px] font-semibold transition {{ $jenis === 'masuk' ? 'border-gov-700 bg-gov-800 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-gov-300 hover:text-gov-700' }}">
            <x-icon name="mail-in" class="h-4 w-4" /> Surat Masuk
            <span class="rounded-full px-1.5 py-0.5 text-[10.5px] tabular-nums {{ $jenis === 'masuk' ? 'bg-white/15' : 'bg-slate-100 text-slate-500' }}">{{ $jumlah['masuk'] }}</span>
        </a>
        <a href="{{ route('arsip.index', ['jenis' => 'keluar']) }}"
           class="inline-flex items-center gap-2 rounded-md border px-3.5 py-2 text-[12.5px] font-semibold transition {{ $jenis === 'keluar' ? 'border-gov-700 bg-gov-800 text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-gov-300 hover:text-gov-700' }}">
            <x-icon name="mail-out" class="h-4 w-4" /> Surat Keluar
            <span class="rounded-full px-1.5 py-0.5 text-[10.5px] tabular-nums {{ $jenis === 'keluar' ? 'bg-white/15' : 'bg-slate-100 text-slate-500' }}">{{ $jumlah['keluar'] }}</span>
        </a>
    </div>

    <form method="GET" class="gov-card mb-4 p-4">
        <input type="hidden" name="jenis" value="{{ $jenis }}">
        <div class="grid grid-cols-2 items-end gap-3 md:grid-cols-2 xl:grid-cols-5">
            <div class="relative col-span-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <label for="cari" class="sr-only">Cari arsip</label>
                <input id="cari" name="cari" value="{{ request('cari') }}"
                       placeholder="{{ $jenis === 'masuk' ? 'Cari nomor agenda, nomor surat, perihal, atau lokasi arsip…' : 'Cari nomor surat, hal, atau penerima…' }}"
                       class="gov-input pl-9">
            </div>
            <div>
                <label class="gov-label" for="instansi_id">{{ $jenis === 'masuk' ? 'Pengirim' : 'Tujuan' }}</label>
                <select id="instansi_id" name="instansi_id" class="gov-input" x-on:change="$el.form.requestSubmit()">
                    <option value="">Semua instansi</option>
                    @foreach ($instansi as $i)
                        <option value="{{ $i->id }}" @selected(request('instansi_id') == $i->id)>{{ $i->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-label" for="klasifikasi_id">Klasifikasi</label>
                <select id="klasifikasi_id" name="klasifikasi_id" class="gov-input" x-on:change="$el.form.requestSubmit()">
                    <option value="">Semua klasifikasi</option>
                    @foreach ($klasifikasi as $k)
                        <option value="{{ $k->id }}" @selected(request('klasifikasi_id') == $k->id)>{{ $k->kode }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 flex gap-2">
                <button class="gov-btn w-full md:w-auto"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
                @if (request()->hasAny(['cari', 'instansi_id', 'klasifikasi_id']))
                    <a href="{{ route('arsip.index', ['jenis' => $jenis]) }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">{{ $jenis === 'masuk' ? 'Surat Masuk Diarsipkan' : 'Surat Keluar Diarsipkan' }}</p>
            <p class="text-xs text-slate-500">{{ $surat->total() }} surat diarsipkan</p>
        </div>

        <div class="overflow-x-auto">
            @if ($jenis === 'masuk')
                <table class="gov-table-w980 divide-y divide-slate-100">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="gov-th w-[140px]">No. Agenda</th>
                            <th class="gov-th">Perihal &amp; Nomor Surat</th>
                            <th class="gov-th w-[176px]">Pengirim</th>
                            <th class="gov-th w-[196px]">Diarsipkan</th>
                            <th class="gov-th w-[180px]">Lokasi Arsip</th>
                            <th class="gov-th w-[72px] pr-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($surat as $s)
                            <tr class="hover:bg-slate-50/70">
                                <td class="gov-td whitespace-nowrap font-mono text-xs font-semibold tabular-nums text-gov-800">{{ $s->no_agenda }}</td>
                                <td class="gov-td">
                                    <a href="{{ route('surat-masuk.show', $s) }}" class="font-semibold text-slate-800 hover:text-gov-700">{{ $s->perihal }}</a>
                                    <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $s->no_surat }}</p>
                                </td>
                                <td class="gov-td text-xs leading-snug">{{ $s->instansi?->nama ?? '-' }}</td>
                                <td class="gov-td whitespace-nowrap text-xs tabular-nums">
                                    {{ $s->diarsipkan_pada?->translatedFormat('d M Y') ?? '-' }}
                                    <p class="text-slate-400">{{ $s->pengarsip?->name ?? 'Sistem' }}</p>
                                </td>
                                <td class="gov-td text-xs leading-snug">{{ $s->lokasi_arsip ?: '-' }}</td>
                                <td class="gov-td pr-5">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('surat-masuk.show', $s) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Lihat">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-icon name="document" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-semibold text-slate-600">Belum ada surat masuk diarsipkan</p>
                                    <p class="mt-1 text-xs text-slate-500">Surat masuk yang tindak lanjutnya selesai dapat diarsipkan dari halaman detail surat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @else
                <table class="gov-table-w1040 divide-y divide-slate-100">
                    <thead class="bg-slate-50/70">
                        <tr>
                            <th class="gov-th w-[196px]">Nomor Surat</th>
                            <th class="gov-th">Hal</th>
                            <th class="gov-th w-[190px]">Tujuan &amp; Unit</th>
                            <th class="gov-th w-[160px]">Pengiriman</th>
                            <th class="gov-th w-[176px]">Diarsipkan</th>
                            <th class="gov-th w-[72px] pr-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($surat as $s)
                            <tr class="hover:bg-slate-50/70">
                                <td class="gov-td whitespace-nowrap font-mono text-xs font-semibold tabular-nums text-gov-800">{{ $s->no_surat }}</td>
                                <td class="gov-td">
                                    <a href="{{ route('surat-keluar.show', $s) }}" class="font-semibold text-slate-800 hover:text-gov-700">{{ $s->hal }}</a>
                                    <p class="mt-0.5 text-xs text-slate-500">{{ $s->klasifikasi?->kode ?? '-' }}</p>
                                </td>
                                <td class="gov-td text-xs leading-snug">
                                    {{ $s->instansi?->nama ?? '-' }}
                                    <p class="text-slate-400">Unit {{ $s->unit?->kode ?? '-' }}</p>
                                </td>
                                <td class="gov-td text-xs leading-snug">
                                    @if ($s->tgl_kirim)
                                        <span class="tabular-nums">{{ $s->tgl_kirim->translatedFormat('d M Y') }}</span>
                                        <p class="text-slate-400">{{ $s->penerima ?: 'Penerima tidak dicatat' }}</p>
                                    @else
                                        <span class="text-slate-400">Belum dikirim</span>
                                    @endif
                                </td>
                                <td class="gov-td whitespace-nowrap text-xs tabular-nums">
                                    {{ $s->diarsipkan_pada?->translatedFormat('d M Y') ?? '-' }}
                                    <p class="text-slate-400">{{ $s->pengarsip?->name ?? 'Sistem' }}</p>
                                </td>
                                <td class="gov-td pr-5">
                                    <div class="flex items-center justify-end">
                                        <a href="{{ route('surat-keluar.show', $s) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Lihat">
                                            <x-icon name="eye" class="h-4 w-4" />
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-14 text-center">
                                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                        <x-icon name="document" class="h-6 w-6" />
                                    </span>
                                    <p class="mt-3 text-sm font-semibold text-slate-600">Belum ada surat keluar diarsipkan</p>
                                    <p class="mt-1 text-xs text-slate-500">Surat keluar dapat diarsipkan setelah berkas final diunggah dan pengiriman tercatat.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            @endif
        </div>

        @if ($surat->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $surat->links() }}</div>
        @endif
    </div>
@endsection
