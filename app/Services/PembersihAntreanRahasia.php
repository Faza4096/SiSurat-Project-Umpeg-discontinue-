<?php

namespace App\Services;

use App\Models\Disposisi;
use App\Notifications\DisposisiBaru;
use App\Notifications\PengingatBatasWaktu;
use App\Notifications\StatusTindakLanjut;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\DB;

/**
 * Job notifikasi yang masuk antrean sebelum aturan penerima surat rahasia
 * diperketat masih memuat nomor/perihal rahasia untuk akun non-operator.
 * Pembersih ini membatalkan job tersebut sebelum sempat dikirim.
 */
class PembersihAntreanRahasia
{
    /** @return int jumlah job yang dihapus */
    public function jalankan(): int
    {
        $dihapus = 0;

        DB::table('jobs')->select('id', 'payload')->chunkById(200, function ($baris) use (&$dihapus) {
            $ids = $baris->filter(fn ($job) => $this->bocor($job->payload))->pluck('id');

            if ($ids->isNotEmpty()) {
                DB::table('jobs')->whereIn('id', $ids)->delete();
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

        if (! $job instanceof SendQueuedNotifications || ! $this->notifikasiRahasia($job->notification)) {
            return false;
        }

        return collect($job->notifiables)->contains(fn ($penerima) => $this->nonOperator($penerima));
    }

    private function notifikasiRahasia(object $notifikasi): bool
    {
        $disposisiId = match (true) {
            $notifikasi instanceof DisposisiBaru => $notifikasi->disposisi?->id,
            $notifikasi instanceof StatusTindakLanjut,
            $notifikasi instanceof PengingatBatasWaktu => $notifikasi->tujuan?->disposisi_id,
            default => null,
        };

        if (! $disposisiId) {
            return false;
        }

        return DB::table('disposisi')
            ->leftJoin('surat_masuk', 'surat_masuk.id', '=', 'disposisi.surat_masuk_id')
            ->leftJoin('surat_keluar', 'surat_keluar.id', '=', 'disposisi.surat_keluar_id')
            ->where('disposisi.id', $disposisiId)
            ->where(fn ($q) => $q->where('surat_masuk.sifat', 'rahasia')
                ->orWhere('surat_keluar.sifat', 'rahasia'))
            ->exists();
    }

    private function nonOperator(object $penerima): bool
    {
        $id = $penerima->id ?? null;

        if (! $id) {
            return false;
        }

        return DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->where('users.id', $id)
            ->whereNotIn('roles.nama', Disposisi::PERAN_OPERATOR)
            ->exists();
    }
}
