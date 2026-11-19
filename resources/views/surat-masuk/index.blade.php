@extends('layouts.sisurat')

@section('judul', 'Surat Masuk')
@section('subjudul', 'Registrasi surat masuk, disposisi, tindak lanjut, dan arsip.')

@section('aksi')
    <a href="{{ route('laporan.index', ['jenis' => 'masuk']) }}" class="gov-btn-outline">
        <x-icon name="chart" class="h-4 w-4" /> Laporan
    </a>
    @if (auth()->user()->punyaRole('staf', 'admin'))
        <a href="{{ route('surat-masuk.create') }}" class="gov-btn">
            <x-icon name="plus" class="h-4 w-4" /> Catat Surat Masuk
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
                <input name="cari" value="{{ request('cari') }}" placeholder="Cari nomor agenda, nomor surat, atau perihal…" class="gov-input pl-9">
            </div>
            <select name="status" class="gov-input">
                <option value="">Semua status</option>
                @foreach (App\Models\SuratMasuk::STATUS as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="sifat" class="gov-input">
                <option value="">Semua sifat</option>
                @foreach (['biasa' => 'Biasa', 'segera' => 'Segera', 'penting' => 'Penting', 'rahasia' => 'Rahasia'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected(request('sifat') === $nilai)>{{ $label }}</option>
                @endforeach
            </select>
            <select name="instansi_id" class="gov-input">
                <option value="">Semua instansi</option>
                @foreach ($instansi as $i)
                    <option value="{{ $i->id }}" @selected(request('instansi_id') == $i->id)>{{ $i->nama }}</option>
                @endforeach
            </select>
            <select name="klasifikasi_id" class="gov-input">
                <option value="">Semua klasifikasi</option>
                @foreach ($klasifikasi as $k)
                    <option value="{{ $k->id }}" @selected(request('klasifikasi_id') == $k->id)>{{ $k->kode }}</option>
                @endforeach
            </select>
            <div>
                <label class="gov-help" for="dari">Diterima dari</label>
                <input type="date" id="dari" name="dari" value="{{ request('dari') }}" class="gov-input">
            </div>
            <div>
                <label class="gov-help" for="sampai">Sampai</label>
                <input type="date" id="sampai" name="sampai" value="{{ request('sampai') }}" class="gov-input">
            </div>
            <div class="col-span-2 flex gap-2 xl:col-span-2">
                <button class="gov-btn w-full md:w-auto"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
                @if (request()->hasAny(['cari', 'status', 'sifat', 'instansi_id', 'klasifikasi_id', 'dari', 'sampai']))
                    <a href="{{ route('surat-masuk.index') }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Daftar Surat Masuk</p>
            <p class="text-xs text-slate-500">{{ $surat->total() }} surat terlihat</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-[900px] divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">No. Agenda</th>
                        <th class="gov-th">Perihal &amp; Nomor Surat</th>
                        <th class="gov-th">Pengirim</th>
                        <th class="gov-th">Tanggal</th>
                        <th class="gov-th">Sifat</th>
                        <th class="gov-th">Status</th>
                        <th class="gov-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($surat as $s)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td whitespace-nowrap font-mono text-xs font-semibold text-gov-800">{{ $s->no_agenda }}</td>
                            <td class="gov-td">
                                <a href="{{ route('surat-masuk.show', $s) }}" class="font-semibold text-slate-800 hover:text-gov-700">{{ $s->perihal }}</a>
                                <p class="mt-0.5 font-mono text-xs text-slate-500">{{ $s->no_surat }}</p>
                            </td>
                            <td class="gov-td text-xs">{{ $s->instansi?->nama ?? '-' }}</td>
                            <td class="gov-td whitespace-nowrap text-xs">
                                {{ $s->tgl_diterima?->translatedFormat('d M Y') }}
                                <p class="text-slate-400">Surat: {{ $s->tgl_surat?->translatedFormat('d M Y') }}</p>
                            </td>
                            <td class="gov-td"><x-badge :value="$s->sifat" type="sifat" /></td>
                            <td class="gov-td"><x-badge :value="$s->status" /></td>
                            <td class="gov-td">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('surat-masuk.show', $s) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Lihat">
                                        <x-icon name="eye" class="h-4 w-4" />
                                    </a>
                                    @if (auth()->user()->can('update', $s))
                                        <a href="{{ route('surat-masuk.edit', $s) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Ubah">
                                            <x-icon name="edit" class="h-4 w-4" />
                                        </a>
                                    @endif
                                    @if (auth()->user()->can('delete', $s))
                                        <form method="POST" action="{{ route('surat-masuk.destroy', $s) }}"
                                              onsubmit="return confirm('Pindahkan surat {{ $s->no_agenda }} ke arsip terhapus?')">
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
                            <td colspan="7" class="px-4 py-14 text-center">
                                <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                                    <x-icon name="inbox" class="h-6 w-6" />
                                </span>
                                <p class="mt-3 text-sm font-semibold text-slate-600">Tidak ada surat masuk</p>
                                <p class="mt-1 text-xs text-slate-500">Ubah filter atau catat surat baru.</p>
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
