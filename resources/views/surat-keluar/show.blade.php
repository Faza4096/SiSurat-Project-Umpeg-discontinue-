@extends('layouts.sisurat')

@section('judul', 'Detail Surat Keluar')
@section('subjudul', 'Nomor ' . $s->no_surat)

@section('aksi')
    <a href="{{ route('surat-keluar.index') }}" class="gov-btn-outline">Kembali</a>
    @can('update', $s)
        <a href="{{ route('surat-keluar.edit', $s) }}" class="gov-btn-outline"><x-icon name="edit" class="h-4 w-4" /> Ubah</a>
    @endcan
    @can('ajukan', $s)
        <form method="POST" action="{{ route('surat-keluar.ajukan', $s) }}">
            @csrf
            <button class="gov-btn"><x-icon name="forward" class="h-4 w-4" /> Ajukan Persetujuan</button>
        </form>
    @endcan
    @if (auth()->user()->can('delete', $s))
        <form method="POST" action="{{ route('surat-keluar.destroy', $s) }}" onsubmit="return confirm('Pindahkan surat ini ke arsip terhapus?')">
            @csrf @method('DELETE')
            <button class="gov-btn-danger"><x-icon name="trash" class="h-4 w-4" /> Hapus</button>
        </form>
    @endif
@endsection

@section('isi')
    @php
        $langkah = ['draf', 'menunggu_ttd', 'disetujui', 'terkirim', 'diarsipkan'];
        $posisi = array_search($s->status, $langkah, true);
        $posisi = $posisi === false ? 0 : $posisi; // status revisi kembali ke tahap draf
    @endphp

    @if ($s->status === 'revisi' && $s->catatan_revisi)
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
            <p class="font-semibold">Dikembalikan untuk revisi</p>
            <p class="mt-1">{{ $s->catatan_revisi }}</p>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-3">
        <div class="gov-card overflow-hidden lg:col-span-2">
            <div class="border-b border-slate-100 bg-gradient-to-r from-gov-900 to-gov-700 px-6 py-6 text-white">
                <p class="text-[11px] font-semibold uppercase tracking-[0.18em] text-gold-300">Surat Keluar</p>
                <p class="mt-1 font-mono text-xl font-bold">{{ $s->no_surat }}</p>
                <h2 class="mt-2 font-display text-lg font-bold leading-snug tracking-[-0.02em]">{{ $s->hal }}</h2>
            </div>

            <div class="grid gap-2 border-b border-slate-100 px-6 py-4 sm:grid-cols-5">
                @foreach ($langkah as $i => $tahap)
                    @php $aktif = $i <= $posisi; @endphp
                    <div class="flex items-center gap-2">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-[11px] font-bold {{ $aktif ? 'bg-gov-700 text-white' : 'bg-slate-100 text-slate-400' }}">
                            @if ($i < $posisi)
                                <x-icon name="check" class="h-3.5 w-3.5" />
                            @else
                                {{ $i + 1 }}
                            @endif
                        </span>
                        <span class="text-xs font-semibold {{ $aktif ? 'text-gov-800' : 'text-slate-400' }}">{{ App\Models\SuratKeluar::STATUS[$tahap] }}</span>
                    </div>
                @endforeach
            </div>

            <dl class="grid gap-x-8 gap-y-4 p-6 sm:grid-cols-2">
                <div>
                    <dt class="gov-label">Tujuan</dt>
                    <dd class="text-sm text-slate-700">{{ $s->instansi?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="gov-label">Unit Pengirim</dt>
                    <dd class="text-sm text-slate-700">{{ $s->unit?->nama ?? '-' }}</dd>
                </div>
                <div>
                    <dt class="gov-label">Klasifikasi</dt>
                    <dd class="text-sm text-slate-700">{{ $s->klasifikasi ? $s->klasifikasi->kode . ' — ' . $s->klasifikasi->uraian : '-' }}</dd>
                </div>
                <div>
                    <dt class="gov-label">Tanggal Surat</dt>
                    <dd class="text-sm text-slate-700">{{ $s->tgl_surat?->translatedFormat('d F Y') }}</dd>
                </div>
                <div>
                    <dt class="gov-label">Sifat</dt>
                    <dd><x-badge :value="$s->sifat" type="sifat" /></dd>
                </div>
                <div>
                    <dt class="gov-label">Lampiran</dt>
                    <dd class="text-sm text-slate-700">{{ $s->lampiran_ket ?: '-' }}</dd>
                </div>
                <div>
                    <dt class="gov-label">Penandatangan</dt>
                    <dd class="text-sm text-slate-700">
                        {{ $s->penandatangan?->name ?? 'Belum ditandatangani' }}
                        @if ($s->disetujui_pada)
                            <p class="text-[11px] text-slate-400">{{ $s->disetujui_pada->translatedFormat('d M Y H:i') }}</p>
                        @endif
                    </dd>
                </div>
                <div>
                    <dt class="gov-label">Status</dt>
                    <dd><x-badge :value="$s->status" /></dd>
                </div>
                @if ($s->suratMasuk)
                    <div class="sm:col-span-2">
                        <dt class="gov-label">Balasan Dari Surat Masuk</dt>
                        <dd class="text-sm">
                            <a href="{{ route('surat-masuk.show', $s->suratMasuk) }}" class="font-semibold text-gov-700 hover:underline">
                                {{ $s->suratMasuk->no_agenda }} — {{ $s->suratMasuk->perihal }}
                            </a>
                        </dd>
                    </div>
                @endif
                <div class="sm:col-span-2">
                    <dt class="gov-label">Isi Ringkas</dt>
                    <dd class="whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $s->isi_ringkas ?: '-' }}</dd>
                </div>
            </dl>
        </div>

        <div class="space-y-4">
            @if ($s->status === 'menunggu_ttd' && auth()->user()->bolehBeriDisposisi())
                <div class="gov-card border-gold-200 p-5">
                    <p class="gov-card-title">Persetujuan Surat</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        ACC menetapkan nomor final, mengubah status menjadi disetujui, dan menerbitkan kartu
                        disposisi untuk unit Tata Usaha.
                    </p>
                    @can('acc', $s)
                        <form method="POST" action="{{ route('surat-keluar.acc', $s) }}" class="mt-3">
                            @csrf
                            <button class="gov-btn w-full"><x-icon name="check" class="h-4 w-4" /> ACC Surat</button>
                        </form>
                    @endcan
                    @can('tolak', $s)
                        <form method="POST" action="{{ route('surat-keluar.tolak', $s) }}" class="mt-3 space-y-2">
                            @csrf
                            <textarea name="catatan_revisi" rows="3" required class="gov-input text-sm"
                                      placeholder="Alasan penolakan / catatan revisi (wajib)"></textarea>
                            <button class="gov-btn-danger w-full"><x-icon name="close" class="h-4 w-4" /> Tolak / Minta Revisi</button>
                        </form>
                    @endcan
                </div>
            @endif

            @if ($s->disposisiKartu)
                <div class="gov-card p-5">
                    <p class="gov-card-title">Kartu Disposisi</p>
                    <p class="mt-1 text-xs leading-relaxed text-slate-500">
                        Diterbitkan saat ACC dan diteruskan ke {{ $s->disposisiKartu->tujuan->pluck('unit.nama')->filter()->join(', ') ?: 'unit Tata Usaha' }}.
                    </p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        <a href="{{ route('disposisi.kartu', $s->disposisiKartu) }}" target="_blank" class="gov-btn-outline !px-3 !py-1.5 text-xs">
                            <x-icon name="print" class="h-4 w-4" /> Lihat Kartu
                        </a>
                        @can('unduhKartu', $s->disposisiKartu)
                            <a href="{{ route('disposisi.pdf', $s->disposisiKartu) }}" class="gov-btn-outline !px-3 !py-1.5 text-xs">
                                <x-icon name="download" class="h-4 w-4" /> Unduh PDF
                            </a>
                        @endcan
                    </div>
                </div>
            @endif

            @if (auth()->user()->can('kirim', $s))
                <div class="gov-card p-5">
                    <p class="gov-card-title">Berkas Final &amp; Pengiriman</p>
                    <p class="mt-1 text-xs text-slate-500">Unggah surat yang sudah ditandatangani dan catat pengirimannya.</p>
                    <form method="POST" action="{{ route('surat-keluar.kirim', $s) }}" enctype="multipart/form-data" class="mt-3 space-y-2">
                        @csrf
                        <div>
                            <label class="gov-label" for="file_final">Berkas Final (PDF)</label>
                            <input id="file_final" type="file" name="file_final" accept=".pdf" required class="gov-input !p-2 text-xs">
                        </div>
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label class="gov-label" for="tgl_kirim">Tanggal Kirim</label>
                                <input id="tgl_kirim" type="date" name="tgl_kirim" value="{{ now()->toDateString() }}" required class="gov-input text-sm">
                            </div>
                            <div>
                                <label class="gov-label" for="metode_kirim">Metode</label>
                                <select id="metode_kirim" name="metode_kirim" class="gov-input text-sm">
                                    @foreach (['pos' => 'Pos', 'email' => 'Email', 'kurir' => 'Kurir', 'langsung' => 'Diserahkan langsung', 'lainnya' => 'Lainnya'] as $nilai => $label)
                                        <option value="{{ $nilai }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="gov-label" for="penerima">Penerima</label>
                            <input id="penerima" name="penerima" required class="gov-input text-sm" placeholder="Nama penerima / bagian">
                        </div>
                        <div>
                            <label class="gov-label" for="no_bukti_kirim">No. Bukti Kirim</label>
                            <input id="no_bukti_kirim" name="no_bukti_kirim" class="gov-input text-sm" placeholder="Resi / nomor agenda surat keluar">
                        </div>
                        <button class="gov-btn w-full"><x-icon name="mail-out" class="h-4 w-4" /> Simpan &amp; Tandai Terkirim</button>
                    </form>
                </div>
            @endif

            @if ($s->file_final_path || $s->penerima)
                <div class="gov-card p-5">
                    <p class="gov-card-title">Pengiriman</p>
                    <dl class="mt-3 space-y-2 text-xs">
                        @if ($s->file_final_path)
                            <div class="flex items-center justify-between gap-2">
                                <dt class="text-slate-500">Berkas final</dt>
                                <dd>
                                    <a href="{{ route('surat-keluar.file-final', $s) }}" class="font-semibold text-gov-700 hover:underline">
                                        {{ $s->file_final_nama ?? 'Unduh PDF' }}
                                    </a>
                                </dd>
                            </div>
                        @endif
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">Tanggal kirim</dt><dd class="text-slate-700">{{ $s->tgl_kirim?->translatedFormat('d M Y') ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">Penerima</dt><dd class="text-right text-slate-700">{{ $s->penerima ?? '-' }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">Metode</dt><dd class="text-slate-700">{{ ucfirst($s->metode_kirim ?? '-') }}</dd></div>
                        <div class="flex justify-between gap-2"><dt class="text-slate-500">Bukti</dt><dd class="font-mono text-slate-700">{{ $s->no_bukti_kirim ?? '-' }}</dd></div>
                        @if ($s->status === 'diarsipkan')
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Diarsipkan</dt><dd class="text-slate-700">{{ $s->diarsipkan_pada?->translatedFormat('d M Y H:i') ?? '-' }}</dd></div>
                            <div class="flex justify-between gap-2"><dt class="text-slate-500">Petugas arsip</dt><dd class="text-slate-700">{{ $s->pengarsip?->name ?? '-' }}</dd></div>
                        @endif
                    </dl>
                    @can('arsipkan', $s)
                        <form method="POST" action="{{ route('surat-keluar.arsipkan', $s) }}" class="mt-3">
                            @csrf
                            <button class="gov-btn-outline w-full"><x-icon name="document" class="h-4 w-4" /> Arsipkan Surat</button>
                        </form>
                    @endcan
                </div>
            @endif

            <div class="gov-card p-5">
                <p class="gov-card-title">Alur Persetujuan</p>
                <ol class="mt-3 space-y-2 text-xs text-slate-500">
                    <li class="flex gap-2"><span class="font-bold text-gov-700">1.</span> Draf dibuat dengan nomor otomatis.</li>
                    <li class="flex gap-2"><span class="font-bold text-gov-700">2.</span> Surat diajukan untuk persetujuan.</li>
                    <li class="flex gap-2"><span class="font-bold text-gov-700">3.</span> Disetujui atau dikembalikan untuk revisi.</li>
                    <li class="flex gap-2"><span class="font-bold text-gov-700">4.</span> Berkas final diunggah dan pengiriman dicatat.</li>
                    <li class="flex gap-2"><span class="font-bold text-gov-700">5.</span> Surat diarsipkan.</li>
                </ol>
            </div>
        </div>

        @can('hapusPermanen', $s)
            <div class="mt-2 rounded-xl border border-red-200 bg-red-50/70 p-5 lg:col-span-3">
                <p class="text-sm font-bold text-red-800">Zona berbahaya</p>
                <p class="mt-1 max-w-2xl text-xs leading-relaxed text-red-700">
                    Berbeda dengan <em>Hapus</em> yang hanya memindahkan surat ke arsip terhapus, tindakan ini
                    membuang surat beserta kartu disposisi, lampiran, dan berkas final PDF. Tidak dapat dibatalkan.
                </p>
                <a href="{{ route('surat-keluar.hapus-permanen.form', $s) }}"
                   class="mt-3 inline-flex items-center gap-2 rounded-lg bg-red-700 px-4 py-2 text-sm font-semibold text-white hover:bg-red-800">
                    <x-icon name="trash" class="h-4 w-4" /> Hapus Permanen…
                </a>
            </div>
        @endcan
    </div>
@endsection
