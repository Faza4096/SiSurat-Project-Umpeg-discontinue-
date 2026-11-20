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
use Illuminate\Support\Str;
use Tests\Concerns\BuatData;
use Tests\TestCase;

/**
 * Job notifikasi yang terlanjur masuk antrean sebelum aturan penerima
 * diperketat tidak boleh sempat terkirim ke non-operator bila suratnya
 * bertanda rahasia.
 */
class PembersihAntreanRahasiaTest extends TestCase
{
    use BuatData, RefreshDatabase;

    public function test_job_notifikasi_rahasia_untuk_non_operator_dibatalkan(): void
    {
        $staf = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->role('pegawai')->unit($unit)->create();
        $stafUnit = User::factory()->staf()->unit($unit)->create();

        $rahasia = $this->suratMasuk($staf, ['sifat' => 'rahasia']);
        $biasa = $this->suratMasuk($staf);
        $disposisiRahasia = $this->disposisi($rahasia, $pimpinan, [$unit]);
        $disposisiBiasa = $this->disposisi($biasa, $pimpinan, [$unit]);
        $tujuanRahasia = $disposisiRahasia->tujuan()->first();

        $antre = Queue::connection('database');
        $antre->push(new SendQueuedNotifications($pegawai, new DisposisiBaru($disposisiRahasia), ['database']));
        $antre->push(new SendQueuedNotifications($pegawai, new DisposisiBaru($disposisiBiasa), ['database']));
        $antre->push(new SendQueuedNotifications($stafUnit, new DisposisiBaru($disposisiRahasia), ['database']));
        $antre->push(new SendQueuedNotifications($pegawai, new StatusTindakLanjut($tujuanRahasia, 'diproses', 'Pimpinan'), ['database']));
        $antre->push(new SendQueuedNotifications($pegawai, new PengingatBatasWaktu($tujuanRahasia), ['database']));

        $this->assertSame(5, DB::table('jobs')->count());

        $dihapus = app(PembersihAntreanRahasia::class)->jalankan();

        // Yang tersisa: disposisi biasa untuk pegawai dan disposisi rahasia untuk staf unit.
        $this->assertSame(3, $dihapus);
        $this->assertSame(2, DB::table('jobs')->count());
    }

    public function test_job_gagal_notifikasi_rahasia_untuk_non_operator_dihapus(): void
    {
        $staf = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->role('pegawai')->unit($unit)->create();
        $stafUnit = User::factory()->staf()->unit($unit)->create();

        $rahasia = $this->suratMasuk($staf, ['sifat' => 'rahasia']);
        $biasa = $this->suratMasuk($staf);
        $disposisiRahasia = $this->disposisi($rahasia, $pimpinan, [$unit]);
        $disposisiBiasa = $this->disposisi($biasa, $pimpinan, [$unit]);

        $antre = Queue::connection('database');
        $antre->push(new SendQueuedNotifications($pegawai, new DisposisiBaru($disposisiRahasia), ['database']));
        $antre->push(new SendQueuedNotifications($pegawai, new DisposisiBaru($disposisiBiasa), ['database']));
        $antre->push(new SendQueuedNotifications($stafUnit, new DisposisiBaru($disposisiRahasia), ['database']));

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

        // Yang tersisa: disposisi biasa untuk pegawai dan disposisi rahasia untuk staf unit.
        $this->assertSame(1, $dihapus);
        $this->assertSame(2, DB::table('failed_jobs')->count());
    }
}
