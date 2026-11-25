<?php

namespace App\Services;

use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use App\Notifications\DisposisiBaru;
use App\Notifications\PengingatBatasWaktu;
use App\Notifications\StatusTindakLanjut;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Job notifikasi surat rahasia yang penerimanya tidak berhak membuka surat
 * (di luar unit tujuan disposisi) masih memuat nomor/perihal rahasia.
 * Pembersih ini membatalkan job tersebut sebelum sempat dikirim, termasuk
 * job yang sudah gagal dan tersimpan di tabel failed_jobs.
 */
class PembersihAntreanRahasia
{
    /** Tabel antrean yang menyimpan payload job notifikasi. */
    private const TABEL = ['jobs', 'failed_jobs'];

    /** @return int jumlah job yang dihapus */
    public function jalankan(): int
    {
        $dihapus = 0;

        foreach (self::TABEL as $tabel) {
            // Driver antrean lain mungkin tidak punya tabel failed_jobs.
            if (Schema::hasTable($tabel)) {
                $dihapus += $this->bersihkan($tabel);
            }
        }

        return $dihapus;
    }

    private function bersihkan(string $tabel): int
    {
        $dihapus = 0;

        DB::table($tabel)->select('id', 'payload')->chunkById(200, function ($baris) use (&$dihapus, $tabel) {
            $ids = $baris->filter(fn ($job) => $this->bocor($job->payload))->pluck('id');

            if ($ids->isNotEmpty()) {
                DB::table($tabel)->whereIn('id', $ids)->delete();
                $dihapus += $ids->count();
            }
        });

        return $dihapus;
    }

    private function bocor(?string $payload): bool
    {
        $perintah = json_decode((string) $payload, true)['data']['command'] ?? null;

        // Batasi unserialize pada payload notifikasi yang memang dikenal.
        if (! is_string($perintah) || ! str_contains($perintah, SendQueuedNotifications::class)) {
            return false;
        }

        try {
            $job = unserialize($perintah);
        } catch (\Throwable) {
            return false;
        }

        if (! $job instanceof SendQueuedNotifications) {
            return false;
        }

        $surat = $this->suratRahasia($job->notification);

        if (! $surat) {
            return false;
        }

        return collect($job->notifiables)->contains(
            fn ($penerima) => ! $surat->bolehDilihatOleh($penerima instanceof User ? $penerima : null)
        );
    }

    private function suratRahasia(object $notifikasi): SuratMasuk|SuratKeluar|null
    {
        $disposisi = match (true) {
            $notifikasi instanceof DisposisiBaru => $notifikasi->disposisi,
            $notifikasi instanceof StatusTindakLanjut,
            $notifikasi instanceof PengingatBatasWaktu => $notifikasi->tujuan?->disposisi,
            default => null,
        };

        $surat = $disposisi?->suratMasuk ?? $disposisi?->suratKeluar;

        return $surat?->isRahasia() ? $surat : null;
    }
}
