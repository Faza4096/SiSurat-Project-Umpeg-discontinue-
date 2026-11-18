@extends('layouts.sisurat')

@section('judul', 'Profil Saya')
@section('subjudul', 'Perbarui informasi akun dan kata sandi Anda.')

@section('isi')
    <div class="grid gap-4 lg:grid-cols-3">
        <div class="space-y-4 lg:col-span-2">
            <div class="gov-card p-5 sm:p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="gov-card p-5 sm:p-6">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="gov-card p-5">
                <p class="gov-card-title">Informasi Akun</p>
                <dl class="mt-3 space-y-2 text-xs">
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Nama</dt><dd class="text-right font-semibold text-slate-700">{{ auth()->user()->name }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">NIP</dt><dd class="text-right font-mono text-slate-700">{{ auth()->user()->nip ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Jabatan</dt><dd class="text-right text-slate-700">{{ auth()->user()->jabatan ?: '-' }}</dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Peran</dt><dd class="text-right"><x-badge :value="auth()->user()->role?->nama" type="role" /></dd></div>
                    <div class="flex justify-between gap-3"><dt class="text-slate-500">Unit</dt><dd class="text-right text-slate-700">{{ auth()->user()->unit?->nama ?? '-' }}</dd></div>
                </dl>
            </div>

            <div class="gov-card p-5">
                <p class="gov-card-title">Penghapusan Akun</p>
                <p class="mt-2 text-xs leading-relaxed text-slate-500">
                    Akun pegawai tidak dapat dihapus sendiri agar jejak surat dan disposisi tetap utuh.
                    Hubungi administrator bila akun perlu dinonaktifkan.
                </p>
            </div>
        </div>
    </div>
@endsection
