@extends('layouts.sisurat')

@section('judul', $item->exists ? 'Ubah Klasifikasi' : 'Tambah Klasifikasi')
@section('subjudul', 'Contoh kode klasifikasi: 400.7 untuk urusan kesehatan.')

@section('aksi')
    <a href="{{ route('klasifikasi.index') }}" class="gov-btn-outline">Kembali</a>
@endsection

@section('isi')
    <form method="POST" action="{{ $item->exists ? route('klasifikasi.update', $item) : route('klasifikasi.store') }}" class="gov-card max-w-2xl">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="gov-card-head"><p class="gov-card-title">Identitas Klasifikasi</p></div>
        <div class="grid gap-4 p-5 sm:grid-cols-3">
            <div>
                <label class="gov-label" for="kode">Kode</label>
                <input id="kode" name="kode" required value="{{ old('kode', $item->kode) }}" class="gov-input font-mono" placeholder="400.7">
            </div>
            <div class="sm:col-span-2">
                <label class="gov-label" for="uraian">Uraian</label>
                <input id="uraian" name="uraian" required value="{{ old('uraian', $item->uraian) }}" class="gov-input" placeholder="Kesehatan">
            </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('klasifikasi.index') }}" class="gov-btn-outline">Batal</a>
            <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Simpan</button>
        </div>
    </form>
@endsection
