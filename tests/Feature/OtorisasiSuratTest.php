<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatData;
use Tests\TestCase;

class OtorisasiSuratTest extends TestCase
{
    use RefreshDatabase, BuatData;

    public function test_staf_dan_admin_membuka_semua_surat_masuk(): void
    {
        $staf = User::factory()->staf()->create();
        $admin = User::factory()->admin()->create();
        $surat = $this->suratMasuk($staf, ['sifat' => 'rahasia']);

        foreach ([$staf, $admin] as $user) {
            $this->actingAs($user)->get(route('surat-masuk.show', $surat))->assertOk();
            $this->actingAs($user)->get(route('surat-masuk.index'))->assertSee($surat->perihal);
        }
    }

    public function test_staf_dan_admin_membuka_semua_surat_keluar(): void
    {
        $staf = User::factory()->staf()->create();
        $admin = User::factory()->admin()->create();
        $surat = $this->suratKeluar($staf, ['unit_id' => $this->unit('UMPEG')->id]);

        foreach ([$staf, $admin] as $user) {
            $this->actingAs($user)->get(route('surat-keluar.show', $surat))->assertOk();
        }
    }

    public function test_staf_dan_admin_dapat_mencatat_surat(): void
    {
        foreach ([User::factory()->staf()->create(), User::factory()->admin()->create()] as $user) {
            $this->actingAs($user)->get(route('surat-masuk.create'))->assertOk();
            $this->actingAs($user)->get(route('surat-keluar.create'))->assertOk();
        }
    }
}
