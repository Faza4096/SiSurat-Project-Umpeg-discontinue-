<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Memperkecil foto profil sebelum disimpan agar header setiap halaman tidak
 * memuat berkas besar. Tanpa pustaka tambahan: hanya memakai ekstensi GD.
 */
class FotoProfilService
{
    /** Sisi terpanjang foto yang disimpan, dalam piksel. */
    public const MAKS_PIKSEL = 400;

    /** Kualitas JPEG hasil olahan (0-100). */
    private const KUALITAS_JPEG = 85;

    /** Apakah server bisa mengolah gambar. */
    public function gdTersedia(): bool
    {
        return extension_loaded('gd') && function_exists('imagecreatetruecolor');
    }

    /**
     * Perkecil dan simpan foto, lalu kembalikan path relatif pada disk privat.
     *
     * @throws ValidationException bila berkas tidak dapat diolah.
     */
    public function simpan(UploadedFile $berkas): string
    {
        if (! $this->gdTersedia()) {
            throw $this->tolak('Server belum mengaktifkan ekstensi GD, sehingga foto tidak dapat diperkecil. Hubungi administrator.');
        }

        $info = @getimagesize($berkas->getRealPath());
        $jenis = $info[2] ?? null;
        [$lebar, $tinggi] = [$info[0] ?? 0, $info[1] ?? 0];

        if ($lebar < 1 || $tinggi < 1) {
            throw $this->tolak('Berkas foto tidak dapat dibaca. Coba unggah berkas lain.');
        }

        $muat = match ($jenis) {
            IMAGETYPE_JPEG => 'imagecreatefromjpeg',
            IMAGETYPE_PNG => 'imagecreatefrompng',
            IMAGETYPE_WEBP => 'imagecreatefromwebp',
            default => null,
        };

        // Format gambar yang tidak didukung diberi pesan sendiri agar pengguna
        // tahu harus mengunggah apa, bukan sekadar "berkas tidak valid".
        if ($muat === null) {
            throw $this->tolak('Format foto tidak dikenali. Unggah foto JPG, PNG, atau WEBP.');
        }

        if (! function_exists($muat)) {
            throw $this->tolak('Server ini belum mendukung format foto tersebut. Unggah foto JPG atau PNG.');
        }

        $asal = @$muat($berkas->getRealPath());

        if ($asal === false) {
            throw $this->tolak('Berkas foto tidak dapat dibaca. Coba unggah berkas lain.');
        }

        // Foto kamera ponsel sering menyimpan arah putar di EXIF; tanpa ini
        // hasil perkecilan tampil miring. Dimensi diambil ulang setelah diputar.
        $asal = $this->putarSesuaiOrientasi($asal, $berkas->getRealPath(), $jenis);
        $lebar = imagesx($asal);
        $tinggi = imagesy($asal);

        $skala = min(1, self::MAKS_PIKSEL / max($lebar, $tinggi));
        $lebarBaru = max(1, (int) round($lebar * $skala));
        $tinggiBaru = max(1, (int) round($tinggi * $skala));

        $kanvas = imagecreatetruecolor($lebarBaru, $tinggiBaru);

        // PNG dan WEBP boleh transparan; isi kanvas dengan alpha penuh dulu.
        if ($jenis === IMAGETYPE_PNG || $jenis === IMAGETYPE_WEBP) {
            imagealphablending($kanvas, false);
            imagesavealpha($kanvas, true);
            imagefilledrectangle($kanvas, 0, 0, $lebarBaru, $tinggiBaru, imagecolorallocatealpha($kanvas, 0, 0, 0, 127));
        }

        imagecopyresampled($kanvas, $asal, 0, 0, 0, 0, $lebarBaru, $tinggiBaru, $lebar, $tinggi);
        imagedestroy($asal);

        $sementara = tempnam(sys_get_temp_dir(), 'foto-profil-');
        $ekstensi = $jenis === IMAGETYPE_PNG ? 'png' : ($jenis === IMAGETYPE_WEBP ? 'webp' : 'jpg');

        try {
            $berhasil = match ($jenis) {
                IMAGETYPE_PNG => imagepng($kanvas, $sementara, 7),
                IMAGETYPE_WEBP => imagewebp($kanvas, $sementara, self::KUALITAS_JPEG),
                default => imagejpeg($kanvas, $sementara, self::KUALITAS_JPEG),
            };

            if (! $berhasil) {
                throw $this->tolak('Foto tidak dapat diperkecil. Coba unggah berkas lain.');
            }

            $path = 'foto-profil/' . Str::random(40) . '.' . $ekstensi;
            Storage::put($path, file_get_contents($sementara));
        } finally {
            imagedestroy($kanvas);
            @unlink($sementara);
        }

        return $path;
    }

    /**
     * Putar gambar mengikuti tag Orientation EXIF (1-8). Nilai yang tidak
     * dikenal atau gagal diputar dibiarkan apa adanya agar unggahan tetap jalan.
     */
    private function putarSesuaiOrientasi($gambar, string $path, ?int $jenis)
    {
        if ($jenis !== IMAGETYPE_JPEG || ! function_exists('exif_read_data')) {
            return $gambar;
        }

        $orientasi = (int) (@exif_read_data($path)['Orientation'] ?? 1);

        // imagerotate menghitung sudut berlawanan arah jarum jam.
        $putaran = match ($orientasi) {
            3 => 180,
            6 => 270,
            8 => 90,
            default => 0,
        };

        if ($putaran === 0) {
            return $gambar;
        }

        $diputar = @imagerotate($gambar, $putaran, 0);

        if ($diputar === false) {
            return $gambar;
        }

        imagedestroy($gambar);

        return $diputar;
    }

    private function tolak(string $pesan): ValidationException
    {
        return ValidationException::withMessages(['foto' => $pesan]);
    }
}
