<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\DisposisiTujuan;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuatData;
use Tests\TestCase;

/**
 * Berkas surat berada di disk privat dan hanya boleh diunduh lewat rute
 * ber-otorisasi. Staf unit lain tidak boleh membuka berkas surat mana pun.
 */
class OtorisasiUnduhanBerkasTest extends TestCase
{
    use BuatData, RefreshDatabase;

    private User $operator;

    private User $orangLuar;

    private User $pegawaiUnitTujuan;

    private SuratMasuk $suratMasuk;

    private SuratKeluar $suratKeluar;

    private Disposisi $disposisi;

    private DisposisiTujuan $tujuan;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');

        $unitTujuan = $this->unit('UMPEG');

        $this->operator = User::factory()->petugasTu()->create();
        $this->orangLuar = User::factory()->staf()->unit($this->unit('ULA'))->create();
        $this->pegawaiUnitTujuan = User::factory()->staf()->unit($unitTujuan)->create();

        $this->suratMasuk = $this->suratMasuk($this->operator);
        Storage::disk('local')->put('lampiran/uji.pdf', 'isi lampiran');
        $this->suratMasuk->lampiran()->create([
            'nama_file' => 'uji.pdf',
            'path' => 'lampiran/uji.pdf',
            'jenis' => 'scan',
            'diunggah_oleh' => $this->operator->id,
        ]);

        Storage::disk('local')->put('surat-keluar/final.pdf', 'isi surat final');
        $this->suratKeluar = $this->suratKeluar($this->operator, [
            'status' => 'disetujui',
            'file_final_path' => 'surat-keluar/final.pdf',
            'file_final_nama' => 'final.pdf',
        ]);

        $this->disposisi = $this->disposisi($this->suratMasuk, $this->operator, [$unitTujuan]);
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

    public function test_akun_staf_unit_lain_tidak_bisa_mengunduh_berkas_surat(): void
    {
        $this->actingAs($this->orangLuar);

        foreach ($this->tautanBerkas() as $nama => $tautan) {
            $this->get($tautan)->assertForbidden();
        }
    }

    public function test_petugas_tu_bisa_mengunduh_berkas_yang_berhak(): void
    {
        $this->actingAs($this->operator);

        foreach ($this->tautanBerkas() as $nama => $tautan) {
            $this->get($tautan)->assertOk();
        }
    }

    public function test_pegawai_unit_tujuan_boleh_mengunduh_kartu_disposisi(): void
    {
        $this->actingAs($this->pegawaiUnitTujuan)
            ->get(route('disposisi.pdf', $this->disposisi))
            ->assertOk();
    }

    public function test_tamu_diarahkan_ke_halaman_masuk(): void
    {
        foreach ($this->tautanBerkas() as $nama => $tautan) {
            $this->get($tautan)->assertRedirect(route('login'));
        }
    }

    /** @return array<string, string> */
    private function tautanBerkas(): array
    {
        return [
            'lampiran surat masuk' => route('lampiran.unduh', $this->suratMasuk->lampiran()->first()),
            'file final surat keluar' => route('surat-keluar.file-final', $this->suratKeluar),
            'bukti tindak lanjut' => route('tujuan.bukti.unduh', $this->tujuan),
            'pdf kartu disposisi' => route('disposisi.pdf', $this->disposisi),
        ];
    }
}
