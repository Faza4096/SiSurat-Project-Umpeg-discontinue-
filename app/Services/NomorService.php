<?php
namespace App\Services;
use App\Models\Penomoran;
use Illuminate\Support\Facades\DB;

class NomorService {
    private const ROMAWI = ['', 'I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];

    /** Ambil nomor urut berikutnya secara atomik (aman dari nomor ganda). */
    public static function urutBerikutnya(int $unitId, string $jenis, ?int $tahun = null): int {
        $tahun ??= (int) now()->year;
        return DB::transaction(function () use ($unitId, $jenis, $tahun) {
            $baris = Penomoran::where(['unit_id' => $unitId, 'jenis' => $jenis, 'tahun' => $tahun])
                ->lockForUpdate()->first()
                ?? Penomoran::create(['unit_id' => $unitId, 'jenis' => $jenis, 'tahun' => $tahun, 'terakhir' => 0]);
            $baris->increment('terakhir');
            return (int) $baris->fresh()->terakhir;
        });
    }

    /** Contoh: AGD/0001/XI/2026 */
    public static function agenda(int $unitId): string {
        $n = self::urutBerikutnya($unitId, 'agenda');
        return sprintf('AGD/%04d/%s/%d', $n, self::ROMAWI[now()->month], now()->year);
    }

    /** Contoh: 400.7/868-TU */
    public static function suratKeluar(int $unitId, string $kodeKlasifikasi, string $kodeUnit): string {
        $n = self::urutBerikutnya($unitId, 'keluar');
        return sprintf('%s/%d-%s', $kodeKlasifikasi, $n, $kodeUnit);
    }
}
