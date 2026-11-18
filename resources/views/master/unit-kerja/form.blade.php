@extends('layouts.sisurat')

@section('judul', $item->exists ? 'Ubah Unit Kerja' : 'Tambah Unit Kerja')
@section('subjudul', 'Kode unit dipakai pada penomoran surat keluar, contoh 400.7/868-TU.')

@section('aksi')
    <a href="{{ route('unit-kerja.index') }}" class="gov-btn-outline">Kembali</a>
@endsection

@section('isi')
    <form method="POST" action="{{ $item->exists ? route('unit-kerja.update', $item) : route('unit-kerja.store') }}" class="gov-card max-w-2xl">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="gov-card-head"><p class="gov-card-title">Identitas Unit Kerja</p></div>
        <div class="grid gap-4 p-5 sm:grid-cols-3">
            <div>
                <label class="gov-label" for="kode">Kode</label>
                <input id="kode" name="kode" required value="{{ old('kode', $item->kode) }}" class="gov-input font-mono" placeholder="TU">
            </div>
            <div class="sm:col-span-2">
                <label class="gov-label" for="nama">Nama Unit</label>
                <input id="nama" name="nama" required value="{{ old('nama', $item->nama) }}" class="gov-input" placeholder="Tata Usaha">
            </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('unit-kerja.index') }}" class="gov-btn-outline">Batal</a>
            <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Simpan</button>
        </div>
    </form>
@endsection
