<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratMasuk extends Model
{
    use SoftDeletes;

    /** Status alur: diterima → didisposisi → diproses → selesai → diarsipkan. */
    public const STATUS = [
        'diterima' => 'Diterima',
        'didisposisi' => 'Didisposisi',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'diarsipkan' => 'Diarsipkan',
    ];

    protected $table = 'surat_masuk';

    protected $fillable = [
        'no_agenda', 'no_surat', 'tgl_surat', 'tgl_diterima', 'instansi_id',
        'klasifikasi_id', 'perihal', 'sifat', 'status', 'dicatat_oleh',
        'lokasi_arsip', 'diarsipkan_pada', 'diarsipkan_oleh',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
        'tgl_diterima' => 'date',
        'diarsipkan_pada' => 'datetime',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }

    public function pencatat()
    {
        return $this->belongsTo(User::class, 'dicatat_oleh');
    }

    public function pengarsip()
    {
        return $this->belongsTo(User::class, 'diarsipkan_oleh');
    }

    public function disposisi()
    {
        return $this->hasMany(Disposisi::class);
    }

    public function lampiran()
    {
        return $this->morphMany(Lampiran::class, 'surat');
    }

    /** Surat keluar yang dibuat sebagai balasan surat masuk ini. */
    public function balasan()
    {
        return $this->hasMany(SuratKeluar::class, 'surat_masuk_id');
    }

    public function isRahasia(): bool
    {
        return $this->sifat === 'rahasia';
    }

    /**
     * Administrator, Petugas TU, dan Pimpinan melihat semua surat.
     * Staf/Umpeg hanya surat yang didisposisikan ke unitnya; sifat rahasia
     * membatasi antarunit, bukan memblokir unit tujuan disposisi.
     */
    public function bolehDilihatOleh(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->bolehLihatSemuaSurat()) {
            return true;
        }

        if ($user->unit_id === null) {
            return false;
        }

        return $this->disposisi()
            ->whereHas('tujuan', fn ($q) => $q->where('unit_id', $user->unit_id))
            ->exists();
    }

    public function scopeTerlihatOleh(Builder $query, User $user): Builder
    {
        if ($user->bolehLihatSemuaSurat()) {
            return $query;
        }

        return $query->whereHas('disposisi.tujuan', fn ($q) => $q->where('unit_id', $user->unit_id));
    }

    public function sudahDidisposisi(): bool
    {
        return $this->disposisi()->withTrashed()->exists();
    }

    /** Surat yang sudah didisposisikan atau diarsipkan tidak boleh dihapus. */
    public function bisaDihapus(): bool
    {
        return ! $this->sudahDidisposisi() && $this->status !== 'diarsipkan';
    }

    public function bisaDiarsipkan(): bool
    {
        return $this->status === 'selesai';
    }
}
