<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /** Empat peran aplikasi beserta sebutannya di antarmuka. */
    public const PERAN = [
        'admin' => 'Administrator',
        'petugas_tu' => 'Petugas TU',
        'pimpinan' => 'Pimpinan',
        'staf' => 'Staf/Umpeg',
    ];

    /** Peran yang mencatat, mengirim, dan mengarsipkan surat (Petugas TU). */
    public const PERAN_TATA_USAHA = ['petugas_tu'];

    /** Peran yang memeriksa surat dan memberikan disposisi (Pimpinan). */
    public const PERAN_PIMPINAN = ['pimpinan'];

    /** Peran yang melihat seluruh surat untuk pengawasan dan tindak lanjut. */
    public const PERAN_LIHAT_SEMUA = ['admin', 'petugas_tu', 'pimpinan'];

    protected $fillable = [
        'unit_id', 'role_id', 'name', 'nip', 'jabatan', 'foto_profil', 'email', 'password',
        'must_change_password', 'aktif', 'password_diubah_pada',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'email_verified_at' => 'datetime',
            'must_change_password' => 'boolean',
            'aktif' => 'boolean',
            'password_diubah_pada' => 'datetime',
        ];
    }

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function unit()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_id');
    }

    public function punyaRole(string ...$roles): bool
    {
        return in_array($this->role?->nama, $roles, true);
    }

    /** Sebutan peran untuk antarmuka, mis. "Petugas TU". */
    public function sebutanPeran(): string
    {
        $nama = $this->role?->nama;

        return self::PERAN[$nama] ?? Str::headline($nama ?: '-');
    }

    /** Petugas TU mencatat, mengirim, dan mengarsipkan surat. */
    public function bolehKelolaSurat(): bool
    {
        return $this->punyaRole(...self::PERAN_TATA_USAHA);
    }

    /** Pimpinan memeriksa surat dan memberikan disposisi. */
    public function bolehBeriDisposisi(): bool
    {
        return $this->punyaRole(...self::PERAN_PIMPINAN);
    }

    /** Administrator, Petugas TU, dan Pimpinan melihat seluruh surat. */
    public function bolehLihatSemuaSurat(): bool
    {
        return $this->punyaRole(...self::PERAN_LIHAT_SEMUA);
    }

    public function perluGantiSandi(): bool
    {
        return (bool) $this->must_change_password;
    }

    /** Alamat foto profil; null bila pengguna belum mengunggah foto. */
    public function fotoProfilUrl(): ?string
    {
        if (! $this->foto_profil) {
            return null;
        }

        return route('profile.foto', [
            'user' => $this->id,
            // Versi menyertai alamat agar peramban memuat foto terbaru usai unggah.
            'v' => $this->updated_at?->timestamp ?? time(),
        ]);
    }

    public function suratMasukDiarsipkan()
    {
        return $this->hasMany(SuratMasuk::class, 'dicatat_oleh');
    }

    public function suratKeluarDibuat()
    {
        return $this->hasMany(SuratKeluar::class, 'dibuat_oleh');
    }

    /** Penerima notifikasi pengajuan tanda tangan: pimpinan. */
    public function scopePemberiDisposisi($query)
    {
        return $query->whereHas('role', fn ($q) => $q->whereIn('nama', self::PERAN_PIMPINAN));
    }
}
