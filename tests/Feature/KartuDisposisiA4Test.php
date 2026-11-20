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
 * Regresi tata letak kartu disposisi: kartu harus tetap satu halaman A4 dan
 * tidak ada teks yang melewati area cetak, termasuk saat unit tujuan banyak
 * dan instruksi panjang.
 */
class KartuDisposisiA4Test extends TestCase
{
    use BuatData, RefreshDatabase;

    /** Ukuran A4 dalam poin (210 × 297 mm). */
    private const LEBAR_A4 = 595.28;

    private const TINGGI_A4 = 841.89;

    public function test_kartu_dengan_banyak_unit_tujuan_tetap_satu_halaman_a4(): void
    {
        Storage::fake('local');

        $pimpinan = User::factory()->staf()->create();

        $surat = $this->suratMasuk($pimpinan, [
            'perihal' => 'Permohonan Data Dukung Program Kesehatan Lingkungan untuk Evaluasi Triwulan IV Tahun Anggaran 2026',
        ]);

        $units = [];
        for ($i = 1; $i <= 8; $i++) {
            $units[] = $this->unit('U' . $i);
        }

        $disposisi = $this->disposisi($surat, $pimpinan, $units, [
            'instruksi' => str_repeat('Segera koordinasikan dengan bidang terkait, siapkan bahan, dan laporkan hasilnya. ', 10),
        ]);

        $pdf = $this->pdfKartu($disposisi);

        $this->assertSame(1, $this->jumlahHalaman($pdf), 'Kartu disposisi meluber ke halaman kedua.');

        [$lebar, $tinggi] = $this->ukuranHalaman($pdf);
        $this->assertEqualsWithDelta(self::LEBAR_A4, $lebar, 1, 'Lebar halaman bukan A4.');
        $this->assertEqualsWithDelta(self::TINGGI_A4, $tinggi, 1, 'Tinggi halaman bukan A4.');

        $this->assertIsiTidakMelewatiAreaCetak($pdf);

        // Isi tidak boleh terpotong: unit terakhir dan kalimat terakhir instruksi ikut tercetak.
        $teks = $this->teksPdf($pdf);
        $this->assertStringContainsString('Unit U8', $teks);
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
     * Titik itu harus berada di dalam area cetak: margin 15 mm kiri/kanan dan
     * 14 mm atas/bawah, dengan toleransi 2 poin untuk pembulatan dompdf.
     */
    private function assertIsiTidakMelewatiAreaCetak(string $pdf): void
    {
        $marginX = 15 * 72 / 25.4;
        $marginY = 14 * 72 / 25.4;
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
                self::LEBAR_A4 - $marginX + $toleransi,
                $x,
                'Teks melewati margin kanan pada baris: ' . $baris[0]
            );

            $this->assertGreaterThanOrEqual(
                $marginY - $toleransi,
                $y,
                'Teks melewati margin bawah pada baris: ' . $baris[0]
            );

            $this->assertLessThanOrEqual(
                self::TINGGI_A4 - $marginY + $toleransi,
                $y,
                'Teks melewati margin atas pada baris: ' . $baris[0]
            );
        }
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
