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

    public function test_petugas_tu_melihat_antrean_pengelolaan_surat(): void
    {
        $tu = User::factory()->petugasTu()->create();

        $menungguDisposisi = $this->suratMasuk($tu);
        $sudahAcc = $this->suratKeluar($tu, ['status' => 'disetujui']);
        $selesai = $this->suratMasuk($tu, ['status' => 'selesai']);

        $this->actingAs($tu)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Belum diberi disposisi')
            ->assertSee($menungguDisposisi->perihal)
            ->assertSee('Sudah di-ACC, belum dikirim')
            ->assertSee($sudahAcc->hal)
            ->assertSee('Belum diarsipkan')
            ->assertSee($selesai->perihal)
            ->assertDontSee('Surat masuk menunggu ACC');
    }

    public function test_staf_melihat_disposisi_unitnya(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->staf()->unit($unit)->create();

        $surat = $this->suratMasuk($tu, ['perihal' => 'Perihal disposisi unit']);
        $this->disposisi($surat, $pimpinan, [$unit]);

        $this->actingAs($staf)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Disposisi untuk unit saya')
            ->assertSee('Perihal disposisi unit')
            ->assertDontSee('Surat masuk menunggu ACC');
    }

    public function test_pimpinan_melihat_antrean_dengan_sifat_mendesak_lebih_dulu(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();

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

    public function test_admin_melihat_ringkasan_sistem_tanpa_antrean_operasional(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $admin = User::factory()->admin()->create();

        $menungguAcc = $this->suratMasuk($tu);
        $sudahAcc = $this->suratKeluar($tu, ['status' => 'disetujui']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Akun Pengguna')
            ->assertDontSee('Surat masuk menunggu ACC')
            ->assertDontSee('Sudah di-ACC, belum dikirim')
            ->assertDontSee('Belum diarsipkan')
            ->assertDontSee($menungguAcc->perihal)
            ->assertDontSee($sudahAcc->hal);
    }

    public function test_dashboard_tidak_menjalankan_query_berlebihan(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');

        foreach (range(1, 5) as $i) {
            $surat = $this->suratMasuk($tu);
            $this->disposisi($surat, $pimpinan, [$unit], ['tgl_penyelesaian' => now()->addDays($i)]);
        }

        $staf = User::factory()->staf()->create(['unit_id' => $unit->id]);

        DB::enableQueryLog();
        $this->actingAs($staf)->get(route('dashboard'))->assertOk();
        $jumlah = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertLessThanOrEqual(25, $jumlah, "Dashboard menjalankan {$jumlah} query.");
    }

    public function test_ringkasan_terbuka_menghitung_disposisi_rahasia_unit_tujuan(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $staf = User::factory()->staf()->unit($unit)->create();
        $luar = User::factory()->staf()->unit($this->unit('ULA'))->create();

        $this->disposisi($this->suratMasuk($tu), $pimpinan, [$unit]);
        $this->disposisi(
            $this->suratMasuk($tu, ['sifat' => 'rahasia']),
            $pimpinan,
            [$unit],
        );

        // Rahasia membatasi antarunit: unit tujuan menghitung keduanya.
        $response = $this->actingAs($staf)->get(route('dashboard'))->assertOk();
        $this->assertSame(2, $response->viewData('ringkasan')['terbuka']);

        $this->assertSame(
            0,
            $this->actingAs($luar)->get(route('dashboard'))->viewData('ringkasan')['terbuka'],
        );
    }

    public function test_ringkasan_hanya_menghitung_surat_bulan_berjalan(): void
    {
        $tu = User::factory()->petugasTu()->create();

        $this->suratMasuk($tu, ['tgl_diterima' => now()->toDateString()]);
        $this->suratMasuk($tu, ['tgl_diterima' => now()->subMonths(2)->toDateString()]);

        $response = $this->actingAs($tu)->get(route('dashboard'))->assertOk();
        $ringkasan = $response->viewData('ringkasan');

        $this->assertSame(1, $ringkasan['masuk']);
        $this->assertStringContainsString('Bulan ini:', $response->getContent());
    }
}
