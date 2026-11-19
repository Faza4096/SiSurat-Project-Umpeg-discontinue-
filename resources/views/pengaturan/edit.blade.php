@extends('layouts.sisurat')

@section('judul', 'Pengaturan Instansi')
@section('subjudul', 'Nama, unit, alamat, dan lambang untuk aplikasi dan kop kartu disposisi.')

@section('isi')
    <form method="POST" action="{{ route('pengaturan.update') }}" enctype="multipart/form-data" class="grid items-start gap-4 lg:grid-cols-3">
        @csrf
        @method('PUT')

        <div class="gov-card lg:col-span-2">
            <div class="gov-card-head">
                <p class="gov-card-title">Identitas Instansi</p>
                <p class="text-[11.5px] text-slate-500">Satu sumber identitas untuk seluruh halaman</p>
            </div>

            <div class="grid gap-4 px-5 py-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="gov-label" for="nama">Nama Instansi (kop)</label>
                    <input id="nama" name="nama" required value="{{ old('nama', $p->nama) }}" class="gov-input" placeholder="PEMERINTAH KABUPATEN BOGOR">
                    <p class="gov-help">Dicetak dengan huruf kapital pada kop kartu.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="gov-label" for="unit">Unit Kerja</label>
                    <input id="unit" name="unit" value="{{ old('unit', $p->unit) }}" class="gov-input" placeholder="Kecamatan Ciomas">
                    <p class="gov-help">Tampil di sidebar, footer, halaman masuk, dan judul PDF laporan.</p>
                </div>
                <div class="sm:col-span-2">
                    <label class="gov-label" for="alamat">Alamat</label>
                    <input id="alamat" name="alamat" value="{{ old('alamat', $p->alamat) }}" class="gov-input">
                </div>
                <div>
                    <label class="gov-label" for="telepon">Telepon</label>
                    <input id="telepon" name="telepon" value="{{ old('telepon', $p->telepon) }}" class="gov-input">
                </div>
                <div>
                    <label class="gov-label" for="email">Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $p->email) }}" class="gov-input">
                </div>
                <div>
                    <label class="gov-label" for="website">Situs</label>
                    <input id="website" name="website" value="{{ old('website', $p->website) }}" class="gov-input">
                </div>
                <div>
                    <label class="gov-label" for="pejabat_jabatan">Jabatan Penanda Tangan</label>
                    <input id="pejabat_jabatan" name="pejabat_jabatan" value="{{ old('pejabat_jabatan', $p->pejabat_jabatan) }}" class="gov-input">
                </div>
                <div class="sm:col-span-2">
                    <label class="gov-label" for="pejabat_nama">Nama Penanda Tangan (opsional)</label>
                    <input id="pejabat_nama" name="pejabat_nama" value="{{ old('pejabat_nama', $p->pejabat_nama) }}" class="gov-input">
                </div>
            </div>

            <div class="flex justify-end border-t border-slate-100 px-5 py-3">
                <button class="gov-btn"><x-icon name="check" class="h-4 w-4" /> Simpan Pengaturan</button>
            </div>
        </div>

        <div class="space-y-4">
            <div class="gov-card">
                <div class="gov-card-head">
                    <p class="gov-card-title">Lambang</p>
                </div>
                <div class="p-5">
                    <div class="flex h-32 items-center justify-center border border-dashed border-slate-300 bg-slate-50">
                        @if ($p->logoDataUri())
                            <img src="{{ $p->logoDataUri() }}" alt="Lambang instansi" class="max-h-28 max-w-full object-contain">
                        @else
                            <span class="text-xs text-slate-400">Belum ada lambang</span>
                        @endif
                    </div>
                    <label class="gov-label mt-4" for="logo">Ganti Lambang</label>
                    <input id="logo" type="file" name="logo" accept=".png,.jpg,.jpeg,.webp" class="gov-input !p-2 text-xs">
                    <p class="gov-help">PNG/JPG/SVG, maksimal 2 MB. Disarankan latar transparan.</p>
                </div>
            </div>

            <div class="gov-card p-5 text-[11.5px] leading-relaxed text-slate-500">
                Dipakai pada kop kartu disposisi, sidebar, footer, halaman masuk, dan judul PDF laporan. Pratinjau kartu ada di
                <a href="{{ route('disposisi.index') }}" class="gov-link">menu Disposisi</a>.
            </div>
        </div>
    </form>
@endsection
