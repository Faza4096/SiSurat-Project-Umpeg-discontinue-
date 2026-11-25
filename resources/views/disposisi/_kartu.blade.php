@php
    /** @var \App\Models\Disposisi $d */
@endphp

<style>
    .kd-sheet { width: 100%; font-family: "Times New Roman", Times, serif; font-size: 10.5pt; color: #000; }

    /* Kop instansi: lambang di kiri, nama daerah lalu judul kartu di tengah */
    .kd-kop { width: 100%; border-collapse: collapse; }
    .kd-kop td { vertical-align: middle; }
    .kd-kop-logo { width: 17mm; }
    .kd-kop-logo img { width: 13mm; height: auto; }
    .kd-kop-spacer { width: 8mm; }
    .kd-kop-teks { text-align: center; }
    .kd-kop-nama { font-size: 16pt; font-weight: bold; line-height: 1.05; }
    .kd-kop-judul { font-size: 12.5pt; font-weight: bold; line-height: 1.1; margin-top: .8mm; }

    /* Garis ganda dari dua garis 1px berjarak tetap agar sama di peramban dan dompdf. */
    .kd-garis-ganda { border-top: 1px solid #000; margin-top: 1.6mm; }
    .kd-garis-ganda > div { height: .7mm; border-bottom: 1px solid #000; }

    /* Baris INDEX | TANGGAL PENYELESAIAN, dipisah garis tegak */
    .kd-atas { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .kd-atas > tbody > tr > td { width: 50%; vertical-align: top; padding: 1.4mm 2mm 1.2mm; border-bottom: 1px solid #000; }
    .kd-atas > tbody > tr > td.kd-atas-kiri { border-right: 1px solid #000; }

    /* Label + titik dua + isi bergaris titik-titik */
    .kd-baris { width: 100%; border-collapse: collapse; }
    .kd-baris td { padding: 0; vertical-align: bottom; }
    .kd-baris td.kd-lbl { width: 64pt; white-space: nowrap; }
    .kd-baris td.kd-titik-dua { width: 1%; white-space: nowrap; padding: 0 1.6mm; }

    .kd-lbl { font-size: 10pt; line-height: 5.8mm; }
    .kd-titik-dua { font-size: 10pt; line-height: 5.8mm; }
    .kd-val { width: 100%; font-size: 10pt; line-height: 5.8mm; border-bottom: 1px dotted #000; word-wrap: break-word; }
    .kd-garis-kosong { height: 5.8mm; border-bottom: 1px dotted #000; margin-top: 1.4mm; }
    .kd-tanggal { margin-top: 1.4mm; }

    /* Empat baris data surat */
    .kd-data { width: 100%; border-collapse: collapse; table-layout: fixed; }
    .kd-data td { padding: .3mm 0; vertical-align: bottom; }
    .kd-data td.kd-lbl-col { width: 15%; font-size: 10pt; line-height: 6.4mm; }
    .kd-data td.kd-titik-col { width: 3%; font-size: 10pt; line-height: 6.4mm; }
    .kd-data td.kd-val-col { width: 82%; font-size: 10pt; line-height: 6.4mm; border-bottom: 1px dotted #000; word-wrap: break-word; }

    /* Dua kolom: instruksi dan tujuan disposisi */
    .kd-dua { width: 100%; border-collapse: collapse; table-layout: fixed; border-bottom: 1px solid #000; }
    .kd-dua > tbody > tr > td { padding: 1.6mm; vertical-align: top; height: 64mm; }
    .kd-dua > tbody > tr > td.kd-kiri { width: 62.5%; border-right: 1px solid #000; }
    .kd-dua > tbody > tr > td.kd-kanan { width: 37.5%; }

    .kd-instruksi { font-size: 10pt; line-height: 1.3; margin-top: 1.2mm; white-space: pre-line; overflow-wrap: break-word; }

    .kd-tujuan { width: 100%; border-collapse: collapse; margin-top: 1.4mm; }
    .kd-tujuan td { border: 0; padding: 0 0 .2mm 0; vertical-align: top; }
    .kd-tujuan td.kd-kotak-cell { width: 5mm; padding-top: .6mm; }
    .kd-kotak { display: inline-block; width: 3mm; height: 3mm; border: 1px solid #000; }
    .kd-unit { font-size: 10pt; line-height: 1.3; }

    .kd-catatan { font-size: 7.5pt; font-style: italic; margin-top: 1.2mm; }

    .kd-halaman { page-break-after: always; }
    .kd-halaman-terakhir { page-break-after: auto; }
</style>

<div class="kd-sheet">
    <table class="kd-kop">
        <tr>
            <td class="kd-kop-logo">
                @if (! empty($logo))
                    <img src="{{ $logo }}" alt="Lambang {{ $instansi->nama }}">
                @endif
            </td>
            <td class="kd-kop-teks">
                <div class="kd-kop-nama">{{ $instansi->namaKop() }}</div>
                <div class="kd-kop-judul">KARTU DISPOSISI</div>
            </td>
            <td class="kd-kop-spacer"></td>
        </tr>
    </table>

    <div class="kd-garis-ganda"><div></div></div>

    <table class="kd-atas">
        <tr>
            <td class="kd-atas-kiri">
                <table class="kd-baris">
                    <tr>
                        <td class="kd-lbl">INDEX</td>
                        <td class="kd-titik-dua">:</td>
                        <td class="kd-val">{{ $d->indeks }}</td>
                    </tr>
                </table>
                <div class="kd-garis-kosong"></div>
            </td>
            <td>
                <div class="kd-lbl">TANGGAL PENYELESAIAN</div>
                <div class="kd-val kd-tanggal">{{ $d->tgl_penyelesaian?->translatedFormat('d F Y') }}</div>
            </td>
        </tr>
    </table>

    <table class="kd-data">
        <tr>
            <td class="kd-lbl-col">Dari</td>
            <td class="kd-titik-col">:</td>
            <td class="kd-val-col">{{ $d->asal() }}</td>
        </tr>
        <tr>
            <td class="kd-lbl-col">Perihal</td>
            <td class="kd-titik-col">:</td>
            <td class="kd-val-col">{{ $d->perihal() }}</td>
        </tr>
        <tr>
            <td class="kd-lbl-col"></td>
            <td class="kd-titik-col"></td>
            <td class="kd-val-col">&nbsp;</td>
        </tr>
        <tr>
            <td class="kd-lbl-col">Tgl. Surat</td>
            <td class="kd-titik-col">:</td>
            <td class="kd-val-col">{{ $d->tanggalSurat()?->translatedFormat('d F Y') }}</td>
        </tr>
        <tr>
            <td class="kd-lbl-col">No. Surat</td>
            <td class="kd-titik-col">:</td>
            <td class="kd-val-col">{{ $d->nomorSuratAsli() }}</td>
        </tr>
    </table>

    <div class="kd-garis-ganda"><div></div></div>

    <table class="kd-dua">
        <tr>
            <td class="kd-kiri">
                <div class="kd-lbl">INSTRUKSI / INFORMASI *)</div>
                <div class="kd-instruksi">{{ $d->instruksi }}</div>
            </td>
            <td class="kd-kanan">
                <div class="kd-lbl">DITERUSKAN KEPADA:</div>
                <table class="kd-tujuan">
                    @forelse ($d->tujuan as $t)
                        <tr>
                            <td class="kd-kotak-cell"><span class="kd-kotak"></span></td>
                            <td>
                                <div class="kd-unit">{{ $t->unit?->nama ?? '-' }}</div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="2" class="kd-unit">—</td></tr>
                    @endforelse
                </table>
            </td>
        </tr>
    </table>

    <div class="kd-catatan">*) Coret yang tidak perlu.</div>
</div>
