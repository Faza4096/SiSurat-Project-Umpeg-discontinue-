<?php

namespace App\Providers;

use App\Models\PengaturanInstansi;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Satu aturan sandi untuk reset, profil, dan penggantian sandi wajib.
        Password::defaults(fn () => Password::min(10)->letters()->numbers());

        // Identitas instansi dibaca dari satu tempat, yaitu menu Pengaturan Instansi.
        View::composer(['layouts.sisurat', 'layouts.guest', 'laporan.pdf'], function ($view) {
            $view->with('identitas', $this->identitasInstansi());
        });

        $this->abaikanHotFileMati();
    }

    /**
     * Baris pengaturan instansi; sebelum migrasi dijalankan dipakai nilai config
     * agar halaman login tetap bisa dirender (mis. saat pengujian).
     */
    private function identitasInstansi(): PengaturanInstansi
    {
        try {
            return PengaturanInstansi::current();
        } catch (QueryException $galat) {
            // Hanya tabel yang belum dimigrasi yang boleh memakai nilai config.
            // Galat lain (kolom hilang, koneksi putus) tetap diteruskan ke
            // penanganan galat agar tidak tersamarkan sebagai halaman normal.
            if (Schema::hasTable('pengaturan_instansi')) {
                throw $galat;
            }

            return new PengaturanInstansi([
                'nama' => config('sisurat.instansi.nama'),
                'unit' => config('sisurat.instansi.unit'),
            ]);
        }
    }

    /**
     * Vite yang berhenti mendadak meninggalkan public/hot; tanpa ini setiap
     * halaman memuat aset dari port yang sudah tidak mendengarkan.
     */
    private function abaikanHotFileMati(): void
    {
        $berkas = public_path('hot');

        if ($this->app->runningUnitTests() || ! is_file($berkas)) {
            return;
        }

        $isi = trim((string) file_get_contents($berkas));
        $bagian = parse_url($isi);

        if (! isset($bagian['host'])) {
            return;
        }

        $port = $bagian['port'] ?? (($bagian['scheme'] ?? 'http') === 'https' ? 443 : 80);

        // Sengaja TTL pendek dan disimpan di store file: cukup menahan percobaan
        // koneksi beruntun tanpa menahan halaman pada dev server yang sudah mati.
        $hidup = Cache::store('file')->remember(
            'vite-hot:' . md5($isi),
            5,
            fn () => $this->portMendengarkan($bagian['host'], $port),
        );

        if ($hidup) {
            return;
        }

        Vite::useHotFile(storage_path('framework/vite.hot-kosong'));
    }

    /** Vite biasanya mendengarkan di IPv4, sedangkan localhost bisa diarahkan ke IPv6. */
    private function portMendengarkan(string $host, int $port): bool
    {
        foreach ($host === 'localhost' ? ['127.0.0.1', '::1'] : [$host] as $alamat) {
            $soket = @fsockopen($alamat, $port, $kode, $pesan, 0.2);

            if ($soket !== false) {
                fclose($soket);

                return true;
            }
        }

        return false;
    }
}
