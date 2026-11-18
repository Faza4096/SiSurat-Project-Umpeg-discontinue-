<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'unit_id', 'role_id', 'name', 'nip', 'jabatan', 'email', 'password',
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

    public function perluGantiSandi(): bool
    {
        return (bool) $this->must_change_password;
    }

    public function suratMasukDiarsipkan()
    {
        return $this->hasMany(SuratMasuk::class, 'dicatat_oleh');
    }

    public function suratKeluarDibuat()
    {
        return $this->hasMany(SuratKeluar::class, 'dibuat_oleh');
    }

    /** Operator yang menerima notifikasi pengajuan tanda tangan. */
    public function scopeOperator($query)
    {
        return $query->whereHas('role', fn ($q) => $q->where('nama', 'staf'));
    }
}
