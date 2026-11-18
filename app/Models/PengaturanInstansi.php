<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * Identitas instansi (satu baris). Seluruh halaman membaca nama dan unit dari
 * sini agar tidak ada teks instansi yang ditulis ulang di view.
 */
class PengaturanInstansi extends Model
{
    protected $table = 'pengaturan_instansi';

    protected $fillable = [
        'nama', 'unit', 'alamat', 'telepon', 'email', 'website', 'logo_path',
        'pejabat_nama', 'pejabat_jabatan',
    ];

    /** Selalu ada satu baris pengaturan. */
    public static function current(): self
    {
        return static::query()->first() ?? static::create([
            'nama' => config('sisurat.instansi.nama'),
            'unit' => config('sisurat.instansi.unit'),
            'logo_path' => 'img/lambang-kabupaten-bogor.png',
        ]);
    }

    /** Unit kerja yang ditampilkan di bawah nama instansi; config hanya cadangan. */
    public function namaUnit(): string
    {
        return $this->unit ?: config('sisurat.instansi.unit');
    }

    public function namaKop(): string
    {
        return mb_strtoupper($this->nama ?: config('sisurat.instansi.nama'));
    }

    public function alamatLengkap(): string
    {
        return collect([$this->alamat, $this->telepon ? 'Telp. ' . $this->telepon : null, $this->website])
            ->filter()
            ->implode(' · ');
    }

    /** Logo dikirim sebagai data URI agar tidak bergantung pada path file saat render PDF. */
    public function logoDataUri(): ?string
    {
        $path = $this->logo_path;

        if (! $path) {
            return null;
        }

        $absolut = null;

        if (file_exists(public_path($path))) {
            $absolut = public_path($path);
        } elseif (Storage::exists($path)) {
            $absolut = Storage::path($path);
        }

        if (! $absolut) {
            return null;
        }

        $mime = match (strtolower(pathinfo($absolut, PATHINFO_EXTENSION))) {
            'svg' => 'image/svg+xml',
            'jpg', 'jpeg' => 'image/jpeg',
            'webp' => 'image/webp',
            default => 'image/png',
        };

        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($absolut));
    }
}
