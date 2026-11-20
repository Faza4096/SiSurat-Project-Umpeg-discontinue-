<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\DisposisiBaru;
use App\Notifications\MenungguTandaTangan;
use App\Notifications\PengingatBatasWaktu;
use App\Notifications\StatusTindakLanjut;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\SendQueuedNotifications;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\Concerns\BuatData;
use Tests\TestCase;

class NotifikasiTest extends TestCase
{
    use RefreshDatabase, BuatData;

    public function test_keempat_notifikasi_di_antrekan(): void
    {
        $kelas = [DisposisiBaru::class, MenungguTandaTangan::class, StatusTindakLanjut::class, PengingatBatasWaktu::class];

        foreach ($kelas as $notifikasi) {
            $this->assertContains(ShouldQueue::class, class_implements($notifikasi), "$notifikasi belum masuk antrean.");
        }
    }

    public function test_notifikasi_dari_request_masuk_antrean(): void
    {
        Queue::fake();

        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->create(['unit_id' => $unit->id]);

        $surat = $this->suratMasuk($tu);
        $tujuan = $this->disposisi($surat, $pimpinan, [$unit])->tujuan()->first();

        $this->actingAs($pimpinan)->patch(route('tujuan.status', $tujuan), ['status' => 'selesai'])
            ->assertSessionHasNoErrors();

        Queue::assertPushed(
            SendQueuedNotifications::class,
            fn (SendQueuedNotifications $job) => $job->notification instanceof StatusTindakLanjut,
        );
    }

    public function test_notifikasi_tersimpan_di_database_dan_tampil_di_halaman(): void
    {
        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->create(['unit_id' => $unit->id]);

        $surat = $this->suratMasuk($tu);
        $disposisi = $this->disposisi($surat, $pimpinan, [$unit]);

        $staf->notify(new DisposisiBaru($disposisi));

        $this->assertSame(1, $staf->notifications()->count());

        $this->actingAs($staf)->get(route('notifikasi.index'))
            ->assertOk()
            ->assertSee($surat->no_agenda);

        $notifikasi = $staf->notifications()->first();
        $this->actingAs($staf)->get(route('notifikasi.baca', $notifikasi->id))->assertRedirect();
        $this->assertNotNull($notifikasi->fresh()->read_at);
    }

    public function test_perubahan_status_hanya_dikabarkan_bila_oleh_pimpinan_atau_tu(): void
    {
        Notification::fake();

        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->create(['unit_id' => $unit->id]);

        $surat = $this->suratMasuk($tu);
        $tujuan = $this->disposisi($surat, $pimpinan, [$unit])->tujuan()->first();

        $this->actingAs($staf)->patch(route('tujuan.status', $tujuan), ['status' => 'diproses'])
            ->assertSessionHasNoErrors();
        Notification::assertNotSentTo($staf, StatusTindakLanjut::class);

        $this->actingAs($pimpinan)->patch(route('tujuan.status', $tujuan), ['status' => 'selesai'])
            ->assertSessionHasNoErrors();
        Notification::assertSentTo($staf, StatusTindakLanjut::class);
    }

    public function test_disposisi_surat_rahasia_tidak_dikabarkan_ke_pegawai_unit_tujuan(): void
    {
        Notification::fake();

        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->role('pegawai')->unit($unit)->create();
        $stafUnit = User::factory()->staf()->unit($unit)->create();

        $surat = $this->suratMasuk($tu, ['sifat' => 'rahasia']);

        $this->actingAs($pimpinan)->post(route('disposisi.acc', $surat), [
            'indeks' => '400.7/R',
            'tgl_penyelesaian' => now()->addDays(3)->toDateString(),
            'instruksi' => 'Tindak lanjuti secara terbatas.',
            'tujuan' => [$unit->id],
        ])->assertSessionHasNoErrors();

        Notification::assertNotSentTo($pegawai, DisposisiBaru::class);
        Notification::assertSentTo($stafUnit, DisposisiBaru::class);
    }

    public function test_status_tindak_lanjut_surat_rahasia_hanya_dikabarkan_ke_operator_unit(): void
    {
        Notification::fake();

        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->role('pegawai')->unit($unit)->create();
        $stafUnit = User::factory()->staf()->unit($unit)->create();

        $surat = $this->suratMasuk($tu, ['sifat' => 'rahasia']);
        $tujuan = $this->disposisi($surat, $pimpinan, [$unit])->tujuan()->first();

        $this->actingAs($pimpinan)->patch(route('tujuan.status', $tujuan), ['status' => 'diproses'])
            ->assertSessionHasNoErrors();

        Notification::assertNotSentTo($pegawai, StatusTindakLanjut::class);
        Notification::assertSentTo($stafUnit, StatusTindakLanjut::class);
    }

    public function test_pengingat_batas_waktu_surat_rahasia_hanya_untuk_operator_unit(): void
    {
        Notification::fake();

        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->role('pegawai')->unit($unit)->create();
        $stafUnit = User::factory()->staf()->unit($unit)->create();

        $surat = $this->suratMasuk($tu, ['sifat' => 'rahasia']);
        $this->disposisi($surat, $pimpinan, [$unit], ['tgl_penyelesaian' => now()->addDay()]);

        $this->artisan('disposisi:ingatkan')->assertSuccessful();

        Notification::assertNotSentTo($pegawai, PengingatBatasWaktu::class);
        Notification::assertSentTo($stafUnit, PengingatBatasWaktu::class);
    }

    public function test_command_pengingat_mengirim_notifikasi_batas_waktu(): void
    {
        Notification::fake();

        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->create(['unit_id' => $unit->id]);

        $surat = $this->suratMasuk($tu);
        $this->disposisi($surat, $pimpinan, [$unit], ['tgl_penyelesaian' => now()->addDay()]);

        $this->artisan('disposisi:ingatkan')->assertSuccessful();

        Notification::assertSentTo($staf, PengingatBatasWaktu::class);
    }

    public function test_command_tidak_mengirim_untuk_batas_yang_masih_lama(): void
    {
        Notification::fake();

        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->create(['unit_id' => $unit->id]);

        $surat = $this->suratMasuk($tu);
        $this->disposisi($surat, $pimpinan, [$unit], ['tgl_penyelesaian' => now()->addDays(20)]);

        $this->artisan('disposisi:ingatkan')->assertSuccessful();

        Notification::assertNotSentTo($staf, PengingatBatasWaktu::class);
    }
}
