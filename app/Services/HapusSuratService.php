<?php

namespace App\Services;

use App\Models\Disposisi;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Hapus permanen: baris basis data dibuang dalam satu transaksi, berkas baru
 * dihapus setelah commit agar tidak ada berkas hilang saat transaksi batal.
 */
class HapusSuratService
{
    public function masuk(SuratMasuk $surat): void
    {
        $berkas = DB::transaction(function () use ($surat) {
            $berkas = $this->hapusDisposisi($surat->disposisi()->withTrashed()->get());
            $berkas = [...$berkas, ...$this->hapusLampiran($surat)];

            // Surat keluar yang membalas tidak ikut terhapus; relasinya menjadi null.
            $surat->forceDelete();

            return $berkas;
        });

        $this->hapusBerkas($berkas);
    }

    public function keluar(SuratKeluar $surat): void
    {
        $berkas = DB::transaction(function () use ($surat) {
            $berkas = $this->hapusDisposisi($surat->disposisiKartu()->withTrashed()->get());
            $berkas = [...$berkas, ...$this->hapusLampiran($surat)];

            if ($surat->file_final_path) {
                $berkas[] = $surat->file_final_path;
            }

            $surat->forceDelete();

            return $berkas;
        });

        $this->hapusBerkas($berkas);
    }

    /**
     * @param  iterable<int, Disposisi>  $daftar
     * @return array<int, string> path berkas yang harus dihapus
     */
    private function hapusDisposisi(iterable $daftar): array
    {
        $berkas = [];

        foreach ($daftar as $disposisi) {
            foreach ($disposisi->tujuan as $tujuan) {
                $berkas[] = $tujuan->bukti_path;
                $tujuan->delete();
            }

            $berkas[] = $disposisi->kartu_path;
            $disposisi->forceDelete();
        }

        return $berkas;
    }

    /** @return array<int, string> */
    private function hapusLampiran(SuratMasuk|SuratKeluar $surat): array
    {
        $berkas = [];

        foreach ($surat->lampiran()->get() as $lampiran) {
            $berkas[] = $lampiran->path;
            $lampiran->delete();
        }

        return $berkas;
    }

    /** @param array<int, string|null> $berkas */
    private function hapusBerkas(array $berkas): void
    {
        $paths = array_values(array_filter($berkas));

        if ($paths !== []) {
            Storage::delete($paths);
        }
    }
}
