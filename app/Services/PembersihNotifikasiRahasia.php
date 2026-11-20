<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * Notifikasi yang sudah tersimpan sebelum aturan penerima surat rahasia
 * diperketat masih memuat nomor/perihal rahasia di kotak masuk non-operator.
 * Pembersih ini menghapus baris lama tersebut berdasarkan tautan surat yang
 * tersimpan di kolom data.
 */
class PembersihNotifikasiRahasia
{
    private const PERAN_OPERATOR = ['admin', 'staf'];

    /** @return int jumlah notifikasi yang dihapus */
    public function jalankan(): int
    {
        $nonOperator = DB::table('users')
            ->join('roles', 'roles.id', '=', 'users.role_id')
            ->whereNotIn('roles.nama', self::PERAN_OPERATOR)
            ->pluck('users.id')
            ->all();

        if ($nonOperator === []) {
            return 0;
        }

        $rahasia = [
            'masuk' => DB::table('surat_masuk')->where('sifat', 'rahasia')->pluck('id')->flip()->all(),
            'keluar' => DB::table('surat_keluar')->where('sifat', 'rahasia')->pluck('id')->flip()->all(),
        ];

        if ($rahasia['masuk'] === [] && $rahasia['keluar'] === []) {
            return 0;
        }

        $dihapus = 0;

        DB::table('notifications')
            ->whereIn('notifiable_id', $nonOperator)
            ->select('id', 'data')
            ->chunkById(200, function ($baris) use (&$dihapus, $rahasia) {
                $ids = $baris
                    ->filter(fn ($notifikasi) => $this->menunjukSuratRahasia($notifikasi->data, $rahasia))
                    ->pluck('id');

                if ($ids->isNotEmpty()) {
                    DB::table('notifications')->whereIn('id', $ids)->delete();
                    $dihapus += $ids->count();
                }
            });

        return $dihapus;
    }

    /** @param array{masuk: array<int, int>, keluar: array<int, int>} $rahasia */
    private function menunjukSuratRahasia(?string $data, array $rahasia): bool
    {
        $url = json_decode((string) $data, true)['url'] ?? '';

        if (! is_string($url) || ! preg_match('#/surat-(masuk|keluar)/(\d+)#', $url, $cocok)) {
            return false;
        }

        return isset($rahasia[$cocok[1]][(int) $cocok[2]]);
    }
}
