@extends('layouts.sisurat')

@section('judul', 'Pengguna')
@section('subjudul', 'Akun aparatur, peran, dan unit kerjanya.')

@section('aksi')
    <a href="{{ route('pengguna.create') }}" class="gov-btn"><x-icon name="plus" class="h-4 w-4" /> Tambah Pengguna</a>
@endsection

@section('isi')
    <form method="GET" class="gov-card mb-4 p-4">
        <div class="grid gap-3 md:grid-cols-[1fr_14rem_auto]">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input name="cari" value="{{ request('cari') }}" placeholder="Cari nama, email, atau NIP…" class="gov-input pl-9">
            </div>
            <select name="role" class="gov-input">
                <option value="">Semua peran</option>
                @foreach ($roles as $role)
                    <option value="{{ $role->nama }}" @selected(request('role') === $role->nama)>{{ $role->sebutan }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="gov-btn w-full md:w-auto"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
                @if (request()->hasAny(['cari', 'role']))
                    <a href="{{ route('pengguna.index') }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Daftar Pengguna</p>
            <p class="text-xs text-slate-500">{{ $items->total() }} akun</p>
        </div>
        <div class="overflow-x-auto">
            <table class="gov-table-w620 divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">Nama / NIP</th>
                        <th class="gov-th">Email</th>
                        <th class="gov-th">Peran</th>
                        <th class="gov-th">Unit Kerja</th>
                        <th class="gov-th text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td">
                                <p class="font-semibold text-slate-700">{{ $item->name }}</p>
                                <p class="text-xs text-slate-500">{{ $item->nip ? 'NIP ' . $item->nip : ($item->jabatan ?: '-') }}</p>
                            </td>
                            <td class="gov-td text-xs text-slate-600">{{ $item->email }}</td>
                            <td class="gov-td"><x-badge :value="$item->role?->nama" type="role" /></td>
                            <td class="gov-td text-xs text-slate-600">{{ $item->unit?->nama ?? '-' }}</td>
                            <td class="gov-td">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('pengguna.edit', $item) }}" class="rounded-md p-1.5 text-slate-500 hover:bg-gov-50 hover:text-gov-700" title="Ubah">
                                        <x-icon name="edit" class="h-4 w-4" />
                                    </a>
                                    <form method="POST" action="{{ route('pengguna.destroy', $item) }}" onsubmit="return confirm('Hapus akun {{ $item->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="rounded-md p-1.5 text-slate-500 hover:bg-red-50 hover:text-red-600" title="Hapus">
                                            <x-icon name="trash" class="h-4 w-4" />
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada pengguna.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
