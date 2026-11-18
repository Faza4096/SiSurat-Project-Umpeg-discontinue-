@extends('layouts.sisurat')

@section('judul', $item->exists ? 'Ubah Instansi' : 'Tambah Instansi')
@section('subjudul', 'Instansi dipakai sebagai pengirim surat masuk dan tujuan surat keluar.')

@section('aksi')
    <a href="{{ route('instansi.index') }}" class="gov-btn-outline">Kembali</a>
@endsection

@section('isi')
    <form method="POST" action="{{ $item->exists ? route('instansi.update', $item) : route('instansi.store') }}" class="gov-card max-w-2xl">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="gov-card-head"><p class="gov-card-title">Identitas Instansi</p></div>
        <div class="grid gap-4 p-5 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="gov-label" for="nama">Nama Instansi</label>
                <input id="nama" name="nama" required value="{{ old('nama', $item->nama) }}" class="gov-input" placeholder="Puskesmas Ciapus">
            </div>
            <div class="sm:col-span-2">
                <label class="gov-label" for="alamat">Alamat</label>
                <input id="alamat" name="alamat" value="{{ old('alamat', $item->alamat) }}" class="gov-input" placeholder="Jl. Raya Sukamakmur, Kec. Ciomas">
            </div>
            <div>
                <label class="gov-label" for="email">Email</label>
                <input id="email" type="email" name="email" value="{{ old('email', $item->email) }}" class="gov-input" placeholder="instansi@contoh.go.id">
            </div>
            <div>
                <label class="gov-label" for="telepon">Telepon</label>
                <input id="telepon" name="telepon" value="{{ old('telepon', $item->telepon) }}" class="gov-input" placeholder="0251-xxxxxxx">
            </div>
        </div>
        <div class="flex justify-end gap-2 border-t border-slate-100 px-5 py-4">
            <a href="{{ route('instansi.index') }}" class="gov-btn-outline">Batal</a>
            <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Simpan</button>
        </div>
    </form>
@endsection
