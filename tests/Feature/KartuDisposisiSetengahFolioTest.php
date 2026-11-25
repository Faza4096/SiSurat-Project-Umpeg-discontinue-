<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\User;
use App\Services\KartuDisposisiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuatData;
use Tests\TestCase;

/**
 * Regresi tata letak kartu disposisi: kartu dicetak pada setengah folio
 * (16,5 × 21,5 cm), tetap satu halaman, dan tidak ada teks yang melewati area
 * cetak, termasuk saat unit tujuan banyak dan instruksi panjang.
 */
class KartuDisposisiSetengahFolioTest extends TestCase
{
    use BuatData, RefreshDatabase;

    /** Setengah folio dalam poin (165 × 215 mm). */
    private const LEBAR = 467.72;

    private const TINGGI = 609.45;

    public function test_kartu_dengan_banyak_unit_tujuan_tetap_satu_halaman_setengah_folio(): void
    {
        Storage::fake('local');

        $pimpinan = User::factory()->pimpinan()->create();

        $surat = $this->suratMasuk($pimpinan, [
            'perihal' => 'Permohonan Data Dukung Program Kesehatan Lingkungan untuk Evaluasi Triwulan IV Tahun Anggaran 2026',
        ]);

        $units = [];
        for ($i = 1; $i <= 8; $i++) {
            $units[] = $this->unit('U' . $i);
        }

        $disposisi = $this->disposisi($surat, $pimpinan, $units, [
            'instruksi' => str_repeat('Segera koordinasikan dengan bidang terkait, siapkan bahan, dan laporkan hasilnya. ', 4),
        ]);

        $pdf = $this->pdfKartu($disposisi);

        $this->assertSame(1, $this->jumlahHalaman($pdf), 'Kartu disposisi meluber ke halaman kedua.');

        [$lebar, $tinggi] = $this->ukuranHalaman($pdf);
        $this->assertEqualsWithDelta(self::LEBAR, $lebar, 1, 'Lebar halaman bukan setengah folio (16,5 cm).');
        $this->assertEqualsWithDelta(self::TINGGI, $tinggi, 1, 'Tinggi halaman bukan setengah folio (21,5 cm).');

        $this->assertIsiTidakMelewatiAreaCetak($pdf);

        // Isi tidak boleh terpotong: unit terakhir dan kalimat terakhir instruksi ikut tercetak.
        $teks = $this->teksPdf($pdf);
        $this->assertStringContainsString('Unit U8', $teks);
        $this->assertStringContainsString('laporkan hasilnya', $teks);
    }

    /**
     * Isi yang lebih panjang dari contoh di atas (perihal beberapa baris, 10 unit
     * tujuan, instruksi beberapa paragraf) harus tetap muat satu halaman setengah
     * folio dengan ukuran teks standar 10pt.
     */
    public function test_isi_lebih_panjang_tetap_satu_halaman_setengah_folio(): void
    {
        Storage::fake('local');

        $pimpinan = User::factory()->pimpinan()->create();

        $surat = $this->suratMasuk($pimpinan, [
            'perihal' => 'Permohonan Data Dukung Program Kesehatan Lingkungan untuk Evaluasi Triwulan IV Tahun Anggaran 2026 beserta Rekapitulasi Capaian Kinerja dan Rencana Tindak Lanjut Setiap Puskesmas',
        ]);

        $units = [];
        for ($i = 1; $i <= 10; $i++) {
            $units[] = $this->unit('Bidang Kesehatan ' . $i);
        }

        $disposisi = $this->disposisi($surat, $pimpinan, $units, [
            'instruksi' => str_repeat('Segera koordinasikan dengan bidang terkait, siapkan bahan dukung, jadwalkan rapat, dan laporkan hasilnya. ', 6),
        ]);

        $pdf = $this->pdfKartu($disposisi);

        $this->assertSame(1, $this->jumlahHalaman($pdf), 'Kartu disposisi meluber ke halaman kedua.');
        $this->assertIsiTidakMelewatiAreaCetak($pdf);

        // Masih ada sisa ruang aman di bawah kartu, bukan sekadar pas di batas halaman.
        $this->assertGreaterThanOrEqual(
            10 * 72 / 25.4 + 24,
            $this->posisiTeksTerbawah($pdf),
            'Sisa ruang aman di bawah kartu kurang dari 8 mm.'
        );

        $teks = $this->teksPdf($pdf);
        $this->assertStringContainsString('Unit Bidang Kesehatan 10', $teks);
        $this->assertStringContainsString('laporkan hasilnya', $teks);
    }

    private function pdfKartu(Disposisi $disposisi): string
    {
        app(KartuDisposisiService::class)->simpan($disposisi);
        $disposisi->refresh();

        return Storage::disk('local')->get($disposisi->kartu_path);
    }

    private function jumlahHalaman(string $pdf): int
    {
        preg_match_all('/\/Count\s+(\d+)/', $pdf, $cocok);

        return $cocok[1] === [] ? 0 : max(array_map('intval', $cocok[1]));
    }

    /** @return array{0: float, 1: float} */
    private function ukuranHalaman(string $pdf): array
    {
        preg_match('/\/MediaBox\s*\[\s*0(?:\.0+)?\s+0(?:\.0+)?\s+([\d.]+)\s+([\d.]+)\s*\]/', $pdf, $cocok);

        return [(float) ($cocok[1] ?? 0), (float) ($cocok[2] ?? 0)];
    }

    /**
     * Setiap baris teks digambar dari titik (x, y) pada koordinat halaman.
     * Titik itu harus berada di dalam area cetak: margin 10 mm di semua sisi,
     * dengan toleransi 2 poin untuk pembulatan dompdf.
     */
    private function assertIsiTidakMelewatiAreaCetak(string $pdf): void
    {
        $marginX = 10 * 72 / 25.4;
        $marginY = 10 * 72 / 25.4;
        $toleransi = 2.0;

        preg_match_all('/BT\s+(-?[\d.]+)\s+(-?[\d.]+)\s+Td/', $this->teksPdf($pdf), $cocok, PREG_SET_ORDER);

        $this->assertNotEmpty($cocok, 'Tidak ada teks yang terbaca dari PDF kartu.');

        foreach ($cocok as $baris) {
            $x = (float) $baris[1];
            $y = (float) $baris[2];

            $this->assertGreaterThanOrEqual(
                $marginX - $toleransi,
                $x,
                'Teks melewati margin kiri pada baris: ' . $baris[0]
            );

            $this->assertLessThanOrEqual(
                self::LEBAR - $marginX + $toleransi,
                $x,
                'Teks melewati margin kanan pada baris: ' . $baris[0]
            );

            $this->assertGreaterThanOrEqual(
                $marginY - $toleransi,
                $y,
                'Teks melewati margin bawah pada baris: ' . $baris[0]
            );

            $this->assertLessThanOrEqual(
                self::TINGGI - $marginY + $toleransi,
                $y,
                'Teks melewati margin atas pada baris: ' . $baris[0]
            );
        }
    }

    /** Posisi vertikal teks paling bawah (diukur dari tepi bawah halaman). */
    private function posisiTeksTerbawah(string $pdf): float
    {
        preg_match_all('/BT\s+(-?[\d.]+)\s+(-?[\d.]+)\s+Td/', $this->teksPdf($pdf), $cocok, PREG_SET_ORDER);

        return min(array_map(fn ($baris) => (float) $baris[2], $cocok));
    }

    /** Isi stream PDF yang sudah didekompres (operator gambar teks). */
    private function teksPdf(string $pdf): string
    {
        if (! preg_match_all('/stream\r?\n(.*?)endstream/s', $pdf, $cocok)) {
            return '';
        }

        $teks = '';

        foreach ($cocok[1] as $blob) {
            $isi = @gzuncompress($blob);

            if ($isi !== false) {
                $teks .= $isi . "\n";
            }
        }

        return $teks;
    }
}
