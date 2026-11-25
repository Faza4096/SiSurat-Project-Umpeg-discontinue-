<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\FotoProfilService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FotoProfilTest extends TestCase
{
    use RefreshDatabase;

    private function unggahFoto(User $user, UploadedFile $berkas)
    {
        return $this->actingAs($user)->post('/profile/foto', ['foto' => $berkas]);
    }

    /** JPEG 400x300 dengan tag EXIF Orientation tertentu, seperti foto kamera. */
    private function berkasJpegBerorientasi(int $orientasi): UploadedFile
    {
        $gambar = imagecreatetruecolor(400, 300);
        imagefilledrectangle($gambar, 0, 0, 400, 300, imagecolorallocate($gambar, 180, 60, 40));

        $path = tempnam(sys_get_temp_dir(), 'exif-');
        imagejpeg($gambar, $path, 90);
        imagedestroy($gambar);

        $jpeg = file_get_contents($path);
        file_put_contents($path, substr($jpeg, 0, 2) . $this->segmenExif($orientasi) . substr($jpeg, 2));

        return new UploadedFile($path, 'kamera.jpg', 'image/jpeg', null, true);
    }

    /** Sisipan APP1 berisi TIFF minimal: satu tag Orientation. */
    private function segmenExif(int $orientasi): string
    {
        $tiff = "II\x2A\x00" . pack('V', 8)
            . pack('v', 1)
            . pack('v', 0x0112) . pack('v', 3) . pack('V', 1) . pack('v', $orientasi) . "\x00\x00"
            . pack('V', 0);

        $payload = "Exif\x00\x00" . $tiff;

        return "\xFF\xE1" . pack('n', strlen($payload) + 2) . $payload;
    }

    public function test_unggah_foto_profil_berhasil_disimpan(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->unggahFoto($user, UploadedFile::fake()->image('foto.jpg', 240, 240))
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();
        $this->assertNotNull($user->foto_profil);
        Storage::disk('local')->assertExists($user->foto_profil);
    }

    public function test_foto_profil_dapat_ditampilkan(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->unggahFoto($user, UploadedFile::fake()->image('foto.png', 240, 240))
            ->assertSessionHasNoErrors();

        $this->actingAs($user)
            ->get('/foto-profil/' . $user->id)
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png');
    }

    public function test_hapus_foto_profil_membuang_berkasnya(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->unggahFoto($user, UploadedFile::fake()->image('foto.jpg', 240, 240))
            ->assertSessionHasNoErrors();

        $path = $user->fresh()->foto_profil;
        Storage::disk('local')->assertExists($path);

        $this->actingAs($user)
            ->delete('/profile/foto')
            ->assertRedirect('/profile');

        $this->assertNull($user->fresh()->foto_profil);
        Storage::disk('local')->assertMissing($path);
        $this->actingAs($user)->get('/foto-profil/' . $user->id)->assertNotFound();
    }

    public function test_foto_diperkecil_maksimal_400_piksel(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->unggahFoto($user, UploadedFile::fake()->image('besar.jpg', 1200, 900))
            ->assertSessionHasNoErrors();

        $path = $user->fresh()->foto_profil;
        [$lebar, $tinggi] = getimagesize(Storage::disk('local')->path($path));

        $this->assertSame(FotoProfilService::MAKS_PIKSEL, $lebar);
        $this->assertSame(300, $tinggi);
    }

    public function test_foto_kamera_diputar_sesuai_orientasi_exif(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $berkas = $this->berkasJpegBerorientasi(6);

        // Pastikan berkas uji memang membawa orientasi 6 (putar 90 derajat searah jarum jam).
        $this->assertSame(6, exif_read_data($berkas->getRealPath())['Orientation'] ?? null);

        $this->unggahFoto($user, $berkas)->assertSessionHasNoErrors();

        $path = $user->fresh()->foto_profil;
        [$lebar, $tinggi] = getimagesize(Storage::disk('local')->path($path));

        // Foto lanskap 400x300 dengan orientasi 6 harus tersimpan tegak 300x400.
        $this->assertSame(300, $lebar);
        $this->assertSame(400, $tinggi);
    }

    public function test_pesan_jelas_bila_ekstensi_gambar_tidak_tersedia(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->partialMock(FotoProfilService::class, function ($mock) {
            $mock->shouldReceive('gdTersedia')->andReturn(false);
        });

        $this->unggahFoto($user, UploadedFile::fake()->image('foto.jpg', 240, 240))
            ->assertSessionHasErrors(['foto' => 'Server belum mengaktifkan ekstensi GD, sehingga foto tidak dapat diperkecil. Hubungi administrator.']);

        $this->assertNull($user->fresh()->foto_profil);
    }

    public function test_berkas_bukan_gambar_ditolak(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->unggahFoto($user, UploadedFile::fake()->create('dokumen.pdf', 100, 'application/pdf'))
            ->assertSessionHasErrors('foto');

        $this->assertNull($user->fresh()->foto_profil);
    }

    public function test_foto_lebih_dari_dua_megabita_ditolak(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();

        $this->unggahFoto($user, UploadedFile::fake()->image('besar.jpg', 240, 240)->size(3000))
            ->assertSessionHasErrors('foto');

        $this->assertNull($user->fresh()->foto_profil);
    }
}
