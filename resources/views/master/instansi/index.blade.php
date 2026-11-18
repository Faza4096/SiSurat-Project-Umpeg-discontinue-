@extends('layouts.sisurat')

@section('judul', 'Instansi')
@section('subjudul', 'Data instansi pengirim dan tujuan surat.')

@section('aksi')
    <a href="{{ route('instansi.create') }}" class="gov-btn"><x-icon name="plus" class="h-4 w-4" /> Tambah Instansi</a>
@endsection

@section('isi')
    <form method="GET" class="gov-card mb-4 p-4">
        <div class="flex flex-wrap gap-3">
            <div class="relative min-w-0 flex-1">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input name="cari" value="{{ request('cari') }}" placeholder="Cari nama instansi…" class="gov-input pl-9">
            </div>
            <button class="gov-btn"><x-icon name="filter" class="h-4 w-4" /> Cari</button>
            @if (request('cari'))
                <a href="{{ route('instansi.index') }}" class="gov-btn-outline">Reset</a>
            @endif
        </div>
    </form>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Daftar Instansi</p>
            <p class="text-xs text-slate-500">{{ $items->total() }} instansi</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-[620px] divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">Nama Instansi</th>
                        <th class="gov-th">Alamat</th>
                        <th class="gov-th">Kontak</th>
                        <th class="gov-th">Surat</th>
                        <th class="gov-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td font-semibold text-slate-700">{{ $item->nama }}</td>
                            <td class="gov-td text-xs text-slate-500">{{ $item->alamat ?: '-' }}</td>
                            <td class="gov-td text-xs text-slate-500">
                                {{ $item->email ?: '-' }}
                                @if ($item->telepon)<p class="text-slate-400">{{ $item->telepon }}</p>@endif
                            </td>
                            <td class="gov-td whitespace-nowrap text-xs text-slate-500">
                                {{ $item->surat_masuk_count }} masuk · {{ $item->surat_keluar_count }} keluar
                            </td>
                            <td class="gov-td">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('instansi.edit', $item) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Ubah">
                                        <x-icon name="edit" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('instansi.destroy', $item) }}" onsubmit="return confirm('Hapus instansi {{ $item->nama }}?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-md p-1.5 text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada instansi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
