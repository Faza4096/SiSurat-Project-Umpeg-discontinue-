@extends('layouts.sisurat')

@section('judul', $judul)
@section('subjudul', 'Tindakan ini tidak dapat dibatalkan.')

@section('aksi')
    <a href="{{ $batal }}" class="gov-btn-outline">Batal</a>
@endsection

@section('isi')
    <div class="mx-auto max-w-2xl space-y-4">
        <div class="rounded-xl border border-red-200 bg-red-50/70 p-5">
            <p class="text-sm font-bold text-red-800">Surat ini akan dihapus bersama seluruh jejaknya</p>
            @isset($peringatan)
                <p class="mt-1 text-xs leading-relaxed text-red-700">{{ $peringatan }}</p>
            @endisset
            <dl class="mt-3 space-y-1.5 text-xs text-red-800">
                @foreach ($rincian as $label => $nilai)
                    <div class="flex justify-between gap-3">
                        <dt>{{ $label }}</dt>
                        <dd class="text-right font-semibold">{{ $nilai }}</dd>
                    </div>
                @endforeach
            </dl>
            <p class="mt-3 text-xs leading-relaxed text-red-700">{{ $penjelasan }}</p>
        </div>

        <form method="POST" action="{{ $aksi }}" class="gov-card p-5">
            @csrf
            @method('DELETE')

            <label for="konfirmasi" class="gov-label">
                Ketik nomor surat <span class="font-mono text-red-700">{{ $nomor }}</span> untuk melanjutkan
            </label>
            <input id="konfirmasi" name="konfirmasi" required autocomplete="off" autofocus
                   value="{{ old('konfirmasi') }}" placeholder="{{ $nomor }}"
                   class="gov-input font-mono">
            <x-input-error :messages="$errors->get('konfirmasi')" class="mt-1.5 text-sm text-red-600" />

            <div class="mt-4 flex flex-wrap justify-end gap-2">
                <a href="{{ $batal }}" class="gov-btn-outline">Batal</a>
                <button class="gov-btn-danger"><x-icon name="trash" class="h-4 w-4" /> Hapus Permanen</button>
            </div>
        </form>
    </div>
@endsection
