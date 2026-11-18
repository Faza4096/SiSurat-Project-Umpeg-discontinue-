@php
    /** @var \App\Models\Disposisi $d */
    $statusUnit = ['belum' => 'Belum ditindaklanjuti', 'diproses' => 'Sedang diproses', 'selesai' => 'Selesai'];
    $tipe = $d->suratMasuk ? 'Surat Masuk' : 'Surat Keluar';
@endphp

<style>
    .kd-sheet { width: 100%; border-collapse: collapse; font-family: "Times New Roman", Times, serif; color: #000; }
    .kd-sheet td { padding: 0; vertical-align: top; }

    .kd-kop { width: 100%; border-collapse: collapse; }
    .kd-kop td { vertical-align: middle; }
    .kd-kop-logo { width: 26mm; }
    .kd-kop-logo img { width: 22mm; height: auto; }
    .kd-kop-teks { text-align: center; }
    .kd-kop-nama { font-size: 14pt; font-weight: bold; line-height: 1.2; letter-spacing: .2px; }
    .kd-kop-alamat { font-size: 8pt; line-height: 1.35; margin-top: 1mm; }

    .kd-garis-ganda { border-top: 3px double #000; margin-top: 2.5mm; }

    .kd-judul { margin: 4mm 0 3.5mm; text-align: center; font-size: 12.5pt; font-weight: bold; letter-spacing: 1.5px; text-decoration: underline; }

    .kd-blok { width: 100%; border-collapse: collapse; border: 3px double #000; }
    .kd-blok > tbody > tr > td { border: 1px solid #000; padding: 2mm 2.5mm; }
    .kd-blok > tbody > tr > td.kd-bersih { padding: 0; }

    .kd-label { font-size: 7.5pt; font-weight: bold; letter-spacing: .6px; text-transform: uppercase; }
    .kd-nilai { font-size: 10.5pt; line-height: 1.45; margin-top: 1mm; }
    .kd-titik { border-bottom: 1px dotted #000; height: 5.5mm; font-size: 10.5pt; line-height: 5.5mm; }

    .kd-setengah { width: 50%; }

    .kd-data { width: 100%; border-collapse: collapse; }
    .kd-data td { border: 1px solid #000; padding: 1.8mm 2.5mm; vertical-align: top; }
    .kd-data td.kd-lbl { width: 30mm; font-size: 9.5pt; font-weight: bold; }
    .kd-data td.kd-val { font-size: 10.5pt; line-height: 1.45; }

    .kd-dua { width: 100%; border-collapse: collapse; }
    .kd-dua > tbody > tr > td { border: 1px solid #000; padding: 2mm 2.5mm; vertical-align: top; height: 78mm; }
    .kd-dua > tbody > tr > td.kd-kolom { width: 50%; }

    .kd-instruksi { font-size: 10.5pt; line-height: 1.55; margin-top: 1.5mm; white-space: pre-line; }

    .kd-tujuan { width: 100%; border-collapse: collapse; margin-top: 1.5mm; }
    .kd-tujuan td { border: 0; padding: 0 0 2mm 0; vertical-align: top; }
    .kd-tujuan td.kd-kotak-cell { width: 6mm; padding-top: .6mm; }
    .kd-kotak { display: inline-block; width: 3.4mm; height: 3.4mm; border: 1px solid #000; }
    .kd-unit { font-size: 10.5pt; font-weight: bold; line-height: 1.3; }
    .kd-unit-status { font-size: 8pt; font-style: italic; }

    .kd-catatan { font-size: 8pt; font-style: italic; margin-top: 2.5mm; }

    .kd-halaman { page-break-after: always; }
    .kd-halaman-terakhir { page-break-after: auto; }
</style>

<table class="kd-sheet">
    <tr>
        <td>
            {{-- Kop instansi --}}
            <table class="kd-kop">
                <tr>
                    <td class="kd-kop-logo">
                        @if (! empty($logo))
                            <img src="{{ $logo }}" alt="Lambang {{ $instansi->nama }}">
                        @endif
                    </td>
                    <td class="kd-kop-teks">
                        <div class="kd-kop-nama">{{ $instansi->namaKop() }}</div>
                        @if ($instansi->alamatLengkap())
                            <div class="kd-kop-alamat">{{ $instansi->alamatLengkap() }}</div>
                        @endif
                    </td>
                    <td class="kd-kop-logo"></td>
                </tr>
            </table>

            <div class="kd-garis-ganda"></div>
            <div class="kd-judul">KARTU DISPOSISI</div>

            {{-- Blok isian --}}
            <table class="kd-blok">
                <tr>
                    <td class="kd-setengah">
                        <div class="kd-label">Index</div>
                        <div class="kd-titik">{{ $d->indeks }}</div>
                        <div class="kd-titik">&nbsp;</div>
                    </td>
                    <td class="kd-setengah">
                        <div class="kd-label">Tanggal Penyelesaian</div>
                        <div class="kd-nilai">{{ $d->tgl_penyelesaian?->translatedFormat('d F Y') ?: '&nbsp;' }}</div>
                        <div class="kd-titik">&nbsp;</div>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="kd-bersih">
                        <table class="kd-data">
                            <tr>
                                <td class="kd-lbl">Dari</td>
                                <td class="kd-val">: {{ $d->asal() }}</td>
                            </tr>
                            <tr>
                                <td class="kd-lbl">Perihal</td>
                                <td class="kd-val">
                                    : {{ $d->perihal() }}
                                    <div class="kd-titik">&nbsp;</div>
                                </td>
                            </tr>
                            <tr>
                                <td class="kd-lbl">Tgl. Surat</td>
                                <td class="kd-val">: {{ $d->tanggalSurat()?->translatedFormat('d F Y') ?? '-' }}</td>
                            </tr>
                            <tr>
                                <td class="kd-lbl">No. Surat</td>
                                <td class="kd-val">: {{ $d->nomorSuratAsli() }}</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td colspan="2" class="kd-bersih">
                        <table class="kd-dua">
                            <tr>
                                <td class="kd-kolom">
                                    <div class="kd-label">Instruksi / Informasi *)</div>
                                    <div class="kd-instruksi">{{ $d->instruksi }}</div>
                                </td>
                                <td class="kd-kolom">
                                    <div class="kd-label">Diteruskan Kepada :</div>
                                    <table class="kd-tujuan">
                                        @forelse ($d->tujuan as $t)
                                            <tr>
                                                <td class="kd-kotak-cell"><span class="kd-kotak"></span></td>
                                                <td>
                                                    <div class="kd-unit">{{ $t->unit?->nama ?? '-' }}</div>
                                                    <div class="kd-unit-status">{{ $statusUnit[$t->status_tindak_lanjut] ?? ucfirst($t->status_tindak_lanjut) }}</div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr><td colspan="2" class="kd-instruksi">—</td></tr>
                                        @endforelse
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            <div class="kd-catatan">*) Coret yang tidak perlu.</div>
        </td>
    </tr>
</table>
