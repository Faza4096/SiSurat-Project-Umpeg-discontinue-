@extends('layouts.sisurat')

@section('judul', 'Catat Surat Masuk')
@section('subjudul', 'Nomor agenda dibuat otomatis saat surat disimpan.')

@section('aksi')
    <a href="{{ route('surat-masuk.index') }}" class="gov-btn-outline">Kembali ke daftar</a>
@endsection

@section('isi')
    <form method="POST" action="{{ route('surat-masuk.store') }}" enctype="multipart/form-data">
        @csrf
        @include('surat-masuk._form', ['s' => $s])

        <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
            <a href="{{ route('surat-masuk.index') }}" class="gov-btn-outline">Batal</a>
            <button type="submit" class="gov-btn">
                <x-icon name="check" class="h-4 w-4" /> Simpan &amp; Buat Nomor Agenda
            </button>
        </div>
    </form>
@endsection
