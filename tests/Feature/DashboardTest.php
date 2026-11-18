<?php

namespace Tests\Feature;

use App\Models\LogAktivitas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\BuatData;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase, BuatData;

    public function test_staf_melihat_antrean_operator(): void
    {
        $staf = User::factory()->staf()->create();

        $menungguDisposisi = $this->suratMasuk($staf);
        $pengajuan = $this->suratKeluar($staf, ['status' => 'menunggu_ttd']);
        $sudahAcc = $this->suratKeluar($staf, ['status' => 'disetujui']);
        $selesai = $this->suratMasuk($staf, ['status' => 'selesai']);

        $this->actingAs($staf)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Surat masuk menunggu ACC')
            ->assertSee($menungguDisposisi->perihal)
            ->assertSee('Surat keluar menunggu ACC')
            ->assertSee($pengajuan->hal)
            ->assertSee('Sudah di-ACC, belum dikirim')
            ->assertSee($sudahAcc->hal)
            ->assertSee('Belum diarsipkan')
            ->assertSee($selesai->perihal)
            ->assertDontSee('Belum diberi disposisi');
    }

    public function test_pimpinan_melihat_antrean_dengan_sifat_mendesak_lebih_dulu(): void
    {
        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();

        $biasa = $this->suratMasuk($tu, ['sifat' => 'biasa']);
        $segera = $this->suratMasuk($tu, ['sifat' => 'segera']);
        $pengajuan = $this->suratKeluar($tu, ['status' => 'menunggu_ttd']);

        $html = $this->actingAs($pimpinan)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Surat masuk menunggu ACC')
            ->assertSee('Surat keluar menunggu ACC')
            ->assertSee($pengajuan->hal)
            ->getContent();

        // Surat bersifat "segera" harus muncul lebih dulu daripada "biasa".
        $this->assertLessThan(
            strpos($html, $biasa->perihal),
            strpos($html, $segera->perihal),
            'Surat segera harus diurutkan sebelum surat biasa.'
        );
    }

    public function test_admin_melihat_ringkasan_akun_dan_log_terbaru(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->staf()->create();

        LogAktivitas::create([
            'pengguna_id' => $admin->id,
            'aksi' => 'pengguna.baru',
            'keterangan' => 'Pengguna uji ditambahkan',
            'waktu' => now(),
        ]);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Akun Pengguna')
            ->assertSee('Total akun')
            ->assertSee('Masih memakai sandi sementara')
            ->assertSee('Log Terbaru')
            ->assertSee('Pengguna uji ditambahkan');
    }

    public function test_staf_tidak_melihat_menu_administrasi_dan_panel_admin(): void
    {
        $staf = User::factory()->staf()->create();
        User::factory()->admin()->create();

        LogAktivitas::create([
            'pengguna_id' => $staf->id,
            'aksi' => 'pengguna.baru',
            'keterangan' => 'Pengguna uji ditambahkan',
            'waktu' => now(),
        ]);

        $this->actingAs($staf)->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Administrasi')
            ->assertDontSee('Akun Pengguna')
            ->assertDontSee('Total akun')
            ->assertDontSee('Log Terbaru')
            ->assertDontSee('Kelola pengguna')
            ->assertDontSee('Pengguna uji ditambahkan')
            ->assertDontSee(route('pengguna.index'))
            ->assertDontSee(route('pengaturan.edit'));
    }

    public function test_admin_melihat_antrean_pimpinan_dan_tu_tanpa_pengulangan(): void
    {
        $tu = User::factory()->staf()->create();
        $admin = User::factory()->admin()->create();

        $menungguAcc = $this->suratMasuk($tu);
        $sudahAcc = $this->suratKeluar($tu, ['status' => 'disetujui']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Surat masuk menunggu ACC')
            ->assertSee($menungguAcc->perihal)
            ->assertSee('Sudah di-ACC, belum dikirim')
            ->assertSee($sudahAcc->hal)
            ->assertDontSee('Belum diberi disposisi');
    }

    public function test_dashboard_tidak_menjalankan_query_berlebihan(): void
    {
        $tu = User::factory()->staf()->create();
        $pimpinan = User::factory()->staf()->create();
        $unit = $this->unit('UMPEG');

        foreach (range(1, 5) as $i) {
            $surat = $this->suratMasuk($tu);
            $this->disposisi($surat, $pimpinan, [$unit], ['tgl_penyelesaian' => now()->addDays($i)]);
        }

        $staf = User::factory()->create(['unit_id' => $unit->id]);

        DB::enableQueryLog();
        $this->actingAs($staf)->get(route('dashboard'))->assertOk();
        $jumlah = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(25, $jumlah, "Dashboard menjalankan {$jumlah} query.");
    }

    public function test_ringkasan_hanya_menghitung_surat_bulan_berjalan(): void
    {
        $tu = User::factory()->staf()->create();

        $this->suratMasuk($tu, ['tgl_diterima' => now()->toDateString()]);
        $this->suratMasuk($tu, ['tgl_diterima' => now()->subMonths(2)->toDateString()]);

        $response = $this->actingAs($tu)->get(route('dashboard'))->assertOk();
        $ringkasan = $response->viewData('ringkasan');

        $this->assertSame(1, $ringkasan['masuk']);
        $this->assertStringContainsString('Bulan ini:', $response->getContent());
    }
}
