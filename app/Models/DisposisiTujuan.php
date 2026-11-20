<?php

namespace App\Models;

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

    /**
     * Penerima notifikasi tindak lanjut: pengguna unit tujuan, tetapi untuk
     * disposisi surat rahasia hanya staf/administrator.
     */
    public function penerimaNotifikasi(): \Illuminate\Database\Eloquent\Collection
    {
        $penerima = User::where('unit_id', $this->unit_id);

        if ($this->disposisi?->isRahasia()) {
            $penerima->whereHas('role', fn ($q) => $q->whereIn('nama', ['admin', 'staf']));
        }

        return $penerima->get();
    }

    /**
     * Hanya unit tujuan (atau admin) yang boleh memperbarui tindak lanjut;
     * akun non-operator tidak pernah menyentuh disposisi surat rahasia.
     */
    public function bolehDiubahOleh(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->punyaRole('admin')) {
            return true;
        }

        if ($this->disposisi?->isRahasia()) {
            return false;
        }

        return $user->unit_id !== null && $user->unit_id === $this->unit_id;
    }
}
