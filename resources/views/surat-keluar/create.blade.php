@extends('layouts.sisurat')

@section('judul', 'Buat Surat Keluar')
@section('subjudul', 'Nomor surat dibuat otomatis dari klasifikasi, urutan, dan kode unit kerja.')

@section('aksi')
    <a href="{{ route('surat-keluar.index') }}" class="gov-btn-outline">Kembali ke daftar</a>
@endsection

@section('isi')
    <form method="POST" action="{{ route('surat-keluar.store') }}">
        @csrf
        @include('surat-keluar._form', ['s' => $s, 'balasan' => $balasan])

        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
            <p class="text-xs text-slate-500">Surat disimpan sebagai <strong>draf</strong>; ajukan untuk persetujuan setelah isi lengkap.</p>
            <div class="flex gap-2">
                <a href="{{ route('surat-keluar.index') }}" class="gov-btn-outline">Batal</a>
                <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Simpan Draf</button>
            </div>
        </div>
    </form>
@endsection
