<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\User;
use App\Notifications\DisposisiBaru;
use App\Services\KartuDisposisiService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuatData;
use Tests\TestCase;

class AccDisposisiTest extends TestCase
{
    use BuatData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        Notification::fake();
    }

    public function test_pimpinan_acc_membuat_disposisi_dan_pdf_kartu(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $penerima = User::factory()->create(['unit_id' => $unit->id]);
        $surat = $this->suratMasuk($tu);

        $this->actingAs($pimpinan)
            ->post(route('disposisi.acc', $surat), [
                'indeks' => '400.7',
                'tgl_penyelesaian' => now()->addDays(3)->toDateString(),
                'instruksi' => 'Koordinasikan dan laporkan hasilnya.',
                'tujuan' => [$unit->id],
            ])
            ->assertRedirect(route('surat-masuk.show', $surat));

        $this->assertSame('didisposisi', $surat->fresh()->status);

        $disposisi = Disposisi::where('surat_masuk_id', $surat->id)->firstOrFail();
        $this->assertSame('400.7', $disposisi->indeks);
        $this->assertSame($pimpinan->id, $disposisi->dibuat_oleh);
        $this->assertCount(1, $disposisi->tujuan);

        $disposisi->refresh();
        $this->assertNotNull($disposisi->kartu_path);
        Storage::disk('local')->assertExists($disposisi->kartu_path);
        $this->assertStringStartsWith('%PDF', Storage::disk('local')->get($disposisi->kartu_path));

        Notification::assertSentTo($penerima, DisposisiBaru::class);
    }

    public function test_administrator_tidak_dapat_meng_acc_surat_masuk(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $admin = User::factory()->admin()->create();
        $unit = $this->unit('UMPEG');
        $surat = $this->suratMasuk($tu);

        $this->actingAs($admin)
            ->post(route('disposisi.acc', $surat), [
                'indeks' => '400.7',
                'tgl_penyelesaian' => now()->addDays(3)->toDateString(),
                'instruksi' => 'Tindak lanjuti dan laporkan.',
                'tujuan' => [$unit->id],
            ])
            ->assertForbidden();

        $this->assertSame('diterima', $surat->fresh()->status);
        $this->assertSame(0, Disposisi::where('surat_masuk_id', $surat->id)->count());
    }

    public function test_form_acc_terisi_data_surat(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $surat = $this->suratMasuk($tu, ['perihal' => 'Undangan Rapat Koordinasi']);

        $this->actingAs($pimpinan)
            ->get(route('disposisi.acc.form', $surat))
            ->assertOk()
            ->assertSee('Undangan Rapat Koordinasi')
            ->assertSee($surat->no_surat)
            ->assertSee($this->instansi()->nama);
    }

    public function test_pimpinan_boleh_acc(): void
    {
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $pencatat = User::factory()->petugasTu()->create();
        $surat = $this->suratMasuk($pencatat);

        $this->actingAs($pimpinan)->get(route('disposisi.acc.form', $surat))->assertOk();
        $this->actingAs($pimpinan)->post(route('disposisi.acc', $surat), [
            'indeks' => '400.7',
            'tgl_penyelesaian' => now()->addDays(2)->toDateString(),
            'instruksi' => 'Tindak lanjuti.',
            'tujuan' => [$unit->id],
        ])->assertSessionHasNoErrors();

        $this->assertSame('didisposisi', $surat->fresh()->status);
    }

    public function test_kartu_pdf_dapat_diunduh_operator_dan_unit_tujuan(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit('UMPEG');
        $surat = $this->suratMasuk($tu);
        $disposisi = $this->disposisi($surat, $pimpinan, [$unit]);
        app(KartuDisposisiService::class)->simpan($disposisi);

        $this->actingAs($tu)->get(route('disposisi.pdf', $disposisi))
            ->assertOk()->assertHeader('content-type', 'application/pdf');

        $penerima = User::factory()->create(['unit_id' => $unit->id]);
        $this->actingAs($penerima)->get(route('disposisi.pdf', $disposisi))->assertOk();

        $admin = User::factory()->admin()->create();
        $this->actingAs($admin)->get(route('disposisi.pdf', $disposisi))->assertOk();
    }

    public function test_nama_berkas_pdf_kartu_mengikuti_isi_lembar(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit();
        $surat = $this->suratMasuk($tu, ['perihal' => 'Undangan Rapat Koordinasi']);
        $disposisi = $this->disposisi($surat, $pimpinan, [$unit]);
        app(KartuDisposisiService::class)->simpan($disposisi);

        $nama = $disposisi->fresh()->namaBerkasKartu();

        $this->assertSame(
            'Kartu Disposisi - '.str_replace('/', '_', $surat->no_agenda)
                .' - Undangan Rapat Koordinasi - '.$disposisi->tgl_disposisi->format('Y-m-d').'.pdf',
            $nama
        );
        $this->assertStringNotContainsString('/', $nama);

        $this->actingAs($tu)->get(route('disposisi.pdf', $disposisi))
            ->assertOk()
            ->assertDownload($nama);
    }

    public function test_cetak_massal_menghasilkan_satu_pdf(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit();

        $satu = $this->disposisi($this->suratMasuk($tu), $pimpinan, [$unit]);
        $dua = $this->disposisi($this->suratMasuk($tu), $pimpinan, [$unit]);

        $response = $this->actingAs($pimpinan)->get(route('disposisi.cetak', [
            'ids' => [$satu->id, $dua->id],
        ]));

        $response->assertOk()->assertHeader('content-type', 'application/pdf');

        $isi = method_exists($response->baseResponse, 'streamedContent')
            ? $response->streamedContent()
            : $response->getContent();

        $this->assertStringStartsWith('%PDF', $isi);
    }

    public function test_notifikasi_disposisi_melampirkan_pdf_kartu(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $unit = $this->unit();
        $surat = $this->suratMasuk($tu);

        $this->actingAs($pimpinan)->post(route('disposisi.acc', $surat), [
            'indeks' => '400',
            'tgl_penyelesaian' => now()->addDay()->toDateString(),
            'instruksi' => 'Tindak lanjuti.',
            'tujuan' => [$unit->id],
        ]);

        $disposisi = Disposisi::where('surat_masuk_id', $surat->id)->firstOrFail();
        $mail = (new DisposisiBaru($disposisi->load('pembuat', 'tujuan.unit')))
            ->toMail(User::factory()->create(['unit_id' => $unit->id]));

        $this->assertCount(1, $mail->attachments);
        $this->assertSame('application/pdf', $mail->attachments[0]['options']['mime']);
        $this->assertSame($disposisi->kartu_nama, $mail->attachments[0]['options']['as']);
    }

    public function test_pimpinan_acc_surat_keluar_membuat_kartu_untuk_tu(): void
    {
        $unitTu = $this->unit('TU');
        $tu = User::factory()->petugasTu()->create(['unit_id' => $unitTu->id]);
        $pimpinan = User::factory()->pimpinan()->create();
        $surat = $this->suratKeluar($tu, ['status' => 'menunggu_ttd']);

        $this->actingAs($pimpinan)->post(route('surat-keluar.acc', $surat))
            ->assertSessionHasNoErrors();

        $surat->refresh();
        $this->assertSame('disetujui', $surat->status);
        $this->assertSame($pimpinan->id, $surat->penandatangan_id);

        $disposisi = Disposisi::where('surat_keluar_id', $surat->id)->firstOrFail();
        $this->assertNotNull($disposisi->kartu_path);
        $this->assertSame('Sampaikan surat kepada tujuan, catat pengiriman, lalu arsipkan.', $disposisi->instruksi);
        Storage::disk('local')->assertExists($disposisi->kartu_path);

        $this->actingAs($tu)->get(route('disposisi.kartu', $disposisi))
            ->assertOk()
            ->assertSee($surat->hal)
            ->assertSee('KARTU DISPOSISI');
    }

    public function test_surat_keluar_yang_sudah_disetujui_tidak_bisa_di_acc_lagi(): void
    {
        $tu = User::factory()->petugasTu()->create();
        $pimpinan = User::factory()->pimpinan()->create();
        $surat = $this->suratKeluar($tu, ['status' => 'disetujui']);

        $this->actingAs($pimpinan)->post(route('surat-keluar.acc', $surat))->assertForbidden();
    }
}
