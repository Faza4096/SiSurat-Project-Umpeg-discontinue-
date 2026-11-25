<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\DisposisiBaru;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuatData;
use Tests\TestCase;

/**
 * Memakai DatabaseMigrations agar tidak ada transaksi pembungkus RefreshDatabase:
 * callback after-commit hanya berjalan pada commit transaksi paling luar.
 */
class AntreanNotifikasiTest extends TestCase
{
    use BuatData, DatabaseMigrations;

    public function test_notifikasi_baru_masuk_antrean_setelah_transaksi_commit(): void
    {
        config(['queue.default' => 'database']);

        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->create(['unit_id' => $unit->id]);

        $surat = $this->suratMasuk($tu);
        $disposisi = $this->disposisi($surat, $pimpinan, [$unit]);

        DB::transaction(function () use ($staf, $disposisi) {
            $staf->notify(new DisposisiBaru($disposisi));

            $this->assertSame(0, DB::table('jobs')->count(), 'Job sudah masuk antrean sebelum transaksi commit.');
            $this->assertSame(0, $staf->notifications()->count(), 'Notifikasi database terkirim sebelum transaksi commit.');
        });

        // Satu job per kanal: 'database' dan 'mail'.
        $this->assertSame(2, DB::table('jobs')->count());
    }
}
