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

    /** Setengah folio 16,5 cm × 21,5 cm dalam poin (1 mm = 2,8346 pt). */
    public const LEBAR_PT = 467.72;

    public const TINGGI_PT = 609.45;

    /** Buat/perbarui PDF kartu satu disposisi. */
    public function simpan(Disposisi $disposisi): Disposisi
    {
        $disposisi->loadMissing([
            'suratMasuk.instansi', 'suratKeluar.unit', 'tujuan.unit', 'pembuat',
        ]);

        // Nama berkas di penyimpanan tetap unik per disposisi; nama unduhan mengikuti isi kartu.
        $nama = 'kartu-disposisi-'.$disposisi->id.'.pdf';

        Storage::disk(self::DISK)->put(
            self::FOLDER.'/'.$nama,
            $this->pdf(collect([$disposisi]))->output()
        );

        $disposisi->updateQuietly([
            'kartu_path' => self::FOLDER.'/'.$nama,
            'kartu_nama' => $disposisi->namaBerkasKartu(),
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

        $nama = $items->count() === 1
            ? $items->first()->namaBerkasKartu()
            : 'Kartu Disposisi - '.$items->count().' kartu - '.now()->format('Y-m-d').'.pdf';

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
            ->setPaper([0, 0, self::LEBAR_PT, self::TINGGI_PT], 'portrait')
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
