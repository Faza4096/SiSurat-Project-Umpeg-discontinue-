<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\DisposisiTujuan;
use App\Models\SuratKeluar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuatData;
use Tests\TestCase;

/**
 * Staf/Umpeg di unit tujuan boleh membuka surat induk, kartu disposisi, dan
 * bukti tindak lanjutnya, sedangkan staf unit lain ditolak. Sifat rahasia
 * membatasi antarunit, bukan memblokir staf unit tujuan.
 */
class AksesDisposisiPerUnitTest extends TestCase
{
    use BuatData, RefreshDatabase;

    private User $operator;

    private User $pegawaiTujuan;

    private User $pegawaiUnitLain;

    private Disposisi $disposisi;

    private DisposisiTujuan $tujuan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $unitTujuan = $this->unit('UMPEG');

        $this->operator = User::factory()->petugasTu()->create();
        $this->pegawaiTujuan = User::factory()->staf()->unit($unitTujuan)->create();
        $this->pegawaiUnitLain = User::factory()->staf()->unit($this->unit('ULA'))->create();

        $this->disposisi = $this->disposisi($this->suratMasuk($this->operator), $this->operator, [$unitTujuan]);
        $this->tujuan = $this->disposisi->tujuan()->first();

        Storage::disk('local')->put('disposisi/bukti/uji.jpg', 'isi bukti');
        $this->tujuan->update([
            'bukti_path' => 'disposisi/bukti/uji.jpg',
            'bukti_nama' => 'uji.jpg',
        ]);

        Storage::disk('local')->put('kartu-disposisi/kartu.pdf', '%PDF-1.4 uji');
        $this->disposisi->update([
            'kartu_path' => 'kartu-disposisi/kartu.pdf',
            'kartu_nama' => 'kartu.pdf',
        ]);
    }

    public function test_pegawai_unit_tujuan_membuka_kartu_dan_bukti(): void
    {
        $this->actingAs($this->pegawaiTujuan)
            ->get(route('disposisi.kartu', $this->disposisi))
            ->assertOk();

        $this->actingAs($this->pegawaiTujuan)
            ->get(route('tujuan.bukti.unduh', $this->tujuan))
            ->assertOk();
    }

    public function test_pegawai_unit_tujuan_melihat_surat_masuk_induknya(): void
    {
        $this->actingAs($this->pegawaiTujuan)
            ->get(route('surat-masuk.show', $this->disposisi->suratMasuk))
            ->assertOk();
    }

    public function test_pegawai_unit_lain_tetap_ditolak(): void
    {
        $this->actingAs($this->pegawaiUnitLain)
            ->get(route('disposisi.kartu', $this->disposisi))
            ->assertForbidden();

        $this->actingAs($this->pegawaiUnitLain)
            ->get(route('tujuan.bukti.unduh', $this->tujuan))
            ->assertForbidden();

        $this->actingAs($this->pegawaiUnitLain)
            ->get(route('surat-masuk.show', $this->disposisi->suratMasuk))
            ->assertForbidden();
    }

    public function test_pegawai_unit_tujuan_membuka_kartu_disposisi_surat_keluar(): void
    {
        $suratKeluar = $this->suratKeluar($this->operator);
        $disposisi = Disposisi::create([
            'surat_keluar_id' => $suratKeluar->id,
            'dibuat_oleh' => $this->operator->id,
            'instruksi' => 'Tindak lanjuti.',
            'tgl_disposisi' => now(),
        ]);
        $disposisi->tujuan()->create(['unit_id' => $this->unit('UMPEG')->id]);

        Storage::disk('local')->put('kartu-disposisi/keluar.pdf', '%PDF-1.4 uji');
        $disposisi->update(['kartu_path' => 'kartu-disposisi/keluar.pdf', 'kartu_nama' => 'keluar.pdf']);

        // Unit tujuan disposisi boleh membuka kartu walau bukan unit pembuat surat.
        $this->actingAs($this->pegawaiTujuan)
            ->get(route('disposisi.kartu', $disposisi))
            ->assertOk();
    }

    public function test_pegawai_unit_pembuat_surat_keluar_melihat_suratnya(): void
    {
        $suratKeluar = $this->suratKeluar($this->operator);
        $pegawaiTu = User::factory()->staf()->unit($this->unit('TU'))->create();

        $this->actingAs($pegawaiTu)
            ->get(route('surat-keluar.show', $suratKeluar))
            ->assertOk();
    }

    public function test_surat_masuk_rahasia_terbuka_bagi_staf_unit_tujuan(): void
    {
        $rahasia = $this->suratMasuk($this->operator, [
            'sifat' => 'rahasia',
            'perihal' => 'Perihal sangat rahasia',
        ]);
        $this->disposisi($rahasia, $this->operator, [$this->unit('UMPEG')]);

        $this->actingAs($this->pegawaiTujuan)
            ->get(route('surat-masuk.show', $rahasia))
            ->assertOk();

        $this->actingAs($this->pegawaiTujuan)
            ->get(route('surat-masuk.index'))
            ->assertSee('Perihal sangat rahasia');
    }

    public function test_surat_masuk_rahasia_tertutup_bagi_staf_unit_lain(): void
    {
        $rahasia = $this->suratMasuk($this->operator, [
            'sifat' => 'rahasia',
            'perihal' => 'Perihal sangat rahasia',
        ]);
        $this->disposisi($rahasia, $this->operator, [$this->unit('UMPEG')]);

        $this->actingAs($this->pegawaiUnitLain)
            ->get(route('surat-masuk.show', $rahasia))
            ->assertForbidden();

        $this->actingAs($this->pegawaiUnitLain)
            ->get(route('surat-masuk.index'))
            ->assertDontSee('Perihal sangat rahasia');
    }

    public function test_semua_jalur_disposisi_surat_rahasia_terbuka_bagi_unit_tujuan(): void
    {
        $rahasia = $this->suratMasuk($this->operator, [
            'sifat' => 'rahasia',
            'perihal' => 'Perihal sangat rahasia',
        ]);
        $disposisi = $this->disposisi($rahasia, $this->operator, [$this->unit('UMPEG')]);
        $tujuan = $disposisi->tujuan()->first();

        Storage::disk('local')->put('kartu-disposisi/rahasia.pdf', '%PDF-1.4 uji');
        $disposisi->update(['kartu_path' => 'kartu-disposisi/rahasia.pdf', 'kartu_nama' => 'rahasia.pdf']);

        Storage::disk('local')->put('disposisi/bukti/rahasia.jpg', 'isi bukti');
        $tujuan->update(['bukti_path' => 'disposisi/bukti/rahasia.jpg', 'bukti_nama' => 'rahasia.jpg']);

        $this->actingAs($this->pegawaiTujuan);

        $this->get(route('disposisi.kartu', $disposisi))->assertOk();
        $this->get(route('disposisi.pdf', $disposisi))->assertOk();
        $this->get(route('disposisi.cetak', ['ids' => [$disposisi->id]]))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('tujuan.bukti.unduh', $tujuan))->assertOk();

        $this->patch(route('tujuan.status', $tujuan), ['status' => 'diproses'])->assertSessionHasNoErrors();
        $this->post(route('tujuan.bukti', $tujuan), [
            'bukti' => UploadedFile::fake()->create('tindak-lanjut.pdf', 120, 'application/pdf'),
        ])->assertSessionHasNoErrors();
        $this->delete(route('tujuan.bukti.hapus', $tujuan))->assertSessionHasNoErrors();

        $this->assertSame('diproses', $tujuan->fresh()->status_tindak_lanjut);
    }

    public function test_jalur_disposisi_surat_rahasia_tertutup_bagi_unit_lain(): void
    {
        $rahasia = $this->suratMasuk($this->operator, ['sifat' => 'rahasia']);
        $disposisi = $this->disposisi($rahasia, $this->operator, [$this->unit('UMPEG')]);
        $tujuan = $disposisi->tujuan()->first();

        $this->actingAs($this->pegawaiUnitLain);

        $this->get(route('disposisi.kartu', $disposisi))->assertForbidden();
        $this->get(route('disposisi.pdf', $disposisi))->assertForbidden();
        $this->get(route('disposisi.cetak', ['ids' => [$disposisi->id]]))->assertForbidden();
        $this->get(route('tujuan.bukti.unduh', $tujuan))->assertForbidden();
        $this->patch(route('tujuan.status', $tujuan), ['status' => 'diproses'])->assertForbidden();

        $this->get(route('disposisi.index'))
            ->assertOk()
            ->assertDontSee($rahasia->perihal);
    }

    public function test_surat_keluar_rahasia_terbuka_bagi_staf_unit_pembuat(): void
    {
        $rahasia = $this->suratKeluar($this->operator, ['sifat' => 'rahasia']);
        $disposisi = Disposisi::create([
            'surat_keluar_id' => $rahasia->id,
            'dibuat_oleh' => $this->operator->id,
            'instruksi' => 'Tindak lanjuti.',
            'tgl_disposisi' => now(),
        ]);

        Storage::disk('local')->put('kartu-disposisi/keluar-rahasia.pdf', '%PDF-1.4 uji');
        $disposisi->update(['kartu_path' => 'kartu-disposisi/keluar-rahasia.pdf', 'kartu_nama' => 'keluar-rahasia.pdf']);

        $pegawaiTu = User::factory()->staf()->unit($this->unit('TU'))->create();

        $this->actingAs($pegawaiTu)
            ->get(route('surat-keluar.show', $rahasia))
            ->assertOk();

        $this->actingAs($this->pegawaiUnitLain)
            ->get(route('surat-keluar.show', $rahasia))
            ->assertForbidden();
    }

    public function test_petugas_tu_tetap_membuka_kartu_disposisi_surat_rahasia(): void
    {
        $rahasia = $this->suratMasuk($this->operator, ['sifat' => 'rahasia']);
        $disposisi = $this->disposisi($rahasia, $this->operator, [$this->unit('UMPEG')]);

        Storage::disk('local')->put('kartu-disposisi/staf.pdf', '%PDF-1.4 uji');
        $disposisi->update(['kartu_path' => 'kartu-disposisi/staf.pdf', 'kartu_nama' => 'staf.pdf']);

        $this->actingAs($this->operator)
            ->get(route('disposisi.kartu', $disposisi))
            ->assertOk();

        $this->actingAs($this->operator)
            ->get(route('disposisi.pdf', $disposisi))
            ->assertOk();
    }
}
