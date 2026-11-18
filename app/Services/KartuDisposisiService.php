<?php

namespace App\Services;

use App\Models\Disposisi;
use App\Models\PengaturanInstansi;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

/**
 * Membuat dan menyimpan PDF kartu disposisi. Berkas disimpan di disk privat
 * (storage/app/kartu-disposisi) dan hanya dapat diunduh lewat rute ber-otorisasi.
 */
class KartuDisposisiService
{
    public const DISK = 'local';

    public const FOLDER = 'kartu-disposisi';

    /** Buat/perbarui PDF kartu satu disposisi. */
    public function simpan(Disposisi $disposisi): Disposisi
    {
        $disposisi->loadMissing([
            'suratMasuk.instansi', 'suratKeluar.unit', 'tujuan.unit', 'pembuat',
        ]);

        $nama = 'kartu-disposisi-' . $disposisi->id . '.pdf';

        Storage::disk(self::DISK)->put(
            self::FOLDER . '/' . $nama,
            $this->pdf(collect([$disposisi]))->output()
        );

        $disposisi->updateQuietly([
            'kartu_path' => self::FOLDER . '/' . $nama,
            'kartu_nama' => $nama,
            'kartu_dibuat_pada' => now(),
        ]);

        return $disposisi;
    }

    /** Pastikan berkas ada; buat ulang bila hilang. */
    public function pastikan(Disposisi $disposisi): Disposisi
    {
        $ada = $disposisi->kartu_path && Storage::disk(self::DISK)->exists($disposisi->kartu_path);

        return $ada ? $disposisi : $this->simpan($disposisi);
    }

    /** Gabungan beberapa kartu dalam satu PDF (satu kartu per halaman). */
    public function unduhBanyak(Collection $items)
    {
        $items->loadMissing(['suratMasuk.instansi', 'suratKeluar.unit', 'tujuan.unit', 'pembuat']);

        $nama = 'kartu-disposisi-' . now()->format('Ymd-His') . '.pdf';

        return $this->pdf($items)->download($nama);
    }

    public function html(Collection $items): string
    {
        return view('disposisi.kartu-pdf', [
            'items' => $items,
            'instansi' => PengaturanInstansi::current(),
            'logo' => PengaturanInstansi::current()->logoDataUri(),
        ])->render();
    }

    private function pdf(Collection $items)
    {
        return Pdf::loadHTML($this->html($items))
            ->setPaper('a4', 'portrait')
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'DejaVu Serif');
    }

    /** Hapus berkas kartu (mis. saat disposisi dibatalkan). */
    public function hapus(Disposisi $disposisi): void
    {
        if ($disposisi->kartu_path) {
            Storage::disk(self::DISK)->delete($disposisi->kartu_path);
        }
    }
}
