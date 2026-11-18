<?php

namespace Tests\Feature;

use App\Models\Disposisi;
use App\Models\DisposisiTujuan;
use App\Models\Lampiran;
use App\Models\SuratKeluar;
use App\Models\SuratMasuk;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\Concerns\BuatData;
use Tests\TestCase;

class HapusPermanenTest extends TestCase
{
    use RefreshDatabase, BuatData;

    public function test_hapus_permanen_surat_masuk_membersihkan_lampiran(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $staf = User::factory()->staf()->create();
        $surat = $this->suratMasuk($staf);

        Storage::disk('local')->put('lampiran/masuk/scan.pdf', 'isi');
        $lampiran = $surat->lampiran()->create([
            'nama_file' => 'scan.pdf',
            'path' => 'lampiran/masuk/scan.pdf',
            'jenis' => 'scan',
            'diunggah_oleh' => $staf->id,
        ]);

        $this->actingAs($admin)
            ->get(route('surat-masuk.show', $surat))
            ->assertSee('Hapus Permanen…')
            ->assertSee('Zona berbahaya');

        $this->actingAs($admin)
            ->get(route('surat-masuk.hapus-permanen.form', $surat))
            ->assertOk()
            ->assertSee($surat->no_agenda);

        // Tanpa nomor yang diketik persis, surat tidak boleh terhapus.
        $this->actingAs($admin)
            ->delete(route('surat-masuk.hapus-permanen', $surat), ['konfirmasi' => 'AGD/salah'])
            ->assertSessionHasErrors('konfirmasi');
        $this->actingAs($admin)
            ->delete(route('surat-masuk.hapus-permanen', $surat))
            ->assertSessionHasErrors('konfirmasi');
        $this->actingAs($admin)
            ->delete(route('surat-masuk.hapus-permanen', $surat), ['konfirmasi' => [$surat->no_agenda]])
            ->assertSessionHasErrors('konfirmasi');
        $this->assertNotNull(SuratMasuk::withTrashed()->find($surat->id));

        $this->actingAs($admin)
            ->delete(route('surat-masuk.hapus-permanen', $surat), ['konfirmasi' => $surat->no_agenda])
            ->assertRedirect(route('surat-masuk.index'));

        $this->assertNull(SuratMasuk::withTrashed()->find($surat->id));
        $this->assertNull(Lampiran::find($lampiran->id));
        Storage::disk('local')->assertMissing('lampiran/masuk/scan.pdf');
    }

    public function test_staf_tidak_boleh_menghapus_permanen_surat_masuk(): void
    {
        $staf = User::factory()->staf()->create();
        $surat = $this->suratMasuk($staf);

        $this->actingAs($staf)
            ->get(route('surat-masuk.show', $surat))
            ->assertDontSee('Zona berbahaya');

        $this->actingAs($staf)
            ->get(route('surat-masuk.hapus-permanen.form', $surat))
            ->assertForbidden();

        $this->actingAs($staf)
            ->delete(route('surat-masuk.hapus-permanen', $surat), ['konfirmasi' => $surat->no_agenda])
            ->assertForbidden();

        $this->assertNotNull(SuratMasuk::withTrashed()->find($surat->id));
    }

    public function test_surat_masuk_yang_sudah_didisposisi_dihapus_beserta_disposisinya(): void
    {
        $admin = User::factory()->admin()->create();
        $surat = $this->suratMasuk($admin);
        $disposisi = $this->disposisi($surat, $admin, [$this->unit('UMPEG')]);

        $this->actingAs($admin)
            ->get(route('surat-masuk.hapus-permanen.form', $surat))
            ->assertOk()
            ->assertSee('Disposisi yang ikut terhapus');

        $this->actingAs($admin)
            ->delete(route('surat-masuk.hapus-permanen', $surat), ['konfirmasi' => $surat->no_agenda])
            ->assertRedirect(route('surat-masuk.index'));

        $this->assertNull(SuratMasuk::withTrashed()->find($surat->id));
        $this->assertSame(0, Disposisi::withTrashed()->where('surat_masuk_id', $surat->id)->count());
        $this->assertSame(0, DisposisiTujuan::where('disposisi_id', $disposisi->id)->count());
    }

    public function test_hapus_permanen_surat_keluar_membersihkan_kartu_disposisi(): void
    {
        Storage::fake('local');

        $admin = User::factory()->admin()->create();
        $surat = $this->suratKeluar($admin);
        $disposisi = $surat->disposisiKartu()->create([
            'dibuat_oleh' => $admin->id,
            'instruksi' => 'Sampaikan lalu arsipkan.',
            'tgl_disposisi' => now(),
            'kartu_path' => 'kartu-disposisi/keluar.pdf',
        ]);
        Storage::disk('local')->put('kartu-disposisi/keluar.pdf', 'pdf');

        $this->actingAs($admin)
            ->get(route('surat-keluar.show', $surat))
            ->assertSee('Zona berbahaya');

        $this->actingAs($admin)
            ->delete(route('surat-keluar.hapus-permanen', $surat), ['konfirmasi' => $surat->no_surat])
            ->assertRedirect(route('surat-keluar.index'));

        $this->assertNull(SuratKeluar::withTrashed()->find($surat->id));
        $this->assertSame(0, Disposisi::withTrashed()->where('surat_keluar_id', $surat->id)->count());
        $this->assertSame(0, DisposisiTujuan::where('disposisi_id', $disposisi->id)->count());
        Storage::disk('local')->assertMissing('kartu-disposisi/keluar.pdf');
    }

    public function test_surat_keluar_yang_sudah_disetujui_dapat_dihapus_permanen(): void
    {
        $admin = User::factory()->admin()->create();
        $surat = $this->suratKeluar($admin, ['status' => 'disetujui']);

        $this->actingAs($admin)
            ->get(route('surat-keluar.show', $surat))
            ->assertSee('Zona berbahaya');

        $this->actingAs($admin)
            ->delete(route('surat-keluar.hapus-permanen', $surat), ['konfirmasi' => $surat->no_surat])
            ->assertRedirect(route('surat-keluar.index'));

        $this->assertNull(SuratKeluar::withTrashed()->find($surat->id));
    }

    public function test_staf_tidak_boleh_menghapus_permanen_surat_keluar(): void
    {
        $staf = User::factory()->staf()->create();
        $surat = $this->suratKeluar($staf);

        $this->actingAs($staf)
            ->delete(route('surat-keluar.hapus-permanen', $surat), ['konfirmasi' => $surat->no_surat])
            ->assertForbidden();

        $this->assertNotNull(SuratKeluar::withTrashed()->find($surat->id));
    }

    public function test_disposisi_yang_sudah_dibatalkan_ikut_terhapus(): void
    {
        $admin = User::factory()->admin()->create();
        $surat = $this->suratMasuk($admin);
        $disposisi = $this->disposisi($surat, $admin, [$this->unit('UMPEG')]);

        // Batal disposisi mengembalikan surat ke status diterima, sehingga boleh dihapus permanen.
        $this->actingAs($admin)->delete(route('disposisi.destroy', $disposisi));
        $this->assertSame('diterima', $surat->fresh()->status);

        $this->actingAs($admin)
            ->delete(route('surat-masuk.hapus-permanen', $surat), ['konfirmasi' => $surat->no_agenda]);

        $this->assertSame(0, Disposisi::withTrashed()->where('surat_masuk_id', $surat->id)->count());
        $this->assertSame(0, DisposisiTujuan::where('disposisi_id', $disposisi->id)->count());
    }

    public function test_surat_keluar_balasan_tetap_ada_setelah_surat_masuk_dihapus(): void
    {
        $admin = User::factory()->admin()->create();
        $masuk = $this->suratMasuk($admin);
        $keluar = $this->suratKeluar($admin, ['surat_masuk_id' => $masuk->id]);

        $this->actingAs($admin)
            ->delete(route('surat-masuk.hapus-permanen', $masuk), ['konfirmasi' => $masuk->no_agenda]);

        $this->assertNotNull($keluar->fresh());
        $this->assertNull($keluar->fresh()->surat_masuk_id);
    }
}
