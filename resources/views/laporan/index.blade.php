@extends('layouts.sisurat')

@section('judul', 'Laporan & Ekspor')
@section('subjudul', 'Rekap arsip surat menurut tanggal, instansi, dan klasifikasi.')

@section('aksi')
    <a href="{{ route('laporan.tindak-lanjut') }}" class="gov-btn-outline">
        <x-icon name="check" class="h-4 w-4" /> Laporan Tindak Lanjut
    </a>
    <a href="{{ route('laporan.ekspor', request()->query()) }}" class="gov-btn-outline">
        <x-icon name="download" class="h-4 w-4" /> Ekspor Excel
    </a>
    <a href="{{ route('laporan.pdf', request()->query()) }}" target="_blank" class="gov-btn">
        <x-icon name="print" class="h-4 w-4" /> Ekspor PDF
    </a>
@endsection

@section('isi')
    <form method="GET" class="gov-card mb-4 p-4">
        <div class="grid grid-cols-2 items-end gap-3 md:grid-cols-2 xl:grid-cols-4">
            <div>
                <label class="gov-label" for="jenis">Jenis Laporan</label>
                <select name="jenis" id="jenis" class="gov-input">
                    <option value="masuk" @selected($jenis === 'masuk')>Surat Masuk</option>
                    <option value="keluar" @selected($jenis === 'keluar')>Surat Keluar</option>
                </select>
            </div>
            <div>
                <label class="gov-label" for="dari">Dari Tanggal</label>
                <input type="date" name="dari" id="dari" value="{{ request('dari') }}" class="gov-input">
            </div>
            <div>
                <label class="gov-label" for="sampai">Sampai Tanggal</label>
                <input type="date" name="sampai" id="sampai" value="{{ request('sampai') }}" class="gov-input">
            </div>
            <div>
                <label class="gov-label" for="status">Status</label>
                <select name="status" id="status" class="gov-input">
                    <option value="">Semua status</option>
                    @foreach ($jenis === 'masuk' ? \App\Models\SuratMasuk::STATUS : \App\Models\SuratKeluar::STATUS as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-label" for="instansi_id">Instansi</label>
                <select name="instansi_id" id="instansi_id" class="gov-input">
                    <option value="">Semua instansi</option>
                    @foreach ($instansi as $i)
                        <option value="{{ $i->id }}" @selected(request('instansi_id') == $i->id)>{{ $i->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-label" for="klasifikasi_id">Klasifikasi</label>
                <select name="klasifikasi_id" id="klasifikasi_id" class="gov-input">
                    <option value="">Semua klasifikasi</option>
                    @foreach ($klasifikasi as $k)
                        <option value="{{ $k->id }}" @selected(request('klasifikasi_id') == $k->id)>{{ $k->kode }} — {{ $k->uraian }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-span-2 flex items-end gap-2 xl:col-span-2">
                <button class="gov-btn"><x-icon name="filter" class="h-4 w-4" /> Terapkan Filter</button>
                @if (request()->hasAny(['dari', 'sampai', 'status', 'instansi_id', 'klasifikasi_id']))
                    <a href="{{ route('laporan.index', ['jenis' => $jenis]) }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach ($ringkasan as $status => $jumlah)
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600">
                <x-badge :value="$status" /> {{ $jumlah }}
            </span>
        @endforeach
        <span class="inline-flex items-center gap-2 rounded-full bg-gov-900 px-3 py-1.5 text-xs font-semibold text-white">
            Total {{ $data->count() }} surat
        </span>
    </div>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">{{ $jenis === 'masuk' ? 'Rekap Surat Masuk' : 'Rekap Surat Keluar' }}</p>
            <p class="text-xs text-slate-500">Data sesuai hak akses</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[880px] divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">Nomor</th>
                        <th class="gov-th">{{ $jenis === 'masuk' ? 'Pengirim' : 'Tujuan' }}</th>
                        <th class="gov-th">Perihal / Hal</th>
                        <th class="gov-th">Klasifikasi</th>
                        <th class="gov-th">Tanggal</th>
                        <th class="gov-th">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($data as $s)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td whitespace-nowrap font-mono text-xs">
                                {{ $jenis === 'masuk' ? $s->no_agenda : $s->no_surat }}
                                <p class="text-[11px] text-slate-400">{{ $jenis === 'masuk' ? $s->no_surat : $s->unit?->kode }}</p>
                            </td>
                            <td class="gov-td text-xs">{{ $s->instansi?->nama ?? '-' }}</td>
                            <td class="gov-td">
                                <a href="{{ route($jenis === 'masuk' ? 'surat-masuk.show' : 'surat-keluar.show', $s) }}"
                                   class="font-semibold text-slate-800 hover:text-gov-700">
                                    {{ $jenis === 'masuk' ? $s->perihal : $s->hal }}
                                </a>
                                @if ($jenis === 'keluar' && $s->penerima)
                                    <p class="text-[11px] text-slate-500">Dikirim ke {{ $s->penerima }} · {{ $s->tgl_kirim?->translatedFormat('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="gov-td text-xs">{{ $s->klasifikasi?->kode ?? '-' }}</td>
                            <td class="gov-td whitespace-nowrap text-xs">
                                {{ ($jenis === 'masuk' ? $s->tgl_diterima : $s->tgl_surat)?->translatedFormat('d M Y') }}
                            </td>
                            <td class="gov-td"><x-badge :value="$s->status" /></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center text-sm text-slate-500">
                                Tidak ada surat pada rentang filter tersebut.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
