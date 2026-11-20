<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratKeluar extends Model
{
    use SoftDeletes;

    /**
     * Status alur: draf → menunggu_ttd → (revisi) → disetujui → terkirim → diarsipkan.
     */
    public const STATUS = [
        'draf' => 'Draf',
        'menunggu_ttd' => 'Menunggu TTD',
        'revisi' => 'Perlu Revisi',
        'disetujui' => 'Disetujui',
        'terkirim' => 'Terkirim',
        'diarsipkan' => 'Diarsipkan',
    ];

    public const BISA_DIUBAH = ['draf', 'menunggu_ttd', 'revisi'];

    /** Belum disetujui; dipakai antrean disposisi dan filter daftar surat keluar. */
    public const BELUM_DISETUJUI = ['draf', 'revisi', 'menunggu_ttd'];

    protected $table = 'surat_keluar';

    protected $fillable = [
        'no_surat', 'tgl_surat', 'instansi_id', 'klasifikasi_id', 'unit_id', 'hal',
        'sifat', 'lampiran_ket', 'isi_ringkas', 'status', 'penandatangan_id', 'dibuat_oleh',
        'surat_masuk_id', 'file_final_path', 'file_final_nama', 'tgl_kirim', 'penerima',
        'metode_kirim', 'no_bukti_kirim', 'catatan_revisi', 'diajukan_pada', 'disetujui_pada',
    ];

    protected $casts = [
        'tgl_surat' => 'date',
        'tgl_kirim' => 'date',
        'diajukan_pada' => 'datetime',
        'disetujui_pada' => 'datetime',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    public function klasifikasi()
    {
        return $this->belongsTo(Klasifikasi::class);
    }

    public function unit()
    {
        return $this->belongsTo(UnitKerja::class, 'unit_id');
    }

    public function penandatangan()
    {
        return $this->belongsTo(User::class, 'penandatangan_id');
    }

    public function pembuat()
    {
        return $this->belongsTo(User::class, 'dibuat_oleh');
    }

    public function lampiran()
    {
        return $this->morphMany(Lampiran::class, 'surat');
    }

    /** Surat masuk yang dibalas surat keluar ini. */
    public function suratMasuk()
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    /** Kartu disposisi hasil ACC atas surat ini. */
    public function disposisiKartu()
    {
        return $this->hasOne(Disposisi::class, 'surat_keluar_id');
    }

    public function isRahasia(): bool
    {
        return $this->sifat === 'rahasia';
    }

    public function bolehDilihatOleh(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->punyaRole('admin', 'staf')) {
            return true;
        }

        return ! $this->isRahasia()
            && $user->unit_id !== null
            && $user->unit_id === $this->unit_id;
    }

    public function scopeTerlihatOleh(Builder $query, User $user): Builder
    {
        if ($user->punyaRole('admin', 'staf')) {
            return $query;
        }

        return $query->where('sifat', '!=', 'rahasia')->where('unit_id', $user->unit_id);
    }

    public function bisaDiubah(): bool
    {
        return in_array($this->status, self::BISA_DIUBAH, true);
    }

    /** Surat yang sudah disetujui/dikirim/diarsipkan tidak boleh dihapus. */
    public function bisaDihapus(): bool
    {
        return in_array($this->status, ['draf', 'revisi'], true);
    }

    public function sudahDikirim(): bool
    {
        return in_array($this->status, ['terkirim', 'diarsipkan'], true);
    }
}
