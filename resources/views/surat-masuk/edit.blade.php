@extends('layouts.sisurat')

@section('judul', 'Ubah Surat Masuk')
@section('subjudul', 'Perbarui data surat ' . $s->no_agenda . '. Nomor agenda tidak berubah.')

@section('aksi')
    <a href="{{ route('surat-masuk.show', $s) }}" class="gov-btn-outline">Lihat detail</a>
@endsection

@section('isi')
    <form method="POST" action="{{ route('surat-masuk.update', $s) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        @include('surat-masuk._form', ['s' => $s])

        <div class="mt-4 flex flex-wrap items-center justify-end gap-2">
            <a href="{{ route('surat-masuk.show', $s) }}" class="gov-btn-outline">Batal</a>
            <button type="submit" class="gov-btn">
                <x-icon name="check" class="h-4 w-4" /> Simpan Perubahan
            </button>
        </div>
    </form>
@endsection
