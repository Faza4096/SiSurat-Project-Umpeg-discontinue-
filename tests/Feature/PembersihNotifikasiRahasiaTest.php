<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\PembersihNotifikasiRahasia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\Concerns\BuatData;
use Tests\TestCase;

/**
 * Notifikasi surat rahasia untuk penerima di luar unit tujuan disposisi masih
 * memuat nomor/perihal rahasia. Pembersih menghapus baris itu, tetapi
 * notifikasi milik staf unit tujuan tetap dipertahankan.
 */
class PembersihNotifikasiRahasiaTest extends TestCase
{
    use BuatData, RefreshDatabase;

    public function test_notifikasi_rahasia_untuk_penerima_tanpa_akses_dihapus(): void
    {
        $operator = User::factory()->petugasTu()->create();
        $unit = $this->unit('UMPEG');
        $penerima = User::factory()->staf()->unit($unit)->create();
        $luar = User::factory()->staf()->unit($this->unit('ULA'))->create();

        $rahasiaMasuk = $this->suratMasuk($operator, ['sifat' => 'rahasia']);
        $biasaMasuk = $this->suratMasuk($operator);
        $rahasiaKeluar = $this->suratKeluar($operator, ['sifat' => 'rahasia']);

        // Disposisi ditujukan ke UMPEG, jadi staf UMPEG berhak membuka suratnya.
        $this->disposisi($rahasiaMasuk, $operator, [$unit]);

        $notifLuar = $this->notifikasi($luar, route('surat-masuk.show', $rahasiaMasuk));
        $notifRahasiaKeluar = $this->notifikasi($luar, route('surat-keluar.show', $rahasiaKeluar));
        $notifBiasaLuar = $this->notifikasi($luar, route('surat-masuk.show', $biasaMasuk));
        $notifPenerima = $this->notifikasi($penerima, route('surat-masuk.show', $rahasiaMasuk));
        $notifOperator = $this->notifikasi($operator, route('surat-masuk.show', $rahasiaMasuk));

        $dihapus = app(PembersihNotifikasiRahasia::class)->jalankan();

        $this->assertSame(2, $dihapus);
        $this->assertDatabaseMissing('notifications', ['id' => $notifLuar]);
        $this->assertDatabaseMissing('notifications', ['id' => $notifRahasiaKeluar]);

        // Surat biasa, staf unit tujuan, dan operator tidak ikut terhapus.
        $this->assertDatabaseHas('notifications', ['id' => $notifBiasaLuar]);
        $this->assertDatabaseHas('notifications', ['id' => $notifPenerima]);
        $this->assertDatabaseHas('notifications', ['id' => $notifOperator]);
    }

    public function test_notifikasi_tanpa_tautan_surat_tidak_ikut_terhapus(): void
    {
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->staf()->unit($unit)->create();

        $id = (string) Str::uuid();
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\Notifications\DisposisiBaru',
            'notifiable_type' => User::class,
            'notifiable_id' => $pegawai->id,
            'data' => json_encode(['jenis' => 'disposisi_baru', 'judul' => 'Tanpa tautan']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->assertSame(0, app(PembersihNotifikasiRahasia::class)->jalankan());
        $this->assertDatabaseHas('notifications', ['id' => $id]);
    }

    private function notifikasi(User $penerima, string $url): string
    {
        $id = (string) Str::uuid();

        DB::table('notifications')->insert([
            'id' => $id,
            'type' => 'App\Notifications\DisposisiBaru',
            'notifiable_type' => User::class,
            'notifiable_id' => $penerima->id,
            'data' => json_encode([
                'jenis' => 'disposisi_baru',
                'judul' => 'Disposisi baru',
                'url' => $url,
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }
}
