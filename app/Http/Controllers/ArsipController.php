<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\Klasifikasi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Services\PdfService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ArsipController extends Controller
{
    /** Daftar surat yang sudah diarsipkan, dipisah menurut jenis surat. */
    public function index(Request $request)
    {
        [$jenis, $query] = $this->susun($request);

        return view('arsip.index', [
            'jenis' => $jenis,
            'surat' => $query->paginate(15)->withQueryString(),
            'jumlah' => [
                'masuk' => SuratMasuk::terlihatOleh($request->user())->where('status', 'diarsipkan')->count(),
                'keluar' => SuratKeluar::terlihatOleh($request->user())->where('status', 'diarsipkan')->count(),
            ],
            'instansi' => Instansi::orderBy('nama')->get(),
            'klasifikasi' => Klasifikasi::orderBy('kode')->get(),
        ]);
    }

    /** Ekspor CSV (UTF-8, pemisah titik koma) sesuai filter aktif. */
    public function ekspor(Request $request): StreamedResponse
    {
        [$jenis, $query] = $this->susun($request);
        $data = $query->get();
        $nama = "arsip-surat-{$jenis}-" . now()->format('Ymd-His') . '.csv';

        return response()->streamDownload(function () use ($jenis, $data) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");

            $tulis = fn (array $baris) => fputcsv($out, array_map($this->selCsv(...), $baris), ';');

            if ($jenis === 'masuk') {
                $tulis(['No. Agenda', 'No. Surat', 'Tanggal Surat', 'Tanggal Diterima', 'Pengirim', 'Klasifikasi', 'Perihal', 'Sifat', 'Lokasi Arsip', 'Diarsipkan Pada', 'Petugas Arsip']);
                foreach ($data as $s) {
                    $tulis([
                        $s->no_agenda, $s->no_surat,
                        $s->tgl_surat?->format('d/m/Y'), $s->tgl_diterima?->format('d/m/Y'),
                        $s->instansi?->nama, $s->klasifikasi?->kode, $s->perihal,
                        SuratMasuk::STATUS[$s->status] ?? $s->status,
                        $s->lokasi_arsip, $s->diarsipkan_pada?->format('d/m/Y H:i'), $s->pengarsip?->name,
                    ]);
                }
            } else {
                $tulis(['No. Surat', 'Tanggal Surat', 'Tujuan', 'Unit', 'Klasifikasi', 'Hal', 'Tgl Kirim', 'Penerima', 'Metode Kirim', 'Diarsipkan Pada', 'Petugas Arsip']);
                foreach ($data as $s) {
                    $tulis([
                        $s->no_surat, $s->tgl_surat?->format('d/m/Y'),
                        $s->instansi?->nama, $s->unit?->nama, $s->klasifikasi?->kode, $s->hal,
                        $s->tgl_kirim?->format('d/m/Y'), $s->penerima, $s->metode_kirim,
                        $s->diarsipkan_pada?->format('d/m/Y H:i'), $s->pengarsip?->name,
                    ]);
                }
            }

            fclose($out);
        }, $nama, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /** Ekspor PDF (dompdf bila tersedia, selain itu halaman siap cetak). */
    public function pdf(Request $request)
    {
        [$jenis, $query] = $this->susun($request);
        $data = $query->get();

        $html = view('arsip.pdf', [
            'jenis' => $jenis,
            'data' => $data,
            'filter' => [
                'cari' => $request->query('cari'),
                'instansi' => Instansi::find($request->query('instansi_id'))?->nama,
                'klasifikasi' => Klasifikasi::find($request->query('klasifikasi_id'))?->kode,
            ],
            'pdf' => PdfService::tersedia(),
        ])->render();

        if (PdfService::tersedia()) {
            return PdfService::unduh($html, "arsip-surat-{$jenis}-" . now()->format('Ymd-His') . '.pdf');
        }

        return response($html)->header('Content-Type', 'text/html; charset=UTF-8');
    }

    /** @return array{0: string, 1: Builder} */
    private function susun(Request $request): array
    {
        $jenis = $request->query('jenis') === 'keluar' ? 'keluar' : 'masuk';
        $cari = trim((string) $request->query('cari'));

        $query = $jenis === 'masuk'
            ? SuratMasuk::with('instansi', 'klasifikasi', 'pengarsip')
                ->terlihatOleh($request->user())
                ->where('status', 'diarsipkan')
                ->latest('diarsipkan_pada')
            : SuratKeluar::with('instansi', 'klasifikasi', 'unit', 'pengarsip')
                ->terlihatOleh($request->user())
                ->where('status', 'diarsipkan')
                ->latest('diarsipkan_pada');

        if ($cari !== '') {
            $query->where(fn ($w) => $jenis === 'masuk'
                ? $w->where('perihal', 'like', "%{$cari}%")
                    ->orWhere('no_surat', 'like', "%{$cari}%")
                    ->orWhere('no_agenda', 'like', "%{$cari}%")
                    ->orWhere('lokasi_arsip', 'like', "%{$cari}%")
                : $w->where('hal', 'like', "%{$cari}%")
                    ->orWhere('no_surat', 'like', "%{$cari}%")
                    ->orWhere('penerima', 'like', "%{$cari}%"));
        }

        $query->when($request->query('instansi_id'), fn ($q, $v) => $q->where('instansi_id', $v))
            ->when($request->query('klasifikasi_id'), fn ($q, $v) => $q->where('klasifikasi_id', $v));

        return [$jenis, $query];
    }

    /**
     * Cegah CSV/formula injection: sel yang diawali pemicu formula Excel/Calc
     * diberi awalan kutip tunggal agar dibaca sebagai teks biasa.
     */
    private function selCsv(mixed $nilai): string
    {
        $teks = (string) ($nilai ?? '');

        return $teks !== '' && str_contains("=+-@\t\r", $teks[0])
            ? "'" . $teks
            : $teks;
    }
}
