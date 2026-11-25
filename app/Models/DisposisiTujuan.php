<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class DisposisiTujuan extends Model
{
    /** Status tindak lanjut unit tujuan. */
    public const STATUS = [
        'belum' => 'Belum',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
    ];

    protected $table = 'disposisi_tujuan';

    protected $fillable = [
        'disposisi_id', 'unit_id', 'status_tindak_lanjut', 'tgl_selesai',
        'catatan', 'bukti_path', 'bukti_nama', 'diunggah_pada', 'selesai_oleh',
    ];

    protected $casts = [
        'tgl_selesai' => 'datetime',
        'diunggah_pada' => 'datetime',
    ];

    public function disposisi()
    {
        return $this->belongsTo(Disposisi::class);
    }

    public function unit()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_id');
    }

    public function penyelesai()
    {
        return $this->belongsTo(User::class, 'selesai_oleh');
    }

    /** Penerima notifikasi tindak lanjut: seluruh pengguna unit tujuan. */
    public function penerimaNotifikasi(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->disposisi?->penerimaNotifikasi($this->unit_id)
            ?? new \Illuminate\Database\Eloquent\Collection;
    }

    /**
     * Administrator, Petugas TU, dan Pimpinan melihat semua baris tindak lanjut;
     * Staf/Umpeg hanya baris unitnya, termasuk disposisi surat rahasia.
     */
    public function scopeTerlihatOleh(Builder $query, ?User $user): Builder
    {
        if (! $user) {
            return $query->whereRaw('1 = 0');
        }

        if ($user->bolehLihatSemuaSurat()) {
            return $query;
        }

        if ($user->unit_id === null) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where('unit_id', $user->unit_id);
    }

    /**
     * Hanya pengguna unit tujuan yang boleh memperbarui tindak lanjut. Sifat
     * rahasia tidak menghalangi staf unit tujuan menindaklanjuti.
     */
    public function bolehDiubahOleh(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        return $user->unit_id !== null && $user->unit_id === $this->unit_id;
    }
}
