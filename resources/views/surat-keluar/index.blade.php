@extends('layouts.sisurat')

@section('judul', 'Surat Keluar')
@section('subjudul', 'Penomoran otomatis, persetujuan, pengiriman, dan arsip.')

@section('aksi')
    <a href="{{ route('laporan.index', ['jenis' => 'keluar']) }}" class="gov-btn-outline">
        <x-icon name="chart" class="h-4 w-4" /> Laporan
    </a>
    @if (auth()->user()->punyaRole('staf', 'admin'))
        <a href="{{ route('surat-keluar.create') }}" class="gov-btn">
            <x-icon name="plus" class="h-4 w-4" /> Buat Surat Keluar
        </a>
    @endif
@endsection

@section('isi')
    <form method="GET" class="gov-card mb-4 p-4">
        <div class="grid grid-cols-2 items-end gap-3 md:grid-cols-2 xl:grid-cols-6">
            <div class="relative col-span-2 xl:col-span-2">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input name="cari" value="{{ request('cari') }}" placeholder="Cari nomor surat atau hal…" class="gov-input pl-9">
            </div>
            <div>
                <label class="gov-help" for="status">Status</label>
                <select id="status" name="status" class="gov-input">
                    <option value="">Semua status</option>
                    <option value="{{ implode(',', App\Models\SuratKeluar::BELUM_DISETUJUI) }}" @selected(request('status') === implode(',', App\Models\SuratKeluar::BELUM_DISETUJUI))>Belum disetujui</option>
                    @foreach (App\Models\SuratKeluar::STATUS as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-help" for="instansi_id">Tujuan</label>
                <select id="instansi_id" name="instansi_id" class="gov-input">
                    <option value="">Semua tujuan</option>
                    @foreach ($instansi as $i)
                        <option value="{{ $i->id }}" @selected(request('instansi_id') == $i->id)>{{ $i->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-help" for="klasifikasi_id">Klasifikasi</label>
                <select id="klasifikasi_id" name="klasifikasi_id" class="gov-input">
                    <option value="">Semua klasifikasi</option>
                    @foreach ($klasifikasi as $k)
                        <option value="{{ $k->id }}" @selected(request('klasifikasi_id') == $k->id)>{{ $k->kode }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-help" for="dari">Dari tanggal</label>
                <input type="date" id="dari" name="dari" value="{{ request('dari') }}" class="gov-input">
            </div>
            <div>
                <label class="gov-help" for="sampai">Sampai</label>
                <input type="date" id="sampai" name="sampai" value="{{ request('sampai') }}" class="gov-input">
            </div>
            <div class="col-span-2 flex gap-2 xl:col-span-2">
                <button class="gov-btn w-full md:w-auto"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
                @if (request()->hasAny(['cari', 'status', 'instansi_id', 'klasifikasi_id', 'dari', 'sampai']))
                    <a href="{{ route('surat-keluar.index') }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Daftar Surat Keluar</p>
            <p class="text-xs text-slate-500">{{ $surat->total() }} surat terlihat</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">Nomor Surat</th>
                        <th class="gov-th">Hal / Tujuan</th>
                        <th class="gov-th">Unit</th>
                        <th class="gov-th">Tanggal</th>
                        <th class="gov-th">Status</th>
                        <th class="gov-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($surat as $s)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td whitespace-nowrap font-mono text-xs font-semibold text-gov-800">{{ $s->no_surat }}</td>
                            <td class="gov-td">
                                <a href="{{ route('surat-keluar.show', $s) }}" class="font-semibold text-slate-800 hover:text-gov-700">{{ $s->hal }}</a>
                                <p class="mt-0.5 text-xs text-slate-500">Kepada {{ $s->instansi?->nama ?? '-' }}</p>
                            </td>
                            <td class="gov-td text-xs">{{ $s->unit?->kode ?? '-' }}</td>
                            <td class="gov-td whitespace-nowrap text-xs">
                                {{ $s->tgl_surat?->translatedFormat('d M Y') }}
                                @if ($s->tgl_kirim)
                                    <p class="text-slate-400">Kirim: {{ $s->tgl_kirim->translatedFormat('d M Y') }}</p>
                                @endif
                            </td>
                            <td class="gov-td"><x-badge :value="$s->status" /></td>
                            <td class="gov-td">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('surat-keluar.show', $s) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Lihat">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    @if (auth()->user()->can('update', $s))
                                        <a href="{{ route('surat-keluar.edit', $s) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Ubah">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    @endif
                                    @if (auth()->user()->can('delete', $s))
                                        <form method="POST" action="{{ route('surat-keluar.destroy', $s) }}" onsubmit="return confirm('Pindahkan surat {{ $s->no_surat }} ke arsip terhapus?')">
                                            @csrf @method('DELETE')
                                            <button class="rounded-md p-1.5 text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                                <x-icon name="trash" class="h-4 w-4" />
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-14 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <x-icon name="outbox" class="h-6 w-6" />
                                </span>
                                <p class="mt-3 text-sm font-semibold text-slate-600">Tidak ada surat keluar</p>
                                <p class="mt-1 text-xs text-slate-500">Buat surat keluar untuk memperoleh nomor otomatis.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($surat->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $surat->links() }}</div>
        @endif
    </div>
@endsection
