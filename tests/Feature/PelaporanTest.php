<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\BuatData;
use Tests\TestCase;

class PelaporanTest extends TestCase
{
    use RefreshDatabase, BuatData;

    public function test_laporan_surat_dapat_difilter(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $surat = $this->suratMasuk($tu);
        $lain = $this->suratMasuk($tu, ['instansi_id' => $this->instansi('Dinas Kesehatan Kab. Bogor')->id]);

        $this->actingAs($tu)
            ->get(route('laporan.index', ['jenis' => 'masuk', 'instansi_id' => $surat->instansi_id]))
            ->assertOk()
            ->assertSee($surat->perihal)
            ->assertDontSee($lain->perihal);
    }

    public function test_ekspor_excel_menghasilkan_csv(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $surat = $this->suratMasuk($tu);

        $response = $this->actingAs($tu)->get(route('laporan.ekspor', ['jenis' => 'masuk']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));

        $isi = $response->streamedContent();
        $this->assertStringContainsString($surat->no_agenda, $isi);
        $this->assertStringContainsString('No. Agenda', $isi);
    }

    public function test_ekspor_pdf_menghasilkan_berkas_pdf(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $this->suratMasuk($tu);

        $response = $this->actingAs($tu)->get(route('laporan.pdf', ['jenis' => 'masuk']));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringStartsWith('%PDF', $response->getContent());
    }

    public function test_laporan_tindak_lanjut_menampilkan_status_unit(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $surat = $this->suratMasuk($tu);
        $this->disposisi($surat, $pimpinan, [$unit]);

        $this->actingAs($tu)
            ->get(route('laporan.tindak-lanjut'))
            ->assertOk()
            ->assertSee($surat->no_agenda)
            ->assertSee($unit->nama);
    }

    public function test_laporan_tindak_lanjut_dibatasi_unit_pengguna_bagi_non_operator(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unitSendiri = $this->unit('UMPEG');
        $unitLain = $this->unit('ULA');
        $pegawai = User::factory()->staf()->unit($unitSendiri)->create();

        $milikSendiri = $this->suratMasuk($tu, ['perihal' => 'Perihal unit sendiri']);
        $milikLain = $this->suratMasuk($tu, ['perihal' => 'Perihal unit lain']);

        $this->disposisi($milikSendiri, $pimpinan, [$unitSendiri]);
        $this->disposisi($milikLain, $pimpinan, [$unitLain]);

        $this->actingAs($pegawai)
            ->get(route('laporan.tindak-lanjut'))
            ->assertOk()
            ->assertSee('Perihal unit sendiri')
            ->assertDontSee('Perihal unit lain')
            ->assertDontSee($unitLain->nama);

        // Operator tetap melihat tindak lanjut seluruh unit.
        $this->actingAs($tu)
            ->get(route('laporan.tindak-lanjut'))
            ->assertOk()
            ->assertSee('Perihal unit sendiri')
            ->assertSee('Perihal unit lain')
            ->assertSee($unitLain->nama);
    }

    public function test_laporan_tindak_lanjut_surat_rahasia_mengikuti_unit_tujuan(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $pegawai = User::factory()->staf()->unit($unit)->create();
        $luar = User::factory()->staf()->unit($this->unit('ULA'))->create();

        $biasa = $this->suratMasuk($tu, ['perihal' => 'Perihal biasa terlihat']);
        $rahasia = $this->suratMasuk($tu, [
            'perihal' => 'Perihal rahasia tersembunyi',
            'sifat' => 'rahasia',
        ]);

        $this->disposisi($biasa, $pimpinan, [$unit]);
        $this->disposisi($rahasia, $pimpinan, [$unit]);

        // Staf unit tujuan menindaklanjuti keduanya, termasuk yang rahasia.
        $this->actingAs($pegawai)
            ->get(route('laporan.tindak-lanjut'))
            ->assertOk()
            ->assertSee('Perihal biasa terlihat')
            ->assertSee('Perihal rahasia tersembunyi');

        // Unit lain tidak melihat baris tersebut.
        $this->actingAs($luar)
            ->get(route('laporan.tindak-lanjut'))
            ->assertOk()
            ->assertDontSee('Perihal biasa terlihat')
            ->assertDontSee('Perihal rahasia tersembunyi');

        $this->actingAs($pimpinan)
            ->get(route('laporan.tindak-lanjut'))
            ->assertOk()
            ->assertSee('Perihal rahasia tersembunyi');
    }
}
