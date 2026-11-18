@extends('layouts.sisurat')

@section('judul', $item->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')
@section('subjudul', 'Peran menentukan hak akses pada alur surat dan disposisi.')

@section('aksi')
    <a href="{{ route('pengguna.index') }}" class="gov-btn-outline">Kembali</a>
@endsection

@section('isi')
    <form method="POST" action="{{ $item->exists ? route('pengguna.update', $item) : route('pengguna.store') }}"
          class="grid gap-4 lg:grid-cols-3">
        @csrf
        @if ($item->exists) @method('PUT') @endif

        <div class="gov-card lg:col-span-2">
            <div class="gov-card-head"><p class="gov-card-title">Data Aparatur</p></div>
            <div class="grid gap-4 p-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="gov-label" for="name">Nama Lengkap</label>
                    <input id="name" name="name" required value="{{ old('name', $item->name) }}" class="gov-input" placeholder="Nama beserta gelar">
                </div>
                <div>
                    <label class="gov-label" for="nip">NIP</label>
                    <input id="nip" name="nip" value="{{ old('nip', $item->nip) }}" class="gov-input font-mono" placeholder="19800101 200501 1 001">
                </div>
                <div>
                    <label class="gov-label" for="jabatan">Jabatan</label>
                    <input id="jabatan" name="jabatan" value="{{ old('jabatan', $item->jabatan) }}" class="gov-input" placeholder="Kepala Subbagian Umum">
                </div>
                <div class="sm:col-span-2">
                    <label class="gov-label" for="email">Email Dinas</label>
                    <input id="email" type="email" name="email" required value="{{ old('email', $item->email) }}" class="gov-input" placeholder="nama@bogorkab.go.id">
                </div>
                <div>
                    <label class="gov-label" for="role_id">Peran</label>
                    <select id="role_id" name="role_id" required class="gov-input">
                        @foreach ($roles as $role)
                            <option value="{{ $role->id }}" @selected(old('role_id', $item->role_id) == $role->id)>{{ ucfirst(str_replace('_', ' ', $role->nama)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="gov-label" for="unit_id">Unit Kerja</label>
                    <select id="unit_id" name="unit_id" class="gov-input">
                        <option value="">— Tanpa unit —</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit->id }}" @selected(old('unit_id', $item->unit_id) == $unit->id)>{{ $unit->nama }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="sm:col-span-2">
                    <label class="gov-label" for="password">{{ $item->exists ? 'Kata Sandi Baru' : 'Kata Sandi' }}</label>
                    <input id="password" type="password" name="password" autocomplete="new-password" class="gov-input"
                           placeholder="{{ $item->exists ? 'Biarkan kosong bila tidak diubah' : 'Minimal 10 karakter, huruf dan angka' }}" @required(! $item->exists)>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div class="gov-card p-5">
                <p class="gov-card-title">Hak Akses</p>
                <ul class="mt-2 space-y-2 text-xs leading-relaxed text-slate-500">
                    <li><span class="font-semibold text-slate-700">Admin</span> — kelola pengguna, unit, klasifikasi, instansi, pengaturan, dan log aktivitas.</li>
                    <li><span class="font-semibold text-slate-700">Staf</span> — catat surat masuk & surat keluar, ACC & disposisi, setujui surat, kirim, dan arsipkan.</li>
                </ul>
            </div>
        </div>

        <div class="flex justify-end gap-2 lg:col-span-3">
            <a href="{{ route('pengguna.index') }}" class="gov-btn-outline">Batal</a>
            <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Simpan</button>
        </div>
    </form>
@endsection
