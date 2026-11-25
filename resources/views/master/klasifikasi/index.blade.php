@extends('layouts.sisurat')

@section('judul', 'Klasifikasi Surat')
@section('subjudul', 'Kode klasifikasi menjadi dasar penomoran surat keluar.')

@section('aksi')
    <a href="{{ route('klasifikasi.create') }}" class="gov-btn"><x-icon name="plus" class="h-4 w-4" /> Tambah Klasifikasi</a>
@endsection

@section('isi')
    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Daftar Klasifikasi</p>
            <p class="text-xs text-slate-500">{{ $items->total() }} kode</p>
        </div>
        <div class="overflow-x-auto">
            <table class="gov-table-w620 divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">Kode</th>
                        <th class="gov-th">Uraian</th>
                        <th class="gov-th">Surat Masuk</th>
                        <th class="gov-th">Surat Keluar</th>
                        <th class="gov-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td font-mono text-xs font-semibold text-gov-800">{{ $item->kode }}</td>
                            <td class="gov-td font-semibold text-slate-700">{{ $item->uraian }}</td>
                            <td class="gov-td text-xs text-slate-500">{{ $item->surat_masuk_count }}</td>
                            <td class="gov-td text-xs text-slate-500">{{ $item->surat_keluar_count }}</td>
                            <td class="gov-td">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('klasifikasi.edit', $item) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Ubah">
                                        <x-icon name="edit" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('klasifikasi.destroy', $item) }}" onsubmit="return confirm('Hapus klasifikasi {{ $item->kode }}?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-md p-1.5 text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada klasifikasi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
