<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatData;
use Tests\TestCase;

class OtorisasiSuratTest extends TestCase
{
    use RefreshDatabase, BuatData;

    public function test_administrator_petugas_tu_dan_pimpinan_membuka_semua_surat_masuk(): void
    {
        $pencatat = User::factory()->petugasTu()->create();
        $surat = $this->suratMasuk($pencatat, ['sifat' => 'rahasia']);

        $pengguna = [
            User::factory()->petugasTu()->create(),
            User::factory()->pimpinan()->create(),
            User::factory()->admin()->create(),
        ];

        foreach ($pengguna as $user) {
            $this->actingAs($user)->get(route('surat-masuk.show', $surat))->assertOk();
            $this->actingAs($user)->get(route('surat-masuk.index'))->assertSee($surat->perihal);
        }
    }

    public function test_administrator_petugas_tu_dan_pimpinan_membuka_semua_surat_keluar(): void
    {
        $pembuat = User::factory()->petugasTu()->create();
        $surat = $this->suratKeluar($pembuat, ['unit_id' => $this->unit('UMPEG')->id]);

        $pengguna = [
            User::factory()->petugasTu()->create(),
            User::factory()->pimpinan()->create(),
            User::factory()->admin()->create(),
        ];

        foreach ($pengguna as $user) {
            $this->actingAs($user)->get(route('surat-keluar.show', $surat))->assertOk();
        }
    }

    public function test_staf_hanya_melihat_surat_yang_didisposisikan_ke_unitnya(): void
    {
        $pencatat = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->staf()->unit($unit)->create();
        $stafLain = User::factory()->staf()->unit($this->unit('ULA'))->create();
        $surat = $this->suratMasuk($pencatat);

        // Belum didisposisikan: staf belum punya akses.
        $this->actingAs($staf)->get(route('surat-masuk.show', $surat))->assertForbidden();

        $this->disposisi($surat, $pimpinan, [$unit]);

        $this->actingAs($staf)->get(route('surat-masuk.show', $surat))->assertOk();
        $this->actingAs($stafLain)->get(route('surat-masuk.show', $surat))->assertForbidden();
    }

    public function test_hanya_petugas_tu_dapat_mencatat_surat(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $this->actingAs($tu)->get(route('surat-masuk.create'))->assertOk();
        $this->actingAs($tu)->get(route('surat-keluar.create'))->assertOk();

        $lain = [
            User::factory()->admin()->create(),
            User::factory()->pimpinan()->create(),
            User::factory()->staf()->create(),
        ];

        foreach ($lain as $user) {
            $this->actingAs($user)->get(route('surat-masuk.create'))->assertForbidden();
            $this->actingAs($user)->get(route('surat-keluar.create'))->assertForbidden();
        }
    }

    public function test_hanya_petugas_tu_dapat_mengubah_surat(): void
    {
        $pencatat = User::factory()->petugasTu()->create();
        $surat = $this->suratMasuk($pencatat);

        foreach ([User::factory()->admin()->create(), User::factory()->pimpinan()->create(), User::factory()->staf()->create()] as $user) {
            $this->actingAs($user)
                ->put(route('surat-masuk.update', $surat), [
                    'no_surat' => '400.7/99-TU',
                    'tgl_surat' => now()->toDateString(),
                    'tgl_diterima' => now()->toDateString(),
                    'perihal' => 'Perubahan tidak sah',
                    'sifat' => 'biasa',
                ])
                ->assertForbidden();
        }
    }
}
