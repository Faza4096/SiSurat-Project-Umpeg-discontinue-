@extends('layouts.sisurat')

@section('judul', 'Ubah Surat Keluar')
@section('subjudul', 'Nomor ' . $s->no_surat)

@section('aksi')
    <a href="{{ route('surat-keluar.show', $s) }}" class="gov-btn-outline">Kembali</a>
@endsection

@section('isi')
    <form method="POST" action="{{ route('surat-keluar.update', $s) }}">
        @csrf
        @method('PUT')
        @include('surat-keluar._form', ['s' => $s, 'balasan' => $balasan])

        <div class="mt-4 flex justify-end gap-2">
            <a href="{{ route('surat-keluar.show', $s) }}" class="gov-btn-outline">Batal</a>
            <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Simpan Perubahan</button>
        </div>
    </form>
@endsection
