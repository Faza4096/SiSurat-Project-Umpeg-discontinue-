@extends('layouts.sisurat')

@section('judul', 'Log Aktivitas')
@section('subjudul', 'Jejak audit aktivitas registrasi surat dan disposisi.')

@section('isi')
    <form method="GET" class="gov-card mb-4 p-4">
        <div class="grid gap-3 md:grid-cols-[1fr_16rem_auto]">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                    <x-icon name="search" class="h-4 w-4" />
                </span>
                <input name="cari" value="{{ request('cari') }}" placeholder="Cari aksi atau keterangan…" class="gov-input pl-9">
            </div>
            <select name="pengguna" class="gov-input">
                <option value="">Semua pengguna</option>
                @foreach ($pengguna as $u)
                    <option value="{{ $u->id }}" @selected(request('pengguna') == $u->id)>{{ $u->name }}</option>
                @endforeach
            </select>
            <div class="flex gap-2">
                <button class="gov-btn w-full md:w-auto"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
                @if (request()->hasAny(['cari', 'pengguna']))
                    <a href="{{ route('log.index') }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Riwayat Aktivitas</p>
            <p class="text-xs text-slate-500">{{ $items->total() }} catatan</p>
        </div>
        <div class="overflow-x-auto">
            <table class="gov-table-w760 divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">Waktu</th>
                        <th class="gov-th">Pengguna</th>
                        <th class="gov-th">Aksi</th>
                        <th class="gov-th">Keterangan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($items as $item)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td whitespace-nowrap text-xs text-slate-500">{{ $item->waktu?->translatedFormat('d M Y H:i') }}</td>
                            <td class="gov-td text-xs font-semibold text-slate-700">{{ $item->pengguna?->name ?? 'Sistem' }}</td>
                            <td class="gov-td"><span class="rounded bg-slate-100 px-2 py-0.5 font-mono text-[11px] text-slate-600">{{ $item->aksi }}</span></td>
                            <td class="gov-td text-xs text-slate-600">{{ $item->keterangan ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-12 text-center text-sm text-slate-500">Belum ada aktivitas tercatat.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $items->links() }}</div>
        @endif
    </div>
@endsection
