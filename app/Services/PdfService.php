<?php

namespace App\Services;

/**
 * Pembungkus tipis dompdf. Bila paket belum terpasang, laporan tetap bisa
 * dicetak lewat halaman HTML siap cetak (Save as PDF di browser).
 */
class PdfService
{
    public static function tersedia(): bool
    {
        return class_exists(\Dompdf\Dompdf::class);
    }

    public static function unduh(string $html, string $nama)
    {
        abort_unless(self::tersedia(), 500, 'Pembuat PDF belum terpasang.');

        $dompdf = new \Dompdf\Dompdf([
            // HTML laporan dirender lokal; sumber daya remote tidak diperlukan dan
            // akan membuat dompdf bisa dipakai untuk SSRF bila ada injeksi HTML.
            'isRemoteEnabled' => false,
            'defaultFont' => 'Times New Roman',
        ]);
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'landscape');
        $dompdf->render();

        return response($dompdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $nama . '"',
        ]);
    }
}
