@extends('layouts.sisurat')

@section('judul', 'Notifikasi')
@section('subjudul', 'Disposisi baru, pengajuan tanda tangan, dan pengingat batas waktu.')

@section('aksi')
    @if (auth()->user()->unreadNotifications()->exists())
        <form method="POST" action="{{ route('notifikasi.baca-semua') }}">
            @csrf
            <button class="gov-btn-outline"><x-icon name="check" class="h-4 w-4" /> Tandai semua dibaca</button>
        </form>
    @endif
@endsection

@section('isi')
    @php
        $ikonNotifikasi = [
            'pengingat_disposisi' => 'calendar',
            'menunggu_ttd' => 'mail-out',
            'status_tindak_lanjut' => 'check',
        ];
    @endphp

    <div class="gov-card overflow-hidden">
        <div class="gov-card-head">
            <p class="gov-card-title">Kotak Masuk</p>
            <p class="text-xs text-slate-500">{{ $daftar->total() }} notifikasi</p>
        </div>

        <ul class="divide-y divide-slate-100">
            @forelse ($daftar as $n)
                <li class="{{ $n->read_at ? '' : 'bg-gold-50/40' }}">
                    <a href="{{ route('notifikasi.baca', $n->id) }}" class="flex items-start gap-3 px-5 py-4 hover:bg-slate-50">
                        <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $n->read_at ? 'bg-slate-100 text-slate-500' : 'bg-gov-700 text-white' }}">
                            <x-icon :name="$ikonNotifikasi[$n->data['jenis']] ?? 'forward'" class="h-4 w-4" />
                        </span>
                        <span class="min-w-0 flex-1">
                            <span class="block text-sm font-semibold text-slate-800">{{ $n->data['judul'] ?? 'Notifikasi' }}</span>
                            <span class="mt-0.5 block text-xs text-slate-500">{{ $n->data['pesan'] ?? '' }}</span>
                            <span class="mt-1 block text-[11px] text-slate-400">{{ $n->created_at?->translatedFormat('d F Y H:i') }}</span>
                        </span>
                        @unless ($n->read_at)
                            <span class="mt-1 h-2 w-2 shrink-0 rounded-full bg-gold-500" title="Belum dibaca"></span>
                        @endunless
                    </a>
                </li>
            @empty
                <li class="px-5 py-14 text-center">
                    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-slate-100 text-slate-400">
                        <x-icon name="bell" class="h-6 w-6" />
                    </span>
                    <p class="mt-3 text-sm font-semibold text-slate-600">Belum ada notifikasi</p>
                    <p class="mt-1 text-xs text-slate-500">Disposisi baru dan pengajuan tanda tangan akan muncul di sini.</p>
                </li>
            @endforelse
        </ul>

        @if ($daftar->hasPages())
            <div class="border-t border-slate-100 px-5 py-3">{{ $daftar->links() }}</div>
        @endif
    </div>
@endsection
