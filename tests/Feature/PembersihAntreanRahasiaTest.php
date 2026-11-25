<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\DisposisiBaru;
use App\Notifications\PengingatBatasWaktu;
use App\Notifications\StatusTindakLanjut;
use App\Services\PembersihAntreanRahasia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Concerns\BuatData;
use Tests\TestCase;

/**
 * Job notifikasi surat rahasia untuk penerima di luar unit tujuan disposisi
 * tidak boleh sempat terkirim. Job untuk staf unit tujuan tetap dibiarkan.
 */
class PembersihAntreanRahasiaTest extends TestCase
{
    use BuatData, RefreshDatabase;

    public function test_job_notifikasi_rahasia_untuk_penerima_tanpa_akses_dibatalkan(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->staf()->unit($unit)->create();
        $luar = User::factory()->staf()->unit($this->unit('ULA'))->create();

        $rahasia = $this->suratMasuk($tu, ['sifat' => 'rahasia']);
        $biasa = $this->suratMasuk($tu);
        $disposisiRahasia = $this->disposisi($rahasia, $pimpinan, [$unit]);
        $disposisiBiasa = $this->disposisi($biasa, $pimpinan, [$unit]);
        $tujuanRahasia = $disposisiRahasia->tujuan()->first();

        $antre = Queue::connection('database');
        $antre->push(new SendQueuedNotifications($luar, new DisposisiBaru($disposisiRahasia), ['database']));
        $antre->push(new SendQueuedNotifications($luar, new DisposisiBaru($disposisiBiasa), ['database']));
        $antre->push(new SendQueuedNotifications($pegawai, new DisposisiBaru($disposisiRahasia), ['database']));
        $antre->push(new SendQueuedNotifications($luar, new StatusTindakLanjut($tujuanRahasia, 'diproses', 'Pimpinan'), ['database']));
        $antre->push(new SendQueuedNotifications($luar, new PengingatBatasWaktu($tujuanRahasia), ['database']));

        $this->assertSame(5, DB::table('jobs')->count());

        $dihapus = app(PembersihAntreanRahasia::class)->jalankan();

        // Yang tersisa: disposisi biasa untuk unit luar dan disposisi rahasia untuk unit tujuan.
        $this->assertSame(3, $dihapus);
        $this->assertSame(2, DB::table('jobs')->count());
    }

    public function test_job_gagal_notifikasi_rahasia_untuk_penerima_tanpa_akses_dihapus(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->staf()->unit($unit)->create();
        $luar = User::factory()->staf()->unit($this->unit('ULA'))->create();

        $rahasia = $this->suratMasuk($tu, ['sifat' => 'rahasia']);
        $biasa = $this->suratMasuk($tu);
        $disposisiRahasia = $this->disposisi($rahasia, $pimpinan, [$unit]);
        $disposisiBiasa = $this->disposisi($biasa, $pimpinan, [$unit]);

        $antre = Queue::connection('database');
        $antre->push(new SendQueuedNotifications($luar, new DisposisiBaru($disposisiRahasia), ['database']));
        $antre->push(new SendQueuedNotifications($luar, new DisposisiBaru($disposisiBiasa), ['database']));
        $antre->push(new SendQueuedNotifications($pegawai, new DisposisiBaru($disposisiRahasia), ['database']));

        // Tiru job yang gagal: payload antrean dipindahkan ke failed_jobs.
        foreach (DB::table('jobs')->get() as $job) {
            DB::table('failed_jobs')->insert([
                'uuid' => (string) Str::uuid(),
                'connection' => 'database',
                'queue' => 'default',
                'payload' => $job->payload,
                'exception' => 'RuntimeException: gagal mengirim notifikasi',
                'failed_at' => now(),
            ]);
        }
        DB::table('jobs')->delete();

        $this->assertSame(3, DB::table('failed_jobs')->count());

        $dihapus = app(PembersihAntreanRahasia::class)->jalankan();

        // Yang tersisa: disposisi biasa untuk unit luar dan disposisi rahasia untuk unit tujuan.
        $this->assertSame(1, $dihapus);
        $this->assertSame(2, DB::table('failed_jobs')->count());
    }

    public function test_tabel_antrean_yang_tidak_ada_dilewati(): void
    {
        // Tidak memakai DDL agar transaksi RefreshDatabase di MySQL tidak ter-commit.
        Schema::shouldReceive('hasTable')->andReturnUsing(fn (string $tabel) => $tabel === 'jobs');

        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $luar = User::factory()->staf()->unit($this->unit('ULA'))->create();

        $disposisi = $this->disposisi($this->suratMasuk($tu, ['sifat' => 'rahasia']), $pimpinan, [$unit]);

        Queue::connection('database')->push(
            new SendQueuedNotifications($luar, new DisposisiBaru($disposisi), ['database'])
        );

        // Tabel jobs tetap dibersihkan meski failed_jobs tidak ada.
        $this->assertSame(1, app(PembersihAntreanRahasia::class)->jalankan());
        $this->assertSame(0, DB::table('jobs')->count());
    }
}
