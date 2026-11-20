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
 * Notifikasi yang tersimpan sebelum aturan penerima diperketat masih memuat
 * nomor/perihal surat rahasia di kotak masuk non-operator. Pembersih harus
 * menghapus baris lama itu tanpa menyentuh notifikasi lain.
 */
class PembersihNotifikasiRahasiaTest extends TestCase
{
    use BuatData, RefreshDatabase;

    public function test_notifikasi_lama_surat_rahasia_milik_non_operator_dihapus(): void
    {
        $staf = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->role('pegawai')->unit($unit)->create();

        $rahasiaMasuk = $this->suratMasuk($staf, ['sifat' => 'rahasia']);
        $biasaMasuk = $this->suratMasuk($staf);
        $rahasiaKeluar = $this->suratKeluar($staf, ['sifat' => 'rahasia']);

        $notifRahasiaMasuk = $this->notifikasi($pegawai, route('surat-masuk.show', $rahasiaMasuk));
        $notifBiasa = $this->notifikasi($pegawai, route('surat-masuk.show', $biasaMasuk));
        $notifRahasiaKeluar = $this->notifikasi($pegawai, route('surat-keluar.show', $rahasiaKeluar));
        $notifStaf = $this->notifikasi($staf, route('surat-masuk.show', $rahasiaMasuk));

        $dihapus = app(PembersihNotifikasiRahasia::class)->jalankan();

        $this->assertSame(2, $dihapus);
        $this->assertDatabaseMissing('notifications', ['id' => $notifRahasiaMasuk]);
        $this->assertDatabaseMissing('notifications', ['id' => $notifRahasiaKeluar]);
        // Surat biasa dan notifikasi milik operator tidak ikut terhapus.
        $this->assertDatabaseHas('notifications', ['id' => $notifBiasa]);
        $this->assertDatabaseHas('notifications', ['id' => $notifStaf]);
    }

    public function test_notifikasi_tanpa_tautan_surat_tidak_ikut_terhapus(): void
    {
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->role('pegawai')->unit($unit)->create();

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
