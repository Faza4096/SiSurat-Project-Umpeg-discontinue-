@extends('layouts.sisurat')

@section('judul', 'Laporan Tindak Lanjut')
@section('subjudul', 'Rekap status disposisi per unit kerja beserta buktinya.')

@section('aksi')
    <a href="{{ route('laporan.index') }}" class="gov-btn-outline">
        <x-icon name="chart" class="h-4 w-4" /> Laporan Surat
    </a>
@endsection

@section('isi')
    <form method="GET" class="gov-card mb-4 p-4">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
            <div>
                <label class="gov-label" for="unit">Unit Kerja</label>
                <select name="unit" id="unit" class="gov-input">
                    <option value="">Semua unit</option>
                    @foreach ($units as $u)
                        <option value="{{ $u->id }}" @selected(request('unit') == $u->id)>{{ $u->nama }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="gov-label" for="status">Status</label>
                <select name="status" id="status" class="gov-input">
                    <option value="">Semua status</option>
                    @foreach (App\Models\DisposisiTujuan::STATUS as $nilai => $label)
                        <option value="{{ $nilai }}" @selected(request('status') === $nilai)>{{ $label }}</option>
                    @endforeach
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
            <div class="flex items-end gap-2">
                <button class="gov-btn w-full"><x-icon name="filter" class="h-4 w-4" /> Terapkan</button>
                @if (request()->hasAny(['unit', 'status', 'dari', 'sampai']))
                    <a href="{{ route('laporan.tindak-lanjut') }}" class="gov-btn-outline">Reset</a>
                @endif
            </div>
        </div>
    </form>

    <div class="mb-4 flex flex-wrap gap-2">
        @foreach (App\Models\DisposisiTujuan::STATUS as $nilai => $label)
            <span class="inline-flex items-center gap-2 rounded-full border border-slate-200 bg-white px-3 py-1.5 text-xs text-slate-600">
                <x-badge :value="$nilai" /> {{ (int) ($ringkasan[$nilai] ?? 0) }}
            </span>
        @endforeach
    </div>

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Tindak Lanjut Disposisi</p>
            <p class="text-xs text-slate-500">{{ $data->total() }} baris</p>
        </div>

        <div class="overflow-x-auto">
            <table class="gov-table-w820 divide-y divide-slate-100">
                <thead class="bg-slate-50/70">
                    <tr>
                        <th class="gov-th">Agenda / Perihal</th>
                        <th class="gov-th">Unit Tujuan</th>
                        <th class="gov-th">Batas</th>
                        <th class="gov-th">Status</th>
                        <th class="gov-th">Bukti</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($data as $t)
                        <tr class="hover:bg-slate-50/70">
                            <td class="gov-td">
                                @if ($t->disposisi?->suratMasuk)
                                    <a href="{{ route('surat-masuk.show', $t->disposisi->suratMasuk) }}"
                                       class="font-semibold text-slate-800 hover:text-gov-700">
                                        {{ $t->disposisi->suratMasuk->perihal }}
                                    </a>
                                    <p class="font-mono text-[11px] text-slate-500">{{ $t->disposisi->suratMasuk->no_agenda }}</p>
                                    <p class="mt-1 line-clamp-2 text-[11px] text-slate-500">{{ $t->disposisi->instruksi }}</p>
                                @else
                                    <span class="text-xs text-slate-400">Surat dihapus</span>
                                @endif
                            </td>
                            <td class="gov-td text-xs">{{ $t->unit?->nama ?? '-' }}</td>
                            <td class="gov-td whitespace-nowrap text-xs">
                                @if ($t->disposisi?->tgl_penyelesaian)
                                    <span class="{{ $t->disposisi->tgl_penyelesaian->isPast() && $t->status_tindak_lanjut !== 'selesai' ? 'font-semibold text-red-600' : 'text-slate-600' }}">
                                        {{ $t->disposisi->tgl_penyelesaian->translatedFormat('d M Y') }}
                                    </span>
                                @else
                                    -
                                @endif
                            </td>
                            <td class="gov-td">
                                <x-badge :value="$t->status_tindak_lanjut" />
                                @if ($t->tgl_selesai)
                                    <p class="mt-1 text-[11px] text-slate-500">{{ $t->tgl_selesai->translatedFormat('d M Y H:i') }}</p>
                                @endif
                            </td>
                            <td class="gov-td text-xs">
                                @if ($t->bukti_path)
                                    <a href="{{ route('tujuan.bukti.unduh', $t) }}" target="_blank"
                                       class="font-semibold text-gov-700 hover:underline">Lihat bukti</a>
                                    <p class="truncate text-[11px] text-slate-400">{{ $t->bukti_nama }}</p>
                                @else
                                    <span class="text-slate-400">Belum ada</span>
                                @endif
                                @if ($t->catatan)
                                    <p class="mt-1 line-clamp-2 text-[11px] text-slate-500">{{ $t->catatan }}</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-14 text-center text-sm text-slate-500">
                                Tidak ada tindak lanjut pada filter tersebut.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($data->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $data->links() }}</div>
        @endif
    </div>
@endsection
